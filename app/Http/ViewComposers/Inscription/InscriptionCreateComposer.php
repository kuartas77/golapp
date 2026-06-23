<?php

namespace App\Http\ViewComposers\Inscription;

use App\Http\ViewComposers\Payments\PaymentsViewComposer;
use App\Models\Inscription;
use App\Models\School;
use App\Models\TrainingGroup;
use App\Repositories\CompetitionGroupRepository;
use App\Repositories\TrainingGroupRepository;
use App\Service\Groups\GroupCatalogCache;
use App\Traits\Commons;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InscriptionCreateComposer
{
    use Commons;

    private TrainingGroupRepository $trainingGroupRepository;

    private CompetitionGroupRepository $competitionGroupRepository;

    public function __construct(TrainingGroupRepository $trainingGroupRepository, CompetitionGroupRepository $competitionGroupRepository, private GroupCatalogCache $groupCatalogCache)
    {
        $this->trainingGroupRepository = $trainingGroupRepository;
        $this->competitionGroupRepository = $competitionGroupRepository;
    }

    public function compose(View $view): void
    {
        if (Auth::check()) {

            $school_id = getSchool(auth()->user())->id;

            $provitionalGroup = $this->groupCatalogCache->remember(GroupCatalogCache::TRAINING, $school_id, 'provisional', function () use ($school_id) {
                return TrainingGroup::query()->orderBy('id')->firstWhere('school_id', $school_id);
            });

            $genders = Cache::remember('KEY_GENDERS', now()->addYear(), function () {
                return config('variables.KEY_GENDERS');
            });

            $positions = Cache::remember('KEY_POSITIONS', now()->addYear(), function () {
                return config('variables.KEY_POSITIONS');
            });

            $blood_types = Cache::rememberForever('KEY_BLOOD_TYPES', function () {
                return config('variables.KEY_BLOOD_TYPES');
            });

            $averages = Cache::rememberForever('KEY_AVERAGES', function () {
                return config('variables.KEY_AVERAGES');
            });

            $dominant_profile = Cache::rememberForever('KEY_DOMINANT_PROFILE', function () {
                return config('variables.KEY_DOMINANT_PROFILE');
            });

            $relationships = Cache::remember('KEY_RELATIONSHIPS_SELECT', now()->addYear(), function () {
                return config('variables.KEY_RELATIONSHIPS_SELECT');
            });

            $instructorId = isInstructor() ? auth()->id() : null;
            $training_groups = $this->groupCatalogCache->remember(GroupCatalogCache::TRAINING, $school_id, 'inscription-options', function () use ($school_id, $instructorId) {
                $filter = Closure::fromCallable([PaymentsViewComposer::class, 'filterGroupsYearActive']);

                return $this->trainingGroupRepository->getListGroupsSchedule(false, $instructorId, $filter, $school_id)->pluck('full_schedule_group', 'id');
            }, $instructorId);

            $competition_groups = $this->groupCatalogCache->remember(
                GroupCatalogCache::COMPETITION,
                $school_id,
                'inscription-options',
                fn () => $this->competitionGroupRepository->getListGroupFullName($school_id, $instructorId),
                $instructorId,
            );

            $inscription_years = Cache::remember("KEY_INSCRIPTION_YEARS_{$school_id}", now()->addMinutes(5), function () use ($school_id) {
                return Inscription::query()->where('school_id', $school_id)->distinct('year')->orderBy('year', 'desc')->pluck('year', 'year');
            });

            $categories = Cache::remember("KEY_CATEGORIES_SELECT_{$school_id}", now()->addMinutes(5), function () use ($school_id) {
                return DB::table('inscriptions')->where('school_id', $school_id)->where('year', now()->year)->orderBy('category')->groupBy('category')->select(['category'])->get();
            });

            $training_groups_arr = $this->groupCatalogCache->remember(
                GroupCatalogCache::TRAINING,
                $school_id,
                'inscription-array',
                fn () => TrainingGroup::query()
                    ->where('school_id', $school_id)
                    ->when($instructorId, fn ($query) => $query->whereRelation('instructors', function ($query) use ($instructorId): void {
                        $query->where('training_group_user.user_id', $instructorId)
                            ->where('assigned_year', now()->year);
                    }))
                    ->select(['id', 'name'])
                    ->where('year_active', now()->year)
                    ->get(),
                $instructorId,
            );

            $document_types = Cache::remember('KEY_DOCUMENT_TYPES', now()->addYear(), fn () => config('variables.KEY_DOCUMENT_TYPES'));

            $jornada = Cache::remember('KEY_JORNADA_TYPES', now()->addYear(), fn () => config('variables.KEY_JORNADA'));

            $schools = [];
            if (isAdmin()) {
                $schools = School::query()->pluck('name', 'id');
            }

            $firstGroup = $provitionalGroup;

            if (request()->routeIs('inscriptions.index')) {
                $training_groups_arr->push($firstGroup);
            }

            // dd($competition_groups);

            $view->with('jornada', $jornada);
            $view->with('schools', $schools);
            $view->with('genders', $genders);
            $view->with('averages', $averages);
            $view->with('positions', $positions);
            $view->with('categories', $categories);
            $view->with('blood_types', $blood_types);
            $view->with('relationships', $relationships);
            $view->with('document_types', $document_types);
            $view->with('training_groups', $training_groups);
            $view->with('provisional_group_id', $firstGroup->id);
            $view->with('dominant_profile', $dominant_profile);
            $view->with('inscription_years', $inscription_years);
            $view->with('competition_groups', $competition_groups);
            $view->with('training_groups_arr', $training_groups_arr);
            $view->with('provitional_group', $provitionalGroup);
        }
    }
}
