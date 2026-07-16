<?php

namespace App\Service\Settings;

use App\Http\ViewComposers\Payments\PaymentsViewComposer;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Repositories\CompetitionGroupRepository;
use App\Repositories\TrainingGroupRepository;
use App\Service\Groups\GroupCatalogCache;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingsCatalogService
{
    public function __construct(private TrainingGroupRepository $trainingGroups, private CompetitionGroupRepository $competitionGroups, private GroupCatalogCache $catalogCache) {}

    public function general(School $school, int $userId, bool $instructor, bool $admin): array
    {
        $schoolId = (int) $school->id;
        $instructorId = $instructor ? $userId : null;
        $groups = $this->catalogCache->remember(GroupCatalogCache::TRAINING, $schoolId, 'settings', function () use ($schoolId, $instructorId) {
            return $this->trainingGroups->getListGroupsSchedule(false, $instructorId, Closure::fromCallable([PaymentsViewComposer::class, 'filterGroupsYearActive']), $schoolId);
        }, $instructorId);
        $attendanceGroups = $this->catalogCache->remember(GroupCatalogCache::TRAINING, $schoolId, 'settings-attendances', function () use ($schoolId, $instructorId, $instructor) {
            return $this->trainingGroups->getListGroupsSchedule(
                false,
                $instructorId,
                Closure::fromCallable([PaymentsViewComposer::class, 'filterGroupsYearActive']),
                $schoolId,
                $instructor
            );
        }, $instructorId);
        $allGroups = collect($groups->all());
        $firstGroup = TrainingGroup::orderBy('id')->firstWhere('school_id', $schoolId);
        if (!$instructor && $firstGroup && !$allGroups->contains('id', $firstGroup->id)) $allGroups->prepend($firstGroup);

        return [
            'all_t_groups' => $allGroups, 't_groups' => $groups,
            'attendance_training_groups' => $attendanceGroups,
            'normal_training_groups' => $groups->reject(fn ($group) => $group->is_complementary)->values(),
            'complementary_training_groups' => $groups->filter(fn ($group) => $group->is_complementary)->values(),
            'categories' => Cache::remember("KEY_CATEGORIES_SELECT_{$schoolId}", now()->addMinutes(5), fn () => DB::table('inscriptions')->where('school_id', $schoolId)->where('year', now()->year)->orderBy('category')->groupBy('category')->select('category')->get()),
            'genders' => $this->options('KEY_GENDERS'),
            'positions' => Cache::remember('KEY_POSITIONS', now()->addYear(), fn () => collect(config('variables.KEY_POSITIONS'))->values()->map(fn ($item) => ['id' => $item, 'name' => $item])),
            'blood_types' => $this->options('KEY_BLOOD_TYPES'), 'averages' => $this->options('KEY_AVERAGES'),
            'dominant_profile' => $this->options('KEY_DOMINANT_PROFILE'), 'relationships' => $this->options('KEY_RELATIONSHIPS_SELECT'),
            'competition_groups' => $this->catalogCache->remember(GroupCatalogCache::COMPETITION, $schoolId, 'settings', fn () => $this->competitionGroups->getListGroupFullName($schoolId, $instructorId), $instructorId),
            'inscription_years' => Cache::remember("KEY_INSCRIPTION_YEARS_{$schoolId}", now()->addMinutes(5), fn () => DB::table('inscriptions')->where('school_id', $schoolId)->distinct('year')->orderBy('year')->select(['year as id', 'year'])->get()),
            'document_types' => $this->options('KEY_DOCUMENT_TYPES'), 'jornada' => $this->options('KEY_JORNADA_TYPES', 'KEY_JORNADA'),
            'schools' => $admin ? School::query()->select(['id', 'name'])->get() : [],
            'type_assistance' => $this->options('KEY_ASSIST'), 'type_payments' => $this->options('KEY_PAYMENTS_SELECT'),
            'training_session_general_objectives' => $this->options('KEY_TRAINING_SESSION_GENERAL_OBJECTIVE'),
            'training_session_specific_goals' => $this->options('KEY_TRAINING_SESSION_SPECIFIC_GOAL'),
            'training_session_contents' => $this->options('KEY_TRAINING_SESSION_CONTENT'),
            'training_session_tasks' => $this->options('KEY_TRAINING_SESSION_TASKS'),
            'settings' => $school->settings, 'current_school_id' => $schoolId,
        ];
    }

    public function groups(School $school): array
    {
        $id = (int) $school->id;
        $years = Cache::remember("KEY_YEARS_{$id}", now()->addDay(), function () { $now = now(); $years = [$now->format('Y') => $now->format('Y')]; if (in_array($now->month, [10, 11, 12])) $years[$now->addYear()->format('Y')] = $now->format('Y'); return $years; });
        return [
            'users' => Cache::remember("KEY_USERS_{$id}", now()->addMinute(), fn () => $school->users()->get(['users.id', 'users.name'])->map(fn ($user) => ['id' => $user->id, 'name' => $user->name])),
            'year_active' => $years,
            'schedules' => Cache::remember("SCHEDULES_{$id}", now()->addMinute(), fn () => Schedule::query()->schoolId()->get(['schedule']))->map(fn ($item) => ['id' => $item->schedule, 'name' => $item->schedule]),
            'categories' => Cache::remember("KEY_CATEGORIES_{$id}", now()->addDay(), fn () => collect(range(now()->subYears(18)->year, now()->subYears(2)->year))->map(fn ($year) => ['id' => categoriesName($year), 'name' => categoriesName($year)])),
            'tournaments' => Cache::remember("KEY_TOURNAMENT_{$id}", now()->addMinutes(2), fn () => Tournament::orderBy('name')->schoolId()->get(['name', 'id'])->map(fn ($item) => ['id' => $item->id, 'name' => $item->name])),
        ];
    }

    private function options(string $cacheKey, ?string $configKey = null): array
    {
        $options = config('variables.'.($configKey ?? $cacheKey), []);
        $versionedCacheKey = $cacheKey.'_'.md5(json_encode($options));

        return Cache::remember($versionedCacheKey, now()->addYear(), fn () => collect($options)->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])->values()->all());
    }
}
