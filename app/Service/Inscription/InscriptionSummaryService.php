<?php

declare(strict_types=1);

namespace App\Service\Inscription;

use App\Models\Assist;
use App\Models\Inscription;
use App\Models\Payment;
use App\Service\PaymentAmountResolver;

class InscriptionSummaryService
{
    public function __construct(private PaymentAmountResolver $paymentAmountResolver) {}

    public function payload(Inscription $inscription): array
    {
        $this->loadSummaryRelations($inscription);

        return [
            'can_edit' => $this->canEdit($inscription),
            'current_year' => now()->year,
            'inscription' => $this->serializeInscription($inscription),
            'player' => $this->serializePlayer($inscription),
            'years' => $this->serializeYears($inscription),
            'payments' => $this->serializePayments($inscription),
            'attendance' => $this->serializeAttendance($inscription),
            'invoices' => $this->serializeInvoices($inscription),
            'evaluations' => $this->serializeEvaluations($inscription),
            'links' => [
                'stats' => url("/player/{$inscription->player_id}/detail"),
                'player' => url("/deportistas/{$inscription->unique_code}"),
                'print' => route('export.inscription', [$inscription->player_id, $inscription->id]),
            ],
            'amounts' => [
                'enrollment' => $this->paymentAmountResolver->inscriptionAmountForSchool(getSchool(auth()->user())),
                'monthly' => $this->paymentAmountResolver->monthlyAmountForInscription($inscription),
                'annuity' => $this->paymentAmountResolver->annuityAmountForSchool(getSchool(auth()->user())),
            ],
        ];
    }

    private function loadSummaryRelations(Inscription $inscription): void
    {
        $inscription->load([
            'player',
            'trainingGroup' => fn ($query) => $query->withTrashed(),
            'payments' => fn ($query) => $query->withTrashed()->orderBy('year'),
            'assistance' => fn ($query) => $query->withTrashed()->orderBy('month'),
            'invoices' => fn ($query) => $query->with(['items'])->latest('id'),
            'playerEvaluations.period',
            'playerEvaluations.template',
            'skillsControls',
        ]);

        $inscription->setAppends(['format_average']);
    }

    private function canEdit(Inscription $inscription): bool
    {
        return (isAdmin() || isSchool())
            && (int) $inscription->year === (int) now()->year
            && ! $inscription->trashed();
    }

    private function serializeInscription(Inscription $inscription): array
    {
        $status = $this->inscriptionStatus($inscription);

        return [
            'id' => $inscription->id,
            'unique_code' => $inscription->unique_code,
            'year' => (int) $inscription->year,
            'category' => $inscription->category,
            'start_date' => $inscription->start_date,
            'status' => $status['value'],
            'status_label' => $status['label'],
            'pre_inscription' => (bool) $inscription->pre_inscription,
            'brother_payment' => (bool) $inscription->brother_payment,
            'documents' => [
                'photos' => (bool) $inscription->photos,
                'copy_identification_document' => (bool) $inscription->copy_identification_document,
                'eps_certificate' => (bool) $inscription->eps_certificate,
                'medic_certificate' => (bool) $inscription->medic_certificate,
                'study_certificate' => (bool) $inscription->study_certificate,
            ],
            'training_group' => $inscription->trainingGroup ? [
                'id' => $inscription->trainingGroup->id,
                'name' => $inscription->trainingGroup->name,
                'full_group' => $inscription->trainingGroup->full_group ?? $inscription->trainingGroup->name,
            ] : null,
            'stats' => $inscription->format_average,
        ];
    }

    private function serializePlayer(Inscription $inscription): array
    {
        $player = $inscription->player;

        return [
            'id' => $player?->id,
            'unique_code' => $player?->unique_code ?? $inscription->unique_code,
            'full_names' => $player?->full_names,
            'photo_url' => $player?->photo_url,
            'gender' => $player?->gender,
            'date_birth' => $player?->date_birth,
            'identification_document' => $player?->identification_document,
            'email' => $player?->email,
            'mobile' => $player?->mobile,
            'phones' => $player?->phones,
            'eps' => $player?->eps,
            'rh' => $player?->rh,
            'address' => $player?->address,
        ];
    }

    private function serializeYears(Inscription $inscription): array
    {
        return Inscription::query()
            ->where('school_id', $inscription->school_id)
            ->where('player_id', $inscription->player_id)
            ->orderByDesc('year')
            ->get(['id', 'year', 'deleted_at'])
            ->map(function (Inscription $item) use ($inscription) {
                $status = $this->inscriptionStatus($item);

                return [
                    'id' => $item->id,
                    'year' => (int) $item->year,
                    'current' => (int) $item->id === (int) $inscription->id,
                    'status_label' => $status['label'],
                ];
            })
            ->values()
            ->all();
    }

    private function inscriptionStatus(Inscription $inscription): array
    {
        if ($inscription->trashed()) {
            return ['value' => 'retired', 'label' => 'Retirada'];
        }

        if ((int) $inscription->year === (int) now()->year) {
            return ['value' => 'active', 'label' => 'Activa'];
        }

        return ['value' => 'historical', 'label' => 'Histórica'];
    }

    private function serializePayments(Inscription $inscription): array
    {
        return $inscription->payments->map(function (Payment $payment) use ($inscription) {
            $row = [
                'id' => $payment->id,
                'year' => (int) $payment->year,
                'inscription_id' => $payment->inscription_id,
                'training_group_id' => $payment->training_group_id,
                'unique_code' => $payment->unique_code,
                'inscription_deleted' => (bool) $inscription->trashed(),
                'default_monthly_amount' => $this->paymentAmountResolver->monthlyAmountForPayment($payment),
            ];

            foreach (Payment::FIELD_AMOUNT_MAP as $field => $amountField) {
                $row[$field] = $payment->{$field};
                $row[$amountField] = $payment->{$amountField};
            }

            return $row;
        })->values()->all();
    }

    private function serializeAttendance(Inscription $inscription): array
    {
        return $inscription->assistance->map(function (Assist $assist) use ($inscription) {
            $classDays = classDays(
                (int) $assist->year,
                (int) $assist->getRawOriginal('month'),
                array_map('dayToNumber', $inscription->trainingGroup?->explode_days ?? [])
            );

            return [
                'id' => $assist->id,
                'year' => (int) $assist->year,
                'month' => (int) $assist->getRawOriginal('month'),
                'month_label' => config('variables.KEY_MONTHS_INDEX')[(int) $assist->getRawOriginal('month')] ?? $assist->month,
                'inscription_deleted' => (bool) $inscription->trashed(),
                'registers' => $classDays->map(function ($classDay) use ($assist) {
                    $column = numbersToLetters($classDay['number_class']);
                    $attendanceDate = $classDay['date'];

                    return [
                        'column' => $column,
                        'class_number' => $classDay['number_class'],
                        'day' => $classDay['name'],
                        'date' => $classDay['date'],
                        'attendance_date' => $attendanceDate,
                        'value' => $assist->{$column},
                        'label' => $assist->{$column} ? checkAssists($assist->{$column}) : '',
                        'observation' => data_get($assist->observations, $attendanceDate, ''),
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }

    private function serializeInvoices(Inscription $inscription): array
    {
        return $inscription->invoices->map(fn ($invoice) => [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'year' => $invoice->year,
            'status' => $invoice->status,
            'issue_date' => optional($invoice->issue_date)->format('Y-m-d'),
            'due_date' => optional($invoice->due_date)->format('Y-m-d'),
            'total_amount' => $invoice->total_amount,
            'paid_amount' => $invoice->paid_amount,
            'url_show' => url("/facturas/{$invoice->id}"),
            'url_print' => $invoice->url_print,
            'items_count' => $invoice->items->count(),
        ])->values()->all();
    }

    private function serializeEvaluations(Inscription $inscription): array
    {
        return $inscription->playerEvaluations->map(fn ($evaluation) => [
            'id' => $evaluation->id,
            'status' => $evaluation->status,
            'evaluation_type' => $evaluation->evaluation_type,
            'overall_score' => $evaluation->overall_score,
            'evaluated_at' => optional($evaluation->evaluated_at)->toISOString(),
            'period' => $evaluation->period ? [
                'id' => $evaluation->period->id,
                'name' => $evaluation->period->name,
                'code' => $evaluation->period->code,
                'year' => $evaluation->period->year,
            ] : null,
            'template' => $evaluation->template ? [
                'id' => $evaluation->template->id,
                'name' => $evaluation->template->name,
            ] : null,
            'urls' => [
                'show' => url("/player-evaluations/{$evaluation->id}"),
                'pdf' => route('player-evaluations.pdf', $evaluation->id),
            ],
        ])->values()->all();
    }
}
