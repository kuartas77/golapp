<?php


namespace App\Http\ViewComposers\Inscription;


use Closure;
use App\Models\School;
use App\Traits\Commons;
use App\Models\Inscription;
use App\Models\TrainingGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TrainingGroupRepository;
use App\Repositories\CompetitionGroupRepository;
use App\Http\ViewComposers\Payments\PaymentsViewComposer;

class InscriptionCreateComposer
{
    use Commons;

    private TrainingGroupRepository $trainingGroupRepository;
    private CompetitionGroupRepository $competitionGroupRepository;

    public function __construct(TrainingGroupRepository $trainingGroupRepository, CompetitionGroupRepository $competitionGroupRepository)
    {
        $this->trainingGroupRepository = $trainingGroupRepository;
        $this->competitionGroupRepository = $competitionGroupRepository;
    }

    public function compose(View $view): void
    {
        if (Auth::check()) {

            $school_id = getSchool(auth()->user())->id;

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

            $training_groups = Cache::remember("KEY_TRAINING_GROUPS_{$school_id}", now()->addMinutes(5), function () {
                $filter = Closure::fromCallable([PaymentsViewComposer::class, 'filterGroupsYearActive']);
                return $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, filter: $filter);
            });

            $competition_groups = Cache::remember("KEY_COMPETITION_GROUPS_{$school_id}", now()->addMinutes(5), function () {
                return $this->competitionGroupRepository->getListGroupFullName();
            });

            $inscription_years = Cache::remember("KEY_INSCRIPTION_YEARS_{$school_id}", now()->addMinutes(5), function () use($school_id) {
                return Inscription::query()->where('school_id', $school_id)->distinct('year')->orderBy('year')->pluck('year', 'year');
            });

            $categories = Cache::remember("KEY_CATEGORIES_SELECT_{$school_id}", now()->addMinutes(5), function() use($school_id){
                return DB::table('inscriptions')->where('school_id', $school_id)->orderBy('category')->groupBy('category')->select(['category'])->get();
            });

            $training_groups_arr = Cache::remember("KEY_TRAINING_GROUPS_ARR_{$school_id}", now()->addMinutes(5), function () {
                return TrainingGroup::schoolId()->select(['id', 'name'])->where('year_active', now()->year)->get();
            });

            $document_types = Cache::remember('KEY_DOCUMENT_TYPES', now()->addYear(), fn() => config('variables.KEY_DOCUMENT_TYPES'));

            $jornada = Cache::remember('KEY_JORNADA_TYPES', now()->addYear(), fn() => config('variables.KEY_JORNADA'));

            $schools = [];
            if (isAdmin()) {
                $schools = School::query()->pluck('name', 'id');
            }

            $firstGroup = Cache::remember('PROVISIONAL_GROUP_'.$school_id, now()->addYear(), fn () => TrainingGroup::orderBy('id')->firstWhere('school_id', $school_id));

            if (request()->routeIs('inscriptions.index')) {
                $training_groups_arr->push($firstGroup);
            }

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
        }
    }

}
