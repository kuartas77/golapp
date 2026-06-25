<?php

declare(strict_types=1);

namespace App\Service\School;

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CurrentSchoolContext
{
    public const SESSION_KEY = 'selected_school_id';

    private array $allowedIdsByUser = [];

    public function current(?User $user = null): School
    {
        $user ??= auth()->user();
        abort_unless($user instanceof User, 401);

        $allowedIds = $this->allowedSchoolIds($user);
        abort_if($allowedIds->isEmpty(), 403, 'El usuario no tiene escuelas habilitadas.');

        $selectedId = $this->selectedSchoolId($user);
        if (! $allowedIds->contains($selectedId)) {
            $selectedId = $this->fallbackSchoolId($user, $allowedIds);
            $this->storeSelection($user, $selectedId);
        }

        $prefix = $this->cachePrefix($user);
        $key = School::cacheKeyFor($prefix, $selectedId);

        $school = Cache::remember(
            $key,
            now()->addMinutes((int) env('SESSION_LIFETIME', 120)),
            fn () => School::query()->with('settingsValues')->find($selectedId)
        );

        if (! $school) {
            $allowedIds = $this->allowedSchoolIds($user);
            abort_if($allowedIds->isEmpty(), 403, 'El usuario no tiene escuelas habilitadas.');
            $selectedId = $this->fallbackSchoolId($user, $allowedIds);
            $this->storeSelection($user, $selectedId);
            $school = School::query()->with('settingsValues')->findOrFail($selectedId);
        }

        return $school;
    }

    public function allowedSchools(?User $user = null): Collection
    {
        $user ??= auth()->user();
        abort_unless($user instanceof User, 401);

        return School::query()
            ->without('settingsValues')
            ->whereIn('id', $this->allowedSchoolIds($user))
            ->orderBy('name')
            ->get();
    }

    public function allowedSchoolIds(User $user): Collection
    {
        if (isset($this->allowedIdsByUser[$user->id])) {
            return $this->allowedIdsByUser[$user->id];
        }

        if ($user->hasAnyRole(['super-admin'])) {
            return $this->allowedIdsByUser[$user->id] = School::query()
                ->without('settingsValues')
                ->pluck('id')
                ->map(fn ($id) => (int) $id);
        }

        $ids = collect([(int) $user->school_id]);

        if ($user->hasAnyRole(['school'])) {
            $primary = School::query()->with('settingsValues')->find($user->school_id);
            $configured = $primary?->settings?->get('MULTIPLE_SCHOOLS');
            $configured = is_string($configured) ? json_decode($configured, true) : $configured;
            $ids = $ids->merge(is_array($configured) ? $configured : []);
        }

        if ($user->hasAnyRole(['instructor'])) {
            $ids = $ids->merge(
                DB::table('schools_user')->where('user_id', $user->id)->pluck('school_id')
            );
        }

        $ids = $ids
            ->filter(fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        return $this->allowedIdsByUser[$user->id] = School::query()
            ->without('settingsValues')
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);
    }

    public function select(int $schoolId, ?User $user = null): bool
    {
        $user ??= auth()->user();
        abort_unless($user instanceof User, 401);
        abort_unless($this->allowedSchoolIds($user)->contains($schoolId), 403, 'No tienes acceso a esta escuela.');
        abort_unless(School::query()->whereKey($schoolId)->exists(), 404);

        $this->storeSelection($user, $schoolId);
        $this->current($user);

        return true;
    }

    public function initialize(User $user): void
    {
        $allowed = $this->allowedSchoolIds($user);
        if ($allowed->isNotEmpty()) {
            $this->storeSelection($user, $this->fallbackSchoolId($user, $allowed));
        }
    }

    private function selectedSchoolId(User $user): int
    {
        $default = max((int) $user->school_id, 1);
        $legacyKey = $this->legacySessionKey($user);
        $value = Session::get(self::SESSION_KEY, Session::get($legacyKey, $default));
        $validated = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $validated !== false ? (int) $validated : $default;
    }

    private function storeSelection(User $user, int $schoolId): void
    {
        Session::put(self::SESSION_KEY, $schoolId);
        Session::put($this->legacySessionKey($user), $schoolId);
    }

    private function fallbackSchoolId(User $user, Collection $allowedIds): int
    {
        $primary = (int) $user->school_id;

        return $allowedIds->contains($primary) ? $primary : (int) $allowedIds->first();
    }

    private function legacySessionKey(User $user): string
    {
        if ($user->hasAnyRole(['super-admin'])) {
            return 'admin.selected_school';
        }

        if ($user->hasAnyRole(['school'])) {
            return 'school.selected_school';
        }

        return 'instructor.selected_school';
    }

    private function cachePrefix(User $user): string
    {
        if ($user->hasAnyRole(['super-admin'])) {
            return School::CACHE_PREFIX_ADMIN;
        }

        if ($user->hasAnyRole(['school'])) {
            return School::CACHE_PREFIX_SCHOOL;
        }

        return '';
    }
}
