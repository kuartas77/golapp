<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Inscription;
use App\Models\InscriptionCustomCharge;
use App\Models\InvoiceCustomItem;
use App\Models\Payment;
use App\Models\School;
use App\Models\Setting;
use App\Models\TrainingGroup;
use App\Notifications\InscriptionNotification;
use App\Service\Groups\GroupCatalogCache;
use App\Service\InscriptionLimitService;
use App\Service\PaymentAmountResolver;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class InscriptionRepository
{
    public function __construct(
        private Inscription $inscription,
        private PeopleRepository $peopleRepository,
        private PaymentAmountResolver $paymentAmountResolver,
        private InscriptionLimitService $inscriptionLimitService
    ) {}

    /**
     * @param  false  $trashed
     */
    public function findById($id, bool $trashed = false): mixed
    {
        if ($trashed) {
            return Inscription::onlyTrashed()->schoolId()->findOrFail($id);
        }

        return Inscription::query()->schoolId()->findOrFail($id);

    }

    /**
     * @param  bool  $created
     */
    public function createInscription(array $requestData): array
    {
        $result = [
            'success' => false,
            'reactivated' => false,
        ];

        try {
            $this->prepareTrainingGroupData($requestData);
            $this->prepareMonthlyPaymentData($requestData);
            $customCharges = $requestData['custom_charges'] ?? [];
            unset($requestData['custom_charges'], $requestData['custom_charges_due_date']);
            $requestData['deleted_at'] = null;

            DB::beginTransaction();

            $existingInscription = $this->inscription->withTrashed()
                ->where('unique_code', $requestData['unique_code'])
                ->where('year', $requestData['year'])
                ->where('school_id', $requestData['school_id'])
                ->first();

            if ($existingInscription && ! $existingInscription->trashed()) {
                throw ValidationException::withMessages([
                    'unique_code' => 'El deportista ya tiene una inscripción activa para el año seleccionado.',
                ]);
            }

            $this->inscriptionLimitService->assertCanCreate(
                School::query()->with('settingsValues')->findOrFail($requestData['school_id']),
                (int) $requestData['year']
            );

            if ($existingInscription?->trashed()) {
                $inscription = $this->reactivateInscription($existingInscription, $requestData);
                $result['reactivated'] = true;
            } else {
                $inscription = $this->inscription->create($requestData);
            }

            $this->setCompetitionGroupIds($inscription, $requestData);
            $this->syncCustomCharges($inscription, $customCharges);

            $inscription->load(['player', 'school']);

            if (checkEmail(data_get($inscription, 'player.email'))) {
                $inscription->player->notifyNow(new InscriptionNotification($inscription));
            }

            DB::commit();
            app(GroupCatalogCache::class)->invalidateSchool((int) $inscription->school_id);

            $result['success'] = true;
        } catch (ValidationException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Exception $exception) {
            DB::rollBack();
            report($exception);
        }

        return $result;
    }

    private function reactivateInscription(Inscription $inscription, array $requestData): Inscription
    {
        $requestData['start_date'] = $inscription->start_date;
        $requestData['unique_code'] = $inscription->unique_code;
        $requestData['deleted_at'] = null;

        $inscription->restore();
        $inscription->fill($requestData)->save();

        $this->restoreLegacyRelations($inscription);
        $this->restoreRetiredPendingMonths($inscription);
        $this->ensureReactivationBaseRecords($inscription);

        return $inscription->fresh([
            'player',
            'school',
            'competitionGroup',
        ]);
    }

    private function prepareTrainingGroupData(array &$requestData): void
    {
        $trainingGroup = TrainingGroup::query()
            ->orderBy('id')
            ->firstWhere('school_id', $requestData['school_id']);

        throw_if(is_null($trainingGroup), Exception::class, 'Training group not found for school');
        $trainingGroupId = isset($requestData['training_group_id']) ? $requestData['training_group_id'] : $trainingGroup->id;

        $requestData['training_group_id'] = $trainingGroupId;
        $requestData['pre_inscription'] = (bool) data_get($requestData, 'pre_inscription', false)
            || (string) $trainingGroupId === (string) $trainingGroup->id;
    }

    private function prepareMonthlyPaymentData(array &$requestData): void
    {
        $school = School::query()
            ->with('settingsValues')
            ->findOrFail($requestData['school_id']);

        $type = $this->paymentAmountResolver->normalizeMonthlyPaymentType(
            data_get($requestData, 'monthly_payment_type'),
            (bool) data_get($requestData, 'brother_payment', false)
        );

        $requestData['monthly_payment_type'] = $type;
        $requestData['monthly_payment_amount'] = $this->paymentAmountResolver
            ->monthlyAmountForSchoolByType($school, $type);
        $requestData['brother_payment'] = $type === Setting::BROTHER_MONTHLY_PAYMENT;
    }

    private function setCompetitionGroupIds($inscription, $requestData): void
    {
        $competitionGroupIds = data_get($requestData, 'competition_groups', []);

        $inscription->competitionGroup()->sync($competitionGroupIds);
    }

    public function updateInscription(array $requestData, Inscription $inscription): bool
    {
        $result = false;
        try {
            $this->prepareTrainingGroupData($requestData);
            $this->prepareMonthlyPaymentData($requestData);
            $customCharges = $requestData['custom_charges'] ?? [];
            unset($requestData['custom_charges'], $requestData['custom_charges_due_date']);
            $requestData['deleted_at'] = null;
            $requestData['unique_code'] = $inscription->unique_code;
            $requestData['start_date'] = $inscription->start_date;

            DB::beginTransaction();

            $this->setCompetitionGroupIds($inscription, $requestData);

            $result = $inscription->update($requestData);
            $this->syncCustomCharges($inscription->fresh(), $customCharges);

            DB::commit();
            app(GroupCatalogCache::class)->invalidateSchool((int) $inscription->school_id);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);
            $result = false;
        }

        return $result;
    }

    private function syncCustomCharges(Inscription $inscription, array $customCharges): void
    {
        if (empty($customCharges)) {
            return;
        }

        $schoolId = (int) $inscription->school_id;
        $catalogIds = collect($customCharges)
            ->pluck('invoice_custom_item_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $catalogItems = InvoiceCustomItem::query()
            ->schoolId()
            ->whereIn('id', $catalogIds)
            ->get()
            ->keyBy('id');

        foreach ($customCharges as $chargeData) {
            $value = (float) data_get($chargeData, 'value', 0);
            $dueDate = data_get($chargeData, 'due_date');
            $chargeId = data_get($chargeData, 'id');
            $shouldDelete = (bool) data_get($chargeData, '_delete', false);

            if ($chargeId) {
                $charge = InscriptionCustomCharge::query()
                    ->where('school_id', $schoolId)
                    ->where('inscription_id', $inscription->id)
                    ->find($chargeId);

                if (! $charge || $charge->status === InscriptionCustomCharge::STATUS_PAID) {
                    continue;
                }

                if ($shouldDelete) {
                    if (
                        $charge->status === InscriptionCustomCharge::STATUS_PENDING
                        && is_null($charge->invoice_item_id)
                    ) {
                        $charge->delete();
                    }

                    continue;
                }

                $charge->update([
                    'value' => $value,
                    'due_date' => $dueDate,
                ]);

                continue;
            }

            $catalogId = (int) data_get($chargeData, 'invoice_custom_item_id');
            $catalogItem = $catalogItems->get($catalogId);

            if (! $catalogItem) {
                continue;
            }

            $activeExists = InscriptionCustomCharge::query()
                ->where('school_id', $schoolId)
                ->where('inscription_id', $inscription->id)
                ->where('invoice_custom_item_id', $catalogItem->id)
                ->whereIn('status', [
                    InscriptionCustomCharge::STATUS_PENDING,
                    InscriptionCustomCharge::STATUS_DUE,
                ])
                ->whereNull('invoice_item_id')
                ->exists();

            if ($activeExists) {
                continue;
            }

            InscriptionCustomCharge::query()->create([
                'school_id' => $schoolId,
                'inscription_id' => $inscription->id,
                'player_id' => $inscription->player_id,
                'invoice_custom_item_id' => $catalogItem->id,
                'name' => $catalogItem->name,
                'value' => $value,
                'status' => InscriptionCustomCharge::STATUS_PENDING,
                'due_date' => $dueDate,
            ]);
        }
    }

    /**
     * @return Builder[]|Collection
     */
    public function getInscriptionsEnabled(): Builder
    {
        return Inscription::query()->select('inscriptions.*')->with(['player.people', 'trainingGroup' => fn ($q) => $q->withTrashed()])
            ->join('players', 'inscriptions.player_id', '=', 'players.id')
            ->inscriptionYear(request('inscription_year'))
            ->schoolId();
    }

    /**
     * @return Builder[]|Collection
     */
    public function getInscriptionsDisabled(): Builder
    {
        return $this->inscription->with(['player.people', 'trainingGroup'])
            ->inscriptionYear(request('inscription_year'))->schoolId()->onlyTrashed();
    }

    public function searchInscriptionCompetition(array $fields): ?Inscription
    {
        return Inscription::query()->with('player')
            ->where('unique_code', $fields['unique_code'])
            ->whereHas(
                'competitionGroup',
                fn ($q) => $q->where('competition_group_id', $fields['competition_group_id']), '<=', 0)
            ->where('year', now()->year)
            ->schoolId()
            ->first();
    }

    public function searchInsUniqueCode($id): ?Inscription
    {
        $query = $this->inscription->query()
            ->with(['player', 'competitionGroup'])
            ->schoolId();

        $inscription = null;

        if (is_numeric($id)) {
            $inscription = (clone $query)->find((int) $id);
        }

        if (! $inscription) {
            $inscription = $query
                ->orderByDesc('id')
                ->firstWhere('unique_code', (string) $id);
        }

        if (! $inscription) {
            return null;
        }

        $inscription->setAttribute(
            'competition_groups',
            $inscription->competitionGroup->pluck('id')->map(fn ($groupId) => (string) $groupId)->values()->all()
        );

        return $inscription;
    }

    public function disable(Inscription $inscription): bool
    {
        try {
            DB::beginTransaction();
            $inscription->load(['payments']);

            foreach ($inscription->payments as $payment) {
                $this->markFuturePendingMonthsAsRetired($payment);
                $payment->save();
            }

            $inscription->delete();
            DB::commit();
            app(GroupCatalogCache::class)->invalidateSchool((int) $inscription->school_id);

            return true;
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return false;
        }
    }

    private function restoreLegacyRelations(Inscription $inscription): void
    {
        $year = (int) Carbon::parse($inscription->start_date)->year;

        $inscription->payments()
            ->withTrashed()
            ->where('year', $year)
            ->update([
                'deleted_at' => null,
                'training_group_id' => $inscription->training_group_id,
            ]);

        $inscription->assistance()
            ->withTrashed()
            ->where('year', $year)
            ->update([
                'deleted_at' => null,
                'training_group_id' => $inscription->training_group_id,
            ]);

        $inscription->skillsControls()
            ->withTrashed()
            ->update(['deleted_at' => null]);
    }

    private function ensureReactivationBaseRecords(Inscription $inscription): void
    {
        $startDate = Carbon::parse($inscription->start_date);
        $year = (int) $startDate->year;
        $month = (int) $startDate->month;

        if (! $inscription->payments()->withTrashed()->where('year', $year)->exists()) {
            $inscription->loadMissing('school.settingsValues');
            $inscription->payments()->create($this->buildInitialPaymentData($inscription, $startDate));
        }

        if (! $inscription->assistance()->withTrashed()->where('year', $year)->where('month', $month)->exists()) {
            $inscription->assistance()->create([
                'training_group_id' => $inscription->training_group_id,
                'year' => $year,
                'month' => $month,
                'school_id' => $inscription->school_id,
            ]);
        }
    }

    private function restoreRetiredPendingMonths(Inscription $inscription): void
    {
        $year = (int) Carbon::parse($inscription->start_date)->year;
        $payments = $inscription->payments()->where('year', $year)->get();

        foreach ($payments as $payment) {
            $shouldSave = false;

            foreach (config('variables.KEY_INDEX_MONTHS', []) as $field) {
                if ((int) $payment->{$field} !== Payment::$permanent_retirement) {
                    continue;
                }

                $payment->{$field} = (string) Payment::$pending;
                $shouldSave = true;
            }

            if ($shouldSave) {
                $payment->save();
            }
        }
    }

    private function buildInitialPaymentData(Inscription $inscription, Carbon $startDate): array
    {
        $paymentValue = $inscription->scholarship ? (string) Payment::$scholarship_recipient : (string) Payment::$pending;
        $dataPayment = [
            'inscription_id' => $inscription->id,
            'year' => (int) $startDate->year,
            'training_group_id' => $inscription->training_group_id,
            'school_id' => $inscription->school_id,
            'unique_code' => $inscription->unique_code,
            'enrollment' => $paymentValue,
            'january' => $paymentValue,
            'february' => $paymentValue,
            'march' => $paymentValue,
            'april' => $paymentValue,
            'may' => $paymentValue,
            'june' => $paymentValue,
            'july' => $paymentValue,
            'august' => $paymentValue,
            'september' => $paymentValue,
            'october' => $paymentValue,
            'november' => $paymentValue,
            'december' => $paymentValue,
        ];

        if ((int) $startDate->month > 1) {
            $this->checkMonthValue((int) $startDate->month, $paymentValue, $dataPayment);
        }

        if (! $inscription->scholarship) {
            $this->debtMonth($inscription, (int) $startDate->month, $dataPayment);
        }

        return $dataPayment;
    }

    private function checkMonthValue(int $actualMonth, string $value, array &$dataPayment): void
    {
        foreach (range(1, $actualMonth) as $monthNumber) {
            $field = config("variables.KEY_INDEX_MONTHS.{$monthNumber}");

            if (! $field) {
                continue;
            }

            $dataPayment[$field] = $actualMonth === $monthNumber
                ? $value
                : (string) Payment::$no_application;
        }
    }

    private function debtMonth(Inscription $inscription, int $actualMonth, array &$dataPayment): void
    {
        $inscriptionAmount = data_get($inscription->school->settings, 'INSCRIPTION_AMOUNT', 70000);
        $monthlyAmount = $this->paymentAmountResolver->monthlyAmountForInscription($inscription);
        $monthField = config("variables.KEY_INDEX_MONTHS.{$actualMonth}");

        $dataPayment['enrollment'] = (string) Payment::$debt;
        $dataPayment['enrollment_amount'] = $inscriptionAmount;

        if ($monthField) {
            $dataPayment[$monthField] = (string) Payment::$debt;
            $dataPayment["{$monthField}_amount"] = $monthlyAmount;
        }
    }

    private function markFuturePendingMonthsAsRetired(Payment $payment): void
    {
        $paymentYear = (int) $payment->year;
        $currentYear = (int) now()->year;
        $currentMonth = (int) now()->month;

        foreach (config('variables.KEY_INDEX_MONTHS', []) as $monthNumber => $field) {
            if (
                $paymentYear < $currentYear
                || ($paymentYear === $currentYear && (int) $monthNumber <= $currentMonth)
            ) {
                continue;
            }

            if ((int) $payment->{$field} === Payment::$pending) {
                $payment->{$field} = (string) Payment::$permanent_retirement;
            }
        }
    }

    public function createInscriptionByYear($actualYear = null, $futureYear = null): void
    {
        try {
            $actualYear = (int) ($actualYear ?: now()->year);

            if ($futureYear instanceof Carbon) {
                $futureYearValue = (int) $futureYear->year;
                $futureStartDate = $futureYear->copy()->startOfYear()->format('Y-m-d');
            } elseif (is_numeric($futureYear)) {
                $futureYearValue = (int) $futureYear;
                $futureStartDate = Carbon::create($futureYearValue, 1, 1)->format('Y-m-d');
            } else {
                $futureDate = now()->addYear()->startOfYear();
                $futureYearValue = (int) $futureDate->year;
                $futureStartDate = $futureDate->format('Y-m-d');
            }

            $trainingGroup = TrainingGroup::query()->orderBy('id')->schoolId()->first();
            throw_if(is_null($trainingGroup), Exception::class, 'Training group not found');

            $inscriptions = $this->inscription->where('year', $actualYear)->schoolId()->get();

            DB::beginTransaction();

            foreach ($inscriptions as $inscription) {
                $inscriptionData = [
                    'school_id' => $inscription->school_id,
                    'player_id' => $inscription->player_id,
                    'unique_code' => $inscription->unique_code,
                    'year' => $futureYearValue,
                    'start_date' => $futureStartDate,
                    'category' => $inscription->category,
                    'photos' => $inscription->photos,
                    'copy_identification_document' => $inscription->copy_identification_document,
                    'eps_certificate' => $inscription->eps_certificate,
                    'medic_certificate' => $inscription->medic_certificate,
                    'study_certificate' => $inscription->study_certificate,
                    'overalls' => $inscription->overalls,
                    'ball' => $inscription->ball,
                    'bag' => $inscription->bag,
                    'presentation_uniform' => $inscription->presentation_uniform,
                    'competition_uniform' => $inscription->competition_uniform,
                    'tournament_pay' => $inscription->tournament_pay,
                    'period_one' => $inscription->period_one,
                    'period_two' => $inscription->period_two,
                    'period_three' => $inscription->period_three,
                    'period_four' => $inscription->period_four,
                    'scholarship' => $inscription->scholarship,
                    'brother_payment' => $inscription->brother_payment,
                    'monthly_payment_type' => $inscription->monthly_payment_type,
                    'monthly_payment_amount' => $inscription->monthly_payment_amount,
                    'training_group_id' => $trainingGroup->id,
                ];

                $this->inscription->withTrashed()->updateOrCreate([
                    'unique_code' => $inscriptionData['unique_code'],
                    'year' => $inscriptionData['year'],
                    'school_id' => $inscriptionData['school_id'],
                ], $inscriptionData);
            }

            DB::commit();
            app(GroupCatalogCache::class)->invalidateSchool((int) $trainingGroup->school_id);
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);
        }
    }

    public function getPreinscriptionsOrProvicionalGroup($schoolId, $trainingGroupId): Builder
    {
        return Inscription::query()
            ->select([
                'inscriptions.id',
                'inscriptions.unique_code',
                DB::raw("CONCAT(players.names, ' ', players.last_names) as names"),
            ])
            ->join('players', 'players.id', '=', 'inscriptions.player_id')
            ->where('inscriptions.year', now()->year)
            ->where('inscriptions.school_id', $schoolId)
            ->where(
                fn ($query) => $query->where('inscriptions.training_group_id', $trainingGroupId)
                    ->orWhere('inscriptions.pre_inscription', 1));
    }
}
