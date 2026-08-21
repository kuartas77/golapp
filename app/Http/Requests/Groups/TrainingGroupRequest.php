<?php

namespace App\Http\Requests\Groups;

use App\Models\Inscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TrainingGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return isAdmin() || isSchool();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'users_id' => ['required', 'array'],
            'name' => ['required'],
            'stage' => ['nullable'],
            'categories' => ['nullable'],
            'days' => ['required', 'array', 'max:5'],
            'schedules' => ['required', 'array'],
            'school_id' => ['required'],
            'year_active' => ['required'],
            'is_complementary' => ['nullable', 'boolean'],
            'monthly_payment_amount' => ['nullable', 'integer', 'min:1'],
            // 'years' => ['required', 'array'],
            // 'year_two' => ['nullable'],
            // 'year_three' => ['nullable'],
            // 'year_four' => ['nullable'],
            // 'year_five' => ['nullable'],
            // 'year_six' => ['nullable'],
            // 'year_seven' => ['nullable'],
            // 'year_eight' => ['nullable'],
            // 'year_nine' => ['nullable'],
            // 'year_ten' => ['nullable'],
            // 'year_eleven' => ['nullable'],
            // 'year_twelve' => ['nullable'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'is_complementary' => $this->boolean('is_complementary'),
            'monthly_payment_amount' => $this->normalizeMonthlyPaymentAmount(),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $school = getSchool(auth()->user());

            if (
                $school->training_group_monthly_payment_enabled
                && ! $this->boolean('is_complementary')
                && $this->input('name') !== 'Provisional'
                && ! $this->input('monthly_payment_amount')
            ) {
                $validator->errors()->add(
                    'monthly_payment_amount',
                    'La tarifa mensual es obligatoria mientras la tarifa por grupo esté activa.'
                );
            }

            if (! $this->boolean('is_complementary')) {
                return;
            }

            $trainingGroup = $this->route('training_group') ?? $this->route('trainingGroup');

            if (! $trainingGroup) {
                return;
            }

            $hasPrimaryInscriptions = Inscription::query()
                ->where('school_id', $this->input('school_id'))
                ->where('training_group_id', $trainingGroup->id)
                ->where('year', now()->year)
                ->whereNull('deleted_at')
                ->exists();

            if ($hasPrimaryInscriptions) {
                $validator->errors()->add(
                    'is_complementary',
                    'No puedes marcar como complementario un grupo con inscripciones principales activas.'
                );
            }
        });
    }

    private function normalizeMonthlyPaymentAmount(): ?int
    {
        if ($this->boolean('is_complementary') || $this->input('name') === 'Provisional') {
            return null;
        }

        $amount = preg_replace('/[^0-9]/', '', (string) $this->input('monthly_payment_amount'));

        return $amount === '' ? null : (int) $amount;
    }
}
