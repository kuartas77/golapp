<?php


namespace App\Http\ViewComposers\Inscription;


use App\Models\School;
use App\Traits\Commons;
use App\Models\Inscription;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TrainingGroupRepository;
use App\Repositories\CompetitionGroupRepository;
use App\Http\ViewComposers\Payments\PaymentsViewComposer;

class InscriptionCreateComposer
{
    use Commons;

    /**
     * @var TrainingGroupRepository
     */
    private $trainingGroupRepository;
    /**
     * @var CompetitionGroupRepository
     */
    private $competitionGroupRepository;

    public function __construct(TrainingGroupRepository $trainingGroupRepository, CompetitionGroupRepository $competitionGroupRepository)
    {
        $this->trainingGroupRepository = $trainingGroupRepository;
        $this->competitionGroupRepository = $competitionGroupRepository;
    }

    public function compose(View $view)
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

            $training_groups = Cache::remember("KEY_TRAINING_GROUPS_{$school_id}", now()->addDay(), function () {
                $filter = \Closure::fromCallable([PaymentsViewComposer::class, 'filterGroupsYearActive']);
                return $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, filter: $filter);
            });

            $competition_groups = Cache::remember("KEY_COMPETITION_GROUPS_{$school_id}", now()->addDay(), function () {
                return $this->competitionGroupRepository->getListGroupFullName();
            });

            $inscription_years = Cache::remember("KEY_INSCRIPTION_YEARS_{$school_id}", now()->addDay(), function () {
                return Inscription::query()->distinct('year')->orderBy('year')->pluck('year', 'year');
            });

            $schools = [];
            if (isAdmin()) {
                $schools = School::query()->pluck('name', 'id');
            }

            $view->with('schools', $schools);
            $view->with('genders', $genders);
            $view->with('averages', $averages);
            $view->with('positions', $positions);
            $view->with('blood_types', $blood_types);
            $view->with('relationships', $relationships);
            $view->with('training_groups', $training_groups);
            $view->with('dominant_profile', $dominant_profile);
            $view->with('inscription_years', $inscription_years);
            $view->with('competition_groups', $competition_groups);
        }
    }

}
