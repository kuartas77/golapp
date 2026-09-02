<?php

namespace App\Http\Requests\Groups;

use App\Service\Category\CategoryFormatService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompetitionGroupRequest extends FormRequest
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
        $school = getSchool(auth()->user());
        $assignableUserIds = $school
            ->groupAssignableUsers()
            ->pluck('users.id')
            ->all();
        $competitionGroup = $this->route('competition_group') ?? $this->route('competitionGroup');

        if ($competitionGroup && (int) $competitionGroup->school_id === (int) $school->id) {
            $assignableUserIds[] = (int) $competitionGroup->user_id;
        }

        return [
            'name' => ['required'],
            'year' => ['required_without:categories'],
            'tournament_id' => ['required', 'exists:tournaments,id'],
            'user_id' => ['required', 'integer', Rule::in($assignableUserIds)],
            'category' => ['required_without:categories'],
            'categories' => ['required', 'array', 'min:1', 'max:12'],
            'categories.*' => [
                'required',
                'string',
                'distinct',
                Rule::in($this->availableCategories()),
            ],
            'school_id' => ['required'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $categories = $this->input('categories');

        if (! is_array($categories) || $categories === []) {
            $legacyCategory = $this->input('category', $this->input('year'));
            $categories = filled($legacyCategory) ? [$legacyCategory] : [];
        }

        $categories = collect($categories)
            ->map(fn ($category) => trim((string) $category))
            ->filter(fn (string $category) => $category !== '')
            ->values()
            ->all();

        $this->merge([
            'school_id' => getSchool(auth()->user())->id,
            'categories' => $categories,
            'category' => implode(', ', $categories),
            'year' => $categories[0] ?? $this->input('year'),
        ]);
    }

    private function availableCategories(): array
    {
        $school = getSchool(auth()->user());
        $formatter = app(CategoryFormatService::class);

        return collect(range(now()->subYears(18)->year, now()->subYears(2)->year))
            ->map(fn (int $year) => $formatter->formatBirthYear($year, $school))
            ->all();
    }
}
