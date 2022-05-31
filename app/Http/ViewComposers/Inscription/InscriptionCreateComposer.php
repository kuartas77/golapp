<?php


namespace App\Http\ViewComposers\Inscription;


use App\Models\CompetitionGroup;
use App\Repositories\CompetitionGroupRepository;
use App\Repositories\TrainingGroupRepository;
use App\Traits\Commons;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

            $school_id = auth()->user()->school_id;

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
                return $this->trainingGroupRepository->getListGroupsSchedule(false);
            });

            $competition_groups = Cache::remember("KEY_COMPETITION_GROUPS_{$school_id}", now()->addDay(), function () {
                return $this->competitionGroupRepository->getListGroupFullName();
            });

            $view->with('genders', $genders);
            $view->with('averages', $averages);
            $view->with('positions', $positions);
            $view->with('blood_types', $blood_types);
            $view->with('relationships', $relationships);
            $view->with('training_groups', $training_groups);
            $view->with('dominant_profile', $dominant_profile);
            $view->with('competition_groups', $competition_groups);
        }
    }

}
