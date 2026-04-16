<?php

declare(strict_types=1);

namespace App\Http\Resources\API\Portal;

use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuardianPlayerDetailResource extends JsonResource
{
    public static $wrap = null;

    /** @var Player */
    public $resource;

    public function toArray(Request $request): array
    {
        /** @var Inscription|null $inscription */
        $inscription = $this->inscriptions->first();
        $historicalInscriptions = collect($this->historical_inscriptions ?? []);

        return [
            'id' => $this->id,
            'unique_code' => $this->unique_code,
            'names' => $this->names,
            'last_names' => $this->last_names,
            'full_names' => $this->full_names,
            'photo_url' => $this->photo_url,
            'identification_document' => $this->identification_document,
            'document_type' => $this->document_type,
            'date_birth' => $this->date_birth,
            'place_birth' => $this->place_birth,
            'gender' => $this->gender,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'phones' => $this->phones,
            'medical_history' => $this->medical_history,
            'school' => $this->school,
            'degree' => $this->degree,
            'jornada' => $this->jornada,
            'address' => $this->address,
            'municipality' => $this->municipality,
            'neighborhood' => $this->neighborhood,
            'rh' => $this->rh,
            'eps' => $this->eps,
            'student_insurance' => $this->student_insurance,
            'school_data' => $this->whenLoaded('schoolData', fn () => [
                'id' => $this->schoolData->id,
                'name' => $this->schoolData->name,
                'slug' => $this->schoolData->slug,
                'logo_file' => $this->schoolData->logo_file,
            ]),
            'historical_inscriptions' => $historicalInscriptions->map(fn ($historicalInscription) => [
                'id' => $historicalInscription->id,
                'year' => $historicalInscription->year,
                'report_url' => route('portal.guardians.players.inscription-report', [
                    'player' => $this->id,
                    'inscription' => $historicalInscription->id,
                ]),
            ])->values(),
            'current_inscription' => $inscription ? [
                'id' => $inscription->id,
                'year' => $inscription->year,
                'category' => $inscription->category,
                'training_group' => $inscription->trainingGroup ? [
                    'id' => $inscription->trainingGroup->id,
                    'name' => $inscription->trainingGroup->name,
                ] : null,
                'stats' => $inscription->format_average,
                'payments' => $inscription->payments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'year' => $payment->year,
                    'months' => collect([
                        'january' => 'Enero',
                        'february' => 'Febrero',
                        'march' => 'Marzo',
                        'april' => 'Abril',
                        'may' => 'Mayo',
                        'june' => 'Junio',
                        'july' => 'Julio',
                        'august' => 'Agosto',
                        'september' => 'Septiembre',
                        'october' => 'Octubre',
                        'november' => 'Noviembre',
                        'december' => 'Diciembre',
                    ])->map(fn ($label, $field) => [
                        'field' => $field,
                        'label' => $label,
                        'value' => $payment->{$field},
                        'display' => getPay($payment->{$field}),
                    ])->values(),
                ])->values(),
                'attendance' => $inscription->assistance->map(function ($assist) {
                    $registers = collect($assist->classDays ?? [])
                        ->map(function ($classDay) use ($assist) {
                            $field = numbersToLetters($classDay['number_class']);
                            $status = $assist->{$field};

                            return [
                                'class_number' => $classDay['number_class'],
                                'day' => $classDay['day'],
                                'date' => $classDay['date'],
                                'status' => $status,
                                'label' => $status ? checkAssists($status) : '',
                            ];
                        })
                        ->values();

                    $attendanceCount = $registers->where('status', 1)->count();
                    $classCount = max(1, $registers->count());

                    return [
                        'id' => $assist->id,
                        'month' => $assist->month,
                        'year' => $assist->year,
                        'percentage' => percent($attendanceCount, $classCount),
                        'registers' => $registers,
                    ];
                })->values(),
                'evaluations' => $inscription->playerEvaluations->map(fn ($evaluation) => [
                    'id' => $evaluation->id,
                    'status' => $evaluation->status,
                    'evaluation_type' => $evaluation->evaluation_type,
                    'overall_score' => $evaluation->overall_score,
                    'evaluated_at' => optional($evaluation->evaluated_at)?->toISOString(),
                    'period' => $evaluation->period ? [
                        'id' => $evaluation->period->id,
                        'name' => $evaluation->period->name,
                        'code' => $evaluation->period->code,
                        'year' => $evaluation->period->year,
                    ] : null,
                    'pdf_url' => route('portal.guardians.evaluations.pdf', $evaluation->id),
                ])->values(),
                'comparison_periods' => $inscription->playerEvaluations
                    ->filter(fn ($evaluation) => $evaluation->period)
                    ->map(fn ($evaluation) => [
                        'id' => $evaluation->period->id,
                        'name' => $evaluation->period->name,
                        'code' => $evaluation->period->code,
                        'year' => $evaluation->period->year,
                    ])
                    ->unique('id')
                    ->values(),
                'report_url' => route('portal.guardians.players.inscription-report', [
                    'player' => $this->id,
                    'inscription' => $inscription->id,
                ]),
            ] : null,
        ];
    }
}
