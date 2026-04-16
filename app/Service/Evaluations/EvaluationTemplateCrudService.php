<?php

namespace App\Service\Evaluations;

use App\Models\Evaluations\EvaluationTemplate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EvaluationTemplateCrudService
{
    public function create(array $data, int $schoolId, int $userId): EvaluationTemplate
    {
        return DB::transaction(function () use ($data, $schoolId, $userId) {
            $template = EvaluationTemplate::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'year' => (int) $data['year'],
                'training_group_id' => $data['training_group_id'] ?? null,
                'status' => $data['status'],
                'version' => 1,
                'created_by' => $userId,
                'school_id' => $schoolId,
            ]);

            $this->replaceCriteria($template, $data['criteria'] ?? []);

            return $template->refresh();
        });
    }

    public function update(EvaluationTemplate $template, array $data): EvaluationTemplate
    {
        $this->ensureNotInUse($template);

        return DB::transaction(function () use ($template, $data) {
            $template->fill([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'year' => (int) $data['year'],
                'training_group_id' => $data['training_group_id'] ?? null,
                'status' => $data['status'],
            ]);
            $template->save();

            $this->replaceCriteria($template, $data['criteria'] ?? []);

            return $template->refresh();
        });
    }

    public function duplicate(EvaluationTemplate $template, int $userId): EvaluationTemplate
    {
        return DB::transaction(function () use ($template, $userId) {
            $template->loadMissing('criteria');

            $copy = EvaluationTemplate::create([
                'name' => $template->name,
                'description' => $template->description,
                'year' => $template->year,
                'training_group_id' => $template->training_group_id,
                'status' => 'draft',
                'version' => $this->nextVersion($template),
                'created_by' => $userId,
                'school_id' => $template->school_id,
            ]);

            $criteria = $template->criteria
                ->sortBy('sort_order')
                ->values()
                ->map(function ($criterion) {
                    return Arr::only($criterion->toArray(), [
                        'dimension',
                        'name',
                        'description',
                        'score_type',
                        'min_score',
                        'max_score',
                        'weight',
                        'sort_order',
                        'is_required',
                    ]);
                })
                ->all();

            $this->replaceCriteria($copy, $criteria);

            return $copy->refresh();
        });
    }

    public function updateStatus(EvaluationTemplate $template, string $status): EvaluationTemplate
    {
        $template->status = $status;
        $template->save();

        return $template->refresh();
    }

    public function delete(EvaluationTemplate $template): void
    {
        $this->ensureNotInUse($template);

        $template->delete();
    }

    private function replaceCriteria(EvaluationTemplate $template, array $criteria): void
    {
        $normalizedCriteria = $this->normalizeCriteria($criteria);

        $template->criteria()->delete();
        $template->criteria()->createMany($normalizedCriteria);
    }

    private function normalizeCriteria(array $criteria): array
    {
        $usedCodes = [];

        return collect($criteria)
            ->values()
            ->map(function (array $criterion, int $index) use (&$usedCodes) {
                $scoreType = $criterion['score_type'] === 'scale' ? 'scale' : 'numeric';
                $code = $this->generateUniqueCode(
                    dimension: (string) $criterion['dimension'],
                    name: (string) $criterion['name'],
                    usedCodes: $usedCodes,
                    fallbackIndex: $index + 1
                );

                $usedCodes[] = $code;

                return [
                    'code' => $code,
                    'dimension' => trim((string) $criterion['dimension']),
                    'name' => trim((string) $criterion['name']),
                    'description' => $criterion['description'] ?? null,
                    'score_type' => $scoreType,
                    'min_score' => $scoreType === 'numeric' ? $criterion['min_score'] : null,
                    'max_score' => $scoreType === 'numeric' ? $criterion['max_score'] : null,
                    'weight' => (float) $criterion['weight'],
                    'sort_order' => (int) ($criterion['sort_order'] ?? ($index + 1)),
                    'is_required' => (bool) ($criterion['is_required'] ?? true),
                ];
            })
            ->sortBy('sort_order')
            ->values()
            ->all();
    }

    private function generateUniqueCode(string $dimension, string $name, array $usedCodes, int $fallbackIndex): string
    {
        $baseCode = (string) Str::of(Str::ascii(trim($dimension . ' ' . $name)))
            ->replaceMatches('/[^A-Za-z0-9]+/', ' ')
            ->trim()
            ->snake();

        if ($baseCode === '') {
            $baseCode = 'criterion_' . $fallbackIndex;
        }

        $candidate = $baseCode;
        $suffix = 2;

        while (in_array($candidate, $usedCodes, true)) {
            $candidate = $baseCode . '_' . $suffix;
            $suffix += 1;
        }

        return $candidate;
    }

    private function ensureNotInUse(EvaluationTemplate $template): void
    {
        if ($template->isInUse()) {
            throw ValidationException::withMessages([
                'template' => ['La plantilla ya tiene evaluaciones asociadas. Duplica una nueva versión para modificarla.'],
            ]);
        }
    }

    private function nextVersion(EvaluationTemplate $template): int
    {
        $currentMaxVersion = EvaluationTemplate::query()
            ->where('school_id', $template->school_id)
            ->where('name', $template->name)
            ->where('year', $template->year)
            ->where(function ($query) use ($template) {
                if ($template->training_group_id) {
                    $query->where('training_group_id', $template->training_group_id);
                    return;
                }

                $query->whereNull('training_group_id');
            })
            ->max('version');

        return max((int) $template->version, (int) $currentMaxVersion) + 1;
    }
}
