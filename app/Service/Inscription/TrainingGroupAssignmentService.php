<?php

declare(strict_types=1);

namespace App\Service\Inscription;

use App\Models\Inscription;
use App\Models\School;
use App\Models\TrainingGroup;
use App\Service\Groups\GroupCatalogCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrainingGroupAssignmentService
{
    public function __construct(
        private readonly InscriptionPaymentService $paymentService,
        private readonly GroupCatalogCache $groupCatalogCache,
    ) {}

    public function assignForSchool(int $inscriptionId, int $targetGroupId, int $schoolId): bool
    {
        $inscription = Inscription::query()
            ->where('school_id', $schoolId)
            ->findOrFail($inscriptionId);
        $targetGroup = $this->findTargetGroup($schoolId, $targetGroupId);

        return $this->assign($inscription, $targetGroup);
    }

    public function assign(Inscription $inscription, TrainingGroup $targetGroup): bool
    {
        if ((int) $inscription->training_group_id === (int) $targetGroup->id) {
            return true;
        }

        $school = School::query()->findOrFail($inscription->school_id);
        $this->assertGroupCanReceiveInscription($school, $targetGroup);

        $requestData = [
            'school_id' => $school->id,
            'training_group_id' => $targetGroup->id,
        ];
        $shouldInitializeGroupTariff = $this->paymentService
            ->shouldInitializeGroupTariff($requestData, $inscription);

        if ($shouldInitializeGroupTariff) {
            $this->paymentService->prepareMonthlyPaymentData($requestData);
        }

        $updated = DB::transaction(function () use (
            $inscription,
            $targetGroup,
            $requestData,
            $shouldInitializeGroupTariff
        ): bool {
            $attributes = ['training_group_id' => $targetGroup->id];

            if ($shouldInitializeGroupTariff) {
                $attributes += [
                    'monthly_payment_type' => $requestData['monthly_payment_type'],
                    'monthly_payment_amount' => $requestData['monthly_payment_amount'],
                    'brother_payment' => $requestData['brother_payment'],
                ];
            }

            $updated = (bool) $inscription->update($attributes);

            if ($updated && $shouldInitializeGroupTariff) {
                $this->paymentService->recalculateCollectibleMonthlyPaymentAmounts(
                    $inscription->fresh(),
                    'inscription_group_tariff'
                );
            }

            return $updated;
        });

        if ($updated) {
            $this->groupCatalogCache->invalidateSchool((int) $inscription->school_id);
        }

        return $updated;
    }

    public function findTargetGroup(int $schoolId, int $targetGroupId): TrainingGroup
    {
        return TrainingGroup::query()
            ->where('school_id', $schoolId)
            ->where('is_complementary', false)
            ->findOrFail($targetGroupId);
    }

    public function assertGroupCanReceiveInscription(School $school, TrainingGroup $targetGroup): void
    {
        if ((int) $targetGroup->school_id !== (int) $school->id || $targetGroup->is_complementary) {
            throw ValidationException::withMessages([
                'training_group_id' => 'Selecciona un grupo principal válido.',
            ]);
        }

        if (
            $school->training_group_monthly_payment_enabled
            && $targetGroup->name !== 'Provisional'
            && (int) $targetGroup->monthly_payment_amount <= 0
        ) {
            throw ValidationException::withMessages([
                'training_group_id' => 'El grupo seleccionado no tiene una tarifa mensual configurada.',
            ]);
        }
    }
}
