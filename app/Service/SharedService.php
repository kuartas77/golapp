<?php

namespace App\Service;

use App\Models\Assist;
use App\Models\Inscription;
use App\Models\Payment;
use App\Models\TrainingGroup;
use App\Service\Groups\GroupCatalogCache;
use App\Service\Inscription\TrainingGroupAssignmentService;
use App\Traits\ErrorTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class SharedService
{
    use ErrorTrait;

    private GroupCatalogCache $groupCatalogCache;

    private TrainingGroupAssignmentService $trainingGroupAssignmentService;

    public function __construct(
        private PaymentAmountResolver $paymentAmountResolver,
        ?GroupCatalogCache $groupCatalogCache = null,
        ?TrainingGroupAssignmentService $trainingGroupAssignmentService = null,
    ) {
        $this->groupCatalogCache = $groupCatalogCache ?? app(GroupCatalogCache::class);
        $this->trainingGroupAssignmentService = $trainingGroupAssignmentService
            ?? app(TrainingGroupAssignmentService::class);
    }

    private array $searchPayment;

    private array $searchAssist;

    private array $dataPayment;

    private array $dataAssist;

    public function paymentAssist(Inscription $inscription)
    {
        try {
            $inscription->loadMissing('school.settingsValues');
            $school = $inscription->school;
            DB::beginTransaction();

            $start_date = Carbon::parse($inscription->start_date);
            $hasPayment = Payment::query()
                ->where('inscription_id', $inscription->id)
                ->where('year', $start_date->year)
                ->exists();

            if ($inscription->wasRecentlyCreated && ! $hasPayment) {

                if (! $inscription->training_group_id) {
                    $trainingGroup = TrainingGroup::query()
                        ->orderBy('id', 'asc')
                        ->where('school_id', $inscription->school_id)
                        ->where('is_complementary', false)
                        ->first();
                    $inscription->training_group_id = $trainingGroup->id;
                    $inscription->save();
                }

                // Una beca parcial conserva estados cobrables; solo la beca completa usa el estado Becado.
                $isFullScholarship = $inscription->scholarship
                    && (int) $inscription->scholarship_percentage === Inscription::FULL_SCHOLARSHIP_PERCENTAGE;
                $paymentValue = $isFullScholarship ? '8' : '0';

                $dataPayment = [
                    'inscription_id' => $inscription->id,
                    'year' => $start_date->year,
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

                if ($start_date->month > 1) {
                    $this->checkMonthValue($start_date->month, $paymentValue, $dataPayment);
                }

                if (! $isFullScholarship) {
                    $this->debtMonth($inscription, $start_date->month, $dataPayment);
                }

                $inscription->payments()->create($dataPayment);

                $this->ensureAssistForGroup($inscription, (int) $inscription->training_group_id, $start_date);
                $this->ensureComplementaryAssist($inscription, $start_date);

            } else {
                if ($inscription->wasChanged('training_group_id')) {

                    $dataToUpdate = ['training_group_id' => $inscription->training_group_id, 'deleted_at' => null];

                    $inscription->payments()->withTrashed()->where('year', $start_date->year)->update($dataToUpdate);

                    $inscription->assistance()
                        ->withTrashed()
                        ->where('year', $start_date->year)
                        ->where('training_group_id', $inscription->getOriginal('training_group_id'))
                        ->update($dataToUpdate);

                    $this->enableSkillControl($inscription);
                }

                if ($inscription->wasChanged('complementary_group_id')) {
                    $this->syncComplementaryAssists($inscription, $start_date);
                } else {
                    $this->ensureComplementaryAssist($inscription, $start_date);
                }

            }

            DB::commit();

        } catch (Throwable $th) {
            DB::rollBack();
            report($th);
        }

    }

    private function ensureComplementaryAssist(Inscription $inscription, Carbon $startDate): void
    {
        $groupIds = match (true) {
            $inscription->relationLoaded('complementaryGroups') => $inscription->complementaryGroups->pluck('id'),
            $inscription->wasRecentlyCreated => collect(),
            default => $inscription->complementaryGroups()->pluck('training_groups.id'),
        };

        $groupIds = $groupIds
            ->push($inscription->complementary_group_id)
            ->filter()
            ->map(fn ($groupId) => (int) $groupId)
            ->unique();

        foreach ($groupIds as $groupId) {
            $this->ensureAssistForGroup($inscription, (int) $groupId, $startDate);
        }
    }

    private function ensureAssistForGroup(Inscription $inscription, int $trainingGroupId, Carbon $startDate): void
    {
        $assist = Assist::query()
            ->withTrashed()
            ->firstOrNew([
                'inscription_id' => $inscription->id,
                'training_group_id' => $trainingGroupId,
                'year' => $startDate->year,
                'month' => $startDate->month,
                'school_id' => $inscription->school_id,
            ]);

        $assist->forceFill(['deleted_at' => null])->save();
    }

    private function syncComplementaryAssists(Inscription $inscription, Carbon $startDate): void
    {
        $previousGroupId = $inscription->getOriginal('complementary_group_id');

        if ($previousGroupId) {
            $query = $inscription->assistance()
                ->withTrashed()
                ->where('year', $startDate->year)
                ->where('training_group_id', $previousGroupId);

            if (! $inscription->complementary_group_id) {
                $query->delete();
            }
        }

        $this->ensureComplementaryAssist($inscription, $startDate);
    }

    private function checkMonthValue(int $actualMonth, $value, &$dataPayment)
    {
        $configMonths = config('variables.KEY_INDEX_MONTHS');
        foreach (range(1, $actualMonth) as $numMonth) {
            $monthField = $configMonths[$numMonth];
            $dataPayment[$monthField] = ($actualMonth == $numMonth) ? $value : '14'; // No aplica

            if ($actualMonth !== $numMonth) {
                $dataPayment["{$monthField}_amount"] = 0;
            }
        }
    }

    private function debtMonth(Inscription $inscription, int $actualMonth, &$dataPayment)
    {
        $school = $inscription->school;
        $inscriptionAmount = $this->paymentAmountResolver->payableInscriptionAmountForInscription($inscription);
        $monthlyAmount = $this->paymentAmountResolver->payableMonthlyAmountForInscription($inscription);

        $dataPayment['enrollment'] = '2';
        $dataPayment['enrollment_amount'] = $inscriptionAmount;
        $configMonths = config('variables.KEY_INDEX_MONTHS');

        $dataPayment[$configMonths[$actualMonth]] = '2';
        $dataPayment[$configMonths[$actualMonth].'_amount'] = $monthlyAmount;
    }

    private function enableSkillControl($inscription)
    {
        $inscription->skillsControls()->withTrashed()->restore();
    }

    public function assignTrainingGroup($inscription_id, $request): bool
    {
        try {
            $target_group = $request->input('target_group', null);

            if (! is_null($target_group)) {
                return $this->trainingGroupAssignmentService->assignForSchool(
                    (int) $inscription_id,
                    (int) $target_group,
                    (int) getSchool(auth()->user())->id
                );
            }

            return false;
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $th) {
            $this->logError('SharedService assignTrainingGroup failed', $th, [
                'inscription_id' => $inscription_id,
            ]);

            return false;
        }
    }
}
