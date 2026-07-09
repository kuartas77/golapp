<?php

namespace App\Http\Requests\Inscription;

use App\Models\Player;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'photos' => ['nullable', 'boolean'],
            'copy_identification_document' => ['nullable', 'boolean'],
            'eps_certificate' => ['nullable', 'boolean'],
            'medic_certificate' => ['nullable', 'boolean'],
            'study_certificate' => ['nullable', 'boolean'],
            'overalls' => ['nullable', 'boolean'],
            'ball' => ['nullable', 'boolean'],
            'bag' => ['nullable', 'boolean'],
            'presentation_uniform' => ['nullable', 'boolean'],
            'competition_uniform' => ['nullable', 'boolean'],
            'tournament_pay' => ['nullable', 'boolean'],
            'period_one' => ['nullable'],
            'period_two' => ['nullable'],
            'period_three' => ['nullable'],
            'period_four' => ['nullable'],
            'scholarship' => ['nullable', 'boolean'],
            'pre_inscription' => ['nullable', 'boolean'],
            'brother_payment' => ['nullable', 'boolean'],
            'monthly_payment_type' => ['nullable', 'string', Rule::in(Setting::monthlyPaymentTypes())],
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
            'ball' => $this->input('ball', false),
            'bag' => $this->input('bag', false),
            'presentation_uniform' => $this->input('presentation_uniform', false),
            'competition_uniform' => $this->input('competition_uniform', false),
            'tournament_pay' => $this->input('tournament_pay', false),
            'scholarship' => $this->input('scholarship', false),
            'monthly_payment_type' => $monthlyPaymentType,
            'brother_payment' => $monthlyPaymentType === Setting::BROTHER_MONTHLY_PAYMENT,
            'competition_groups' => array_filter($this->input('competition_groups', [])),
            'training_group_id' => $this->filled('training_group_id') ? $this->training_group_id : null,
            'complementary_group_id' => $this->filled('complementary_group_id') ? $this->complementary_group_id : null,
            'pre_inscription' => $this->input('pre_inscription', false),
            'custom_charges' => $this->normalizeCustomCharges(),
        ]);
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
