<?php

declare(strict_types=1);

namespace App\Service\Methodology;

use App\Models\MethodologyRecord;
use App\Models\TrainingSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class VisualResourceImageService
{
    public const MODE_DIAGRAM = 'diagram';
    public const MODE_IMAGE = 'image';

    public const PLANNING_PHASE_KEYS = [
        'initial_phase',
        'central_phase_one',
        'central_phase_two',
        'central_phase_three',
    ];

    public function store(UploadedFile $file): string
    {
        $school = getSchool(auth()->user());

        return $file->store($school->slug . '/methodology', 'public');
    }

    public function url(?string $path): ?string
    {
        return $path ? route('images', $path) : null;
    }

    public function localPath(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->path($path);
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    public function deleteMany(array $paths): void
    {
        collect($paths)->filter()->unique()->each(fn (string $path) => $this->delete($path));
    }

    public function methodologyPayload(array $validated, array $input, array $files = [], MethodologyRecord $record = null): array
    {
        if (($validated['type'] ?? null) !== MethodologyRecord::TYPE_PLANNING) {
            $oldPaths = collect($record?->diagram_media ?? [])->pluck('path')->filter()->all();

            return [array_replace($validated, ['diagram_media' => null]), [], $oldPaths];
        }

        $oldMedia = $record?->diagram_media ?? [];
        $media = [];
        $newPaths = [];
        $deleteAfterCommit = [];

        foreach (self::PLANNING_PHASE_KEYS as $phaseKey) {
            $oldPath = data_get($oldMedia, "{$phaseKey}.path");
            $mode = $this->normalizeMode(data_get($input, "diagram_media.{$phaseKey}.mode", data_get($oldMedia, "{$phaseKey}.mode", self::MODE_DIAGRAM)));
            $remove = filter_var(data_get($input, "diagram_media.{$phaseKey}.image_remove", false), FILTER_VALIDATE_BOOLEAN);
            $file = $this->methodologyFile($files, $phaseKey);

            if ($mode === self::MODE_IMAGE && ! $file && (! $oldPath || $remove)) {
                throw ValidationException::withMessages([
                    "diagram_images.{$phaseKey}" => 'Carga una imagen para usar este recurso visual.',
                ]);
            }
        }

        foreach (self::PLANNING_PHASE_KEYS as $phaseKey) {
            $oldPath = data_get($oldMedia, "{$phaseKey}.path");
            $mode = $this->normalizeMode(data_get($input, "diagram_media.{$phaseKey}.mode", data_get($oldMedia, "{$phaseKey}.mode", self::MODE_DIAGRAM)));
            $remove = filter_var(data_get($input, "diagram_media.{$phaseKey}.image_remove", false), FILTER_VALIDATE_BOOLEAN);
            $file = $this->methodologyFile($files, $phaseKey);
            $path = $oldPath;

            if ($file instanceof UploadedFile) {
                $path = $this->store($file);
                $newPaths[] = $path;

                if ($oldPath) {
                    $deleteAfterCommit[] = $oldPath;
                }
            } elseif ($remove) {
                $path = null;

                if ($oldPath) {
                    $deleteAfterCommit[] = $oldPath;
                }
            }

            if ($mode === self::MODE_IMAGE && ! $path) {
                throw ValidationException::withMessages([
                    "diagram_images.{$phaseKey}" => 'Carga una imagen para usar este recurso visual.',
                ]);
            }

            $media[$phaseKey] = array_filter([
                'mode' => $mode,
                'path' => $path,
            ], fn ($value) => $value !== null);
        }

        return [array_replace($validated, ['diagram_media' => $media]), $newPaths, $deleteAfterCommit];
    }

    public function sessionPayload(array $validated, array $input, array $files = [], TrainingSession $session = null): array
    {
        $oldPhases = $session
            ? $session->phases()->get(['position', 'visual_mode', 'image_path'])->keyBy('position')
            : collect();

        $newPaths = [];
        $deleteAfterCommit = [];
        $keptOldPaths = [];

        foreach (collect($validated['phases'])->values() as $index => $phase) {
            $position = $index + 1;
            $oldPhase = $oldPhases->get($position);
            $oldPath = $oldPhase?->image_path;
            $mode = $this->normalizeMode(data_get($input, "phases.{$index}.visual_mode", $oldPhase?->visual_mode ?? self::MODE_DIAGRAM));
            $remove = filter_var(data_get($input, "phases.{$index}.image_remove", false), FILTER_VALIDATE_BOOLEAN);
            $file = $this->uploadedFile(data_get($files, "phases.{$index}.image"));

            if ($mode === self::MODE_IMAGE && ! $file && (! $oldPath || $remove)) {
                throw ValidationException::withMessages([
                    "phases.{$index}.image" => 'Carga una imagen para usar este recurso visual.',
                ]);
            }
        }

        $validated['phases'] = collect($validated['phases'])->values()->map(function (array $phase, int $index) use ($input, $oldPhases, $files, &$newPaths, &$deleteAfterCommit, &$keptOldPaths): array {
            $position = $index + 1;
            $oldPhase = $oldPhases->get($position);
            $oldPath = $oldPhase?->image_path;
            $mode = $this->normalizeMode(data_get($input, "phases.{$index}.visual_mode", $oldPhase?->visual_mode ?? self::MODE_DIAGRAM));
            $remove = filter_var(data_get($input, "phases.{$index}.image_remove", false), FILTER_VALIDATE_BOOLEAN);
            $file = $this->uploadedFile(data_get($files, "phases.{$index}.image"));
            $path = $oldPath;

            if ($file instanceof UploadedFile) {
                $path = $this->store($file);
                $newPaths[] = $path;

                if ($oldPath) {
                    $deleteAfterCommit[] = $oldPath;
                }
            } elseif ($remove) {
                $path = null;

                if ($oldPath) {
                    $deleteAfterCommit[] = $oldPath;
                }
            } elseif ($oldPath) {
                $keptOldPaths[] = $oldPath;
            }

            if ($mode === self::MODE_IMAGE && ! $path) {
                throw ValidationException::withMessages([
                    "phases.{$index}.image" => 'Carga una imagen para usar este recurso visual.',
                ]);
            }

            return $phase + [
                'visual_mode' => $mode,
                'image_path' => $path,
            ];
        })->all();

        $deletedPhasePaths = $oldPhases
            ->pluck('image_path')
            ->filter()
            ->diff([...$keptOldPaths, ...collect($validated['phases'])->pluck('image_path')->filter()->all(), ...$deleteAfterCommit])
            ->all();

        return [$validated, $newPaths, [...$deleteAfterCommit, ...$deletedPhasePaths]];
    }

    private function normalizeMode(mixed $mode): string
    {
        return $mode === self::MODE_IMAGE ? self::MODE_IMAGE : self::MODE_DIAGRAM;
    }

    private function methodologyFile(array $files, string $phaseKey): ?UploadedFile
    {
        return $this->uploadedFile(data_get($files, "diagram_images.{$phaseKey}") ?? ($files["diagram_images.{$phaseKey}"] ?? null));
    }

    private function uploadedFile(mixed $value): ?UploadedFile
    {
        if ($value instanceof UploadedFile) {
            return $value;
        }

        if (is_array($value)) {
            return collect($value)->first(fn ($file) => $file instanceof UploadedFile);
        }

        return null;
    }
}
