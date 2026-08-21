<?php

declare(strict_types=1);

namespace App\Service\Inscription;

use App\Models\Inscription;
use App\Models\School;
use App\Models\SkillsControl;
use App\Notifications\InscriptionNotification;
use App\Service\Groups\GroupCatalogCache;
use App\Service\InscriptionLimitService;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class InscriptionMutationService
{
    public function __construct(
        private Inscription $inscription,
        private InscriptionLimitService $inscriptionLimitService,
        private InscriptionCustomChargeService $customChargeService,
        private InscriptionGroupService $groupService,
        private InscriptionPaymentService $paymentService
    ) {}

    /**
     * @param  array<string, mixed>  $requestData
     * @return array{success: bool, reactivated: bool}
     */
    public function create(array $requestData): array
    {
        $result = [
            'success' => false,
            'reactivated' => false,
        ];

        try {
            $this->groupService->prepareTrainingGroupData($requestData);
            $sendNotification = $requestData['send_notification'] ?? true;
            $customCharges = $requestData['custom_charges'] ?? [];
            $complementaryGroupIds = $requestData['complementary_group_ids'] ?? [];
            unset($requestData['custom_charges'], $requestData['custom_charges_due_date'], $requestData['send_notification'], $requestData['complementary_group_ids']);
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

            $shouldInitializeGroupTariff = $existingInscription?->trashed()
                && $this->paymentService->shouldInitializeGroupTariff($requestData, $existingInscription);

            if (
                $existingInscription?->trashed()
                && $existingInscription->monthly_payment_amount !== null
                && ! $shouldInitializeGroupTariff
            ) {
                $this->paymentService->preserveMonthlyPaymentData($requestData, $existingInscription);
            } else {
                $this->paymentService->prepareMonthlyPaymentData($requestData);
            }

            $this->inscriptionLimitService->assertCanCreate(
                School::query()->with('settingsValues')->findOrFail($requestData['school_id']),
                (int) $requestData['year']
            );

            if ($existingInscription?->trashed()) {
                $inscription = $this->reactivate($existingInscription, $requestData);
                $result['reactivated'] = true;
            } else {
                $inscription = $this->inscription->create($requestData);
            }

            $this->groupService->syncComplementaryGroups($inscription, $complementaryGroupIds);
            $this->groupService->syncCompetitionGroups($inscription, $requestData);
            $this->customChargeService->sync($inscription, $customCharges);

            if ($shouldInitializeGroupTariff) {
                $this->paymentService->recalculateCollectibleMonthlyPaymentAmounts(
                    $inscription->fresh(),
                    'inscription_group_tariff'
                );
            }

            $inscription->load(['player', 'school']);

            if ($sendNotification && checkEmail(data_get($inscription, 'player.email'))) {
                $inscription->player->notify(
                    (new InscriptionNotification($inscription))->afterCommit()
                );
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

    /** @param array<string, mixed> $requestData */
    public function update(array $requestData, Inscription $inscription): bool
    {
        $result = false;

        try {
            $this->groupService->prepareTrainingGroupData($requestData);
            $school = School::query()->findOrFail($requestData['school_id']);
            $shouldInitializeGroupTariff = $this->paymentService
                ->shouldInitializeGroupTariff($requestData, $inscription);
            $shouldRecalculateMonthlyPayments = ! $school->training_group_monthly_payment_enabled
                && (bool) data_get($requestData, 'recalculate_monthly_payments', false);

            if ($shouldInitializeGroupTariff || $shouldRecalculateMonthlyPayments) {
                $this->paymentService->prepareMonthlyPaymentData($requestData);
            } else {
                $this->paymentService->preserveMonthlyPaymentData($requestData, $inscription);
            }

            $shouldApplyScholarshipPayments = ! (bool) $inscription->scholarship
                && (bool) data_get($requestData, 'scholarship', false);
            $customCharges = $requestData['custom_charges'] ?? [];
            $complementaryGroupIds = $requestData['complementary_group_ids'] ?? [];
            unset($requestData['custom_charges'], $requestData['custom_charges_due_date'], $requestData['recalculate_monthly_payments'], $requestData['complementary_group_ids']);
            $requestData['deleted_at'] = null;
            $requestData['unique_code'] = $inscription->unique_code;
            $requestData['start_date'] = $inscription->start_date;

            DB::beginTransaction();

            $inscription->loadMissing('complementaryGroups');
            $this->groupService->syncCompetitionGroups($inscription, $requestData);

            $result = $inscription->update($requestData);
            $this->groupService->syncComplementaryGroups($inscription, $complementaryGroupIds);

            if ($result) {
                $freshInscription = $inscription->fresh();

                if ($shouldApplyScholarshipPayments) {
                    $this->paymentService->applyScholarshipMonthlyPayments($freshInscription);
                } elseif ($shouldInitializeGroupTariff) {
                    $this->paymentService->recalculateCollectibleMonthlyPaymentAmounts(
                        $freshInscription,
                        'inscription_group_tariff'
                    );
                } elseif ($shouldRecalculateMonthlyPayments) {
                    $this->paymentService->recalculateCollectibleMonthlyPaymentAmounts($freshInscription);
                }
            }

            $this->customChargeService->sync($inscription->fresh(), $customCharges);

            DB::commit();
            app(GroupCatalogCache::class)->invalidateSchool((int) $inscription->school_id);
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);
            $result = false;
        }

        return $result;
    }

    public function disable(Inscription $inscription): bool
    {
        try {
            DB::beginTransaction();
            $inscription->load(['payments']);

            foreach ($inscription->payments as $payment) {
                $this->paymentService->markFutureCollectibleMonthsAsRetired($payment);
                $payment->save();
            }

            $inscription->payments()->delete();
            $inscription->assistance()->delete();
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

    /** @param array<string, mixed> $requestData */
    private function reactivate(Inscription $inscription, array $requestData): Inscription
    {
        $inscription->loadMissing('complementaryGroups');
        $requestData['start_date'] = $inscription->start_date;
        $requestData['unique_code'] = $inscription->unique_code;
        $requestData['deleted_at'] = null;

        $inscription->restore();
        $inscription->fill($requestData)->save();

        $this->restoreLegacyRelations($inscription);
        $this->paymentService->restoreRetiredPendingMonths($inscription);
        $this->ensureReactivationBaseRecords($inscription);

        return $inscription->fresh([
            'player',
            'school',
            'competitionGroup',
            'complementaryGroups',
        ]);
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
            ->whereHas('trainingGroup', fn ($query) => $query->where('is_complementary', false))
            ->update([
                'deleted_at' => null,
                'training_group_id' => $inscription->training_group_id,
            ]);

        $this->groupService->ensureInitialAssists($inscription);

        SkillsControl::withTrashed()
            ->where('inscription_id', $inscription->id)
            ->update(['deleted_at' => null]);
    }

    private function ensureReactivationBaseRecords(Inscription $inscription): void
    {
        $startDate = Carbon::parse($inscription->start_date);
        $year = (int) $startDate->year;

        if (! $inscription->payments()->withTrashed()->where('year', $year)->exists()) {
            $inscription->loadMissing('school.settingsValues');
            $inscription->payments()->create($this->paymentService->buildInitialPaymentData($inscription, $startDate));
        }

        $this->groupService->ensureInitialAssists($inscription);
    }
}
