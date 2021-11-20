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

            $genders = Cache::remember('KEY_GENDERS', now()->addYear(), function () {
                return config('variables.KEY_GENDERS');
            });

            $positions = Cache::remember('KEY_POSITIONS', now()->addYear(), function () {
                return config('variables.KEY_POSITIONS');
            });

            $blood_types = Cache::remember('KEY_BLOOD_TYPES', now()->addYear(), function () {
                return config('variables.KEY_BLOOD_TYPES');
            });

            $averages = Cache::remember('KEY_AVERAGES', now()->addYear(), function () {
                return config('variables.KEY_AVERAGES');
            });

            $training_groups = Cache::remember('KEY_TRAINING_GROUPS', now()->addYear(), function () {
                return $this->trainingGroupRepository->getListGroupsSchedule(false);
            });

            $dominant_profile = Cache::remember('KEY_DOMINANT_PROFILE', now()->addYear(), function () {
                return config('variables.KEY_DOMINANT_PROFILE');
            });

            $competition_groups = Cache::remember('KEY_COMPETITION_GROUPS', now()->addYear(), function () {
                return $this->competitionGroupRepository->getListGroupFullName();
            });

            $relationships = Cache::remember('KEY_RELATIONSHIPS_SELECT', now()->addYear(), function () {
                return config('variables.KEY_RELATIONSHIPS_SELECT');
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
