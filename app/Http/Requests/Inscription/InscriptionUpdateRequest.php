<?php

namespace App\Http\Requests\Inscription;

use App\Models\CompetitionGroup;
use App\Models\Player;
use App\Models\Setting;
use App\Models\TrainingGroup;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class InscriptionUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return isAdmin() || isSchool();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'player_id' => ['required', 'numeric', 'bail'],
            'school_id' => ['required', 'numeric', 'bail'],
            'unique_code' => ['required', 'exists:players,unique_code', 'bail'],
            'year' => ['required', 'bail'],
            'start_date' => ['required', 'bail'],
            'category' => ['required', 'bail'],
            'training_group_id' => [
                'nullable',
                'numeric',
                Rule::exists('training_groups', 'id')->where(fn ($query) => $query
                    ->where('school_id', getSchool(auth()->user())->id)
                    ->where('is_complementary', false)),
            ],
            'complementary_group_id' => [
                'nullable',
                'numeric',
                'different:training_group_id',
                Rule::exists('training_groups', 'id')->where(fn ($query) => $query
                    ->where('school_id', getSchool(auth()->user())->id)
                    ->where('is_complementary', true)),
            ],
            'competition_groups' => ['nullable', 'array'],
            'competition_groups.*' => [
                'integer',
                'distinct',
                Rule::exists('competition_groups', 'id')->where(fn ($query) => $query
                    ->where('school_id', getSchool(auth()->user())->id)),
            ],
            'photos' => ['nullable', 'boolean'],
            'copy_identification_document' => ['nullable', 'boolean'],
            'eps_certificate' => ['nullable', 'boolean'],
            'medic_certificate' => ['nullable', 'boolean'],
            'study_certificate' => ['nullable', 'boolean'],
            'overalls' => ['nullable', 'boolean'],
            'presentation_uniform' => ['nullable', 'boolean'],
            'period_one' => ['nullable'],
            'period_two' => ['nullable'],
            'period_three' => ['nullable'],
            'period_four' => ['nullable'],
            'scholarship' => ['nullable', 'boolean'],
            'pre_inscription' => ['nullable', 'boolean'],
            'brother_payment' => ['nullable', 'boolean'],
            'monthly_payment_type' => ['nullable', 'string', Rule::in(Setting::monthlyPaymentTypes())],
            'recalculate_monthly_payments' => ['nullable', 'boolean'],
            'custom_charges' => ['nullable', 'array'],
            'custom_charges.*.id' => ['nullable', 'integer', 'exists:inscription_custom_charges,id'],
            'custom_charges.*.invoice_custom_item_id' => ['nullable', 'integer', 'exists:invoice_custom_items,id'],
            'custom_charges.*.value' => ['nullable', 'numeric', 'min:0'],
            'custom_charges.*.due_date' => ['nullable', 'date'],
            'custom_charges.*._delete' => ['nullable', 'boolean'],
        ];
    }

    /**
     *
     */
    protected function prepareForValidation(): void
    {
        $dateBirth = Player::find($this->player_id)->date_birth;
        $startDate = Carbon::parse($this->start_date);
        $monthlyPaymentType = $this->resolveMonthlyPaymentType();

        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'year' => $startDate->year,
            'start_date' => $startDate,
            'category' => categoriesName(Carbon::parse($dateBirth)->year),
            'photos' => $this->input('photos', false),
            'copy_identification_document' => $this->input('copy_identification_document', false),
            'eps_certificate' => $this->input('eps_certificate', false),
            'medic_certificate' => $this->input('medic_certificate', false),
            'study_certificate' => $this->input('study_certificate', false),
            'overalls' => $this->input('overalls', false),
            'presentation_uniform' => $this->input('presentation_uniform', false),
            'scholarship' => $this->input('scholarship', false),
            'monthly_payment_type' => $monthlyPaymentType,
            'brother_payment' => $monthlyPaymentType === Setting::BROTHER_MONTHLY_PAYMENT,
            'recalculate_monthly_payments' => $this->boolean('recalculate_monthly_payments'),
            'competition_groups' => array_filter($this->input('competition_groups', [])),
            'training_group_id' => $this->filled('training_group_id') ? $this->training_group_id : null,
            'complementary_group_id' => $this->filled('complementary_group_id') ? $this->complementary_group_id : null,
            'pre_inscription' => $this->input('pre_inscription', false),
            'custom_charges' => $this->normalizeCustomCharges(),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $trainingGroup = $this->selectedTrainingGroup();

            if (! $trainingGroup) {
                return;
            }

            $complementaryGroupId = $this->input('complementary_group_id');

            if ($complementaryGroupId) {
                $complementaryGroup = TrainingGroup::query()
                    ->where('school_id', $this->input('school_id'))
                    ->where('is_complementary', true)
                    ->find($complementaryGroupId);

                if ($complementaryGroup && $complementaryGroup->sport !== $trainingGroup->sport) {
                    $validator->errors()->add('complementary_group_id', 'El grupo complementario debe pertenecer al mismo deporte del grupo de entrenamiento.');
                }
            }

            $competitionGroupIds = $this->input('competition_groups', []);

            if ($competitionGroupIds === []) {
                return;
            }

            $differentSportExists = CompetitionGroup::query()
                ->where('school_id', $this->input('school_id'))
                ->whereIn('id', $competitionGroupIds)
                ->where('sport', '!=', $trainingGroup->sport)
                ->exists();

            if ($differentSportExists) {
                $validator->errors()->add('competition_groups', 'Los grupos de competencia deben pertenecer al mismo deporte del grupo de entrenamiento.');
            }
        });
    }

    private function selectedTrainingGroup(): ?TrainingGroup
    {
        if ($this->filled('training_group_id')) {
            return TrainingGroup::query()
                ->where('school_id', $this->input('school_id'))
                ->where('is_complementary', false)
                ->find($this->input('training_group_id'));
        }

        return TrainingGroup::query()
            ->orderBy('id')
            ->where('school_id', $this->input('school_id'))
            ->where('is_complementary', false)
            ->first();
    }

    private function normalizeCustomCharges(): array
    {
        return collect($this->input('custom_charges', []))
            ->filter(fn ($charge) => is_array($charge))
            ->map(function (array $charge): array {
                $charge['value'] = preg_replace('/[^0-9]/', '', (string) ($charge['value'] ?? 0));
                $charge['due_date'] = $charge['due_date'] ?? $this->input('custom_charges_due_date');
                $charge['_delete'] = (bool) ($charge['_delete'] ?? false);

                return $charge;
            })
            ->values()
            ->all();
    }

    private function resolveMonthlyPaymentType(): string
    {
        $type = $this->input('monthly_payment_type');

        if (in_array($type, Setting::monthlyPaymentTypes(), true)) {
            return $type;
        }

        return $this->boolean('brother_payment')
            ? Setting::BROTHER_MONTHLY_PAYMENT
            : Setting::MONTHLY_PAYMENT;
    }
}
