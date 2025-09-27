<?php

namespace App\Http\Controllers;

use App\Http\ViewComposers\Payments\PaymentsViewComposer;
use App\Models\Inscription;
use App\Models\School;
use App\Models\TrainingGroup;
use App\Repositories\CompetitionGroupRepository;
use App\Repositories\TrainingGroupRepository;
use App\Traits\Commons;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    use Commons;

    public function __construct(
        private TrainingGroupRepository $trainingGroupRepository,
        private CompetitionGroupRepository $competitionGroupRepository
    ) {
        //
    }

    public function index()
    {
        $school_id = getSchool(auth()->user())->id;
        $user_id = auth()->id();

        $training_groups = Cache::remember("KEY_TRAINING_GROUPS_{$school_id}.{$user_id}", now()->addMinutes(5), function () {
            $filter = Closure::fromCallable([PaymentsViewComposer::class, 'filterGroupsYearActive']);
            if (isSchool() || isAdmin()) {
                return $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, filter: $filter);
            } else {

                return $this->trainingGroupRepository->getListGroupsSchedule(deleted: false, user_id: auth()->id(), filter: $filter);
            }
        });

        $firstGroup = Cache::remember('PROVISIONAL_GROUP_' . $school_id, now()->addYear(), fn() => TrainingGroup::orderBy('id')->firstWhere('school_id', $school_id));
        $training_groups->push($firstGroup);


        $categories = Cache::remember(
            "KEY_CATEGORIES_SELECT_{$school_id}",
            now()->addMinutes(5),
            fn() =>
            DB::table('inscriptions')->where('school_id', $school_id)->orderBy('category')->groupBy('category')->select(['category'])->get()
        );

        $genders = Cache::remember('KEY_GENDERS', now()->addYear(), fn() => config('variables.KEY_GENDERS'));

        $positions = Cache::remember('KEY_POSITIONS', now()->addYear(), fn() => config('variables.KEY_POSITIONS'));

        $blood_types = Cache::rememberForever('KEY_BLOOD_TYPES', fn() => config('variables.KEY_BLOOD_TYPES'));

        $averages = Cache::rememberForever('KEY_AVERAGES', fn() => config('variables.KEY_AVERAGES'));

        $dominant_profile = Cache::rememberForever('KEY_DOMINANT_PROFILE', fn() => config('variables.KEY_DOMINANT_PROFILE'));

        $relationships = Cache::remember('KEY_RELATIONSHIPS_SELECT', now()->addYear(), fn() => config('variables.KEY_RELATIONSHIPS_SELECT'));

        $competition_groups = Cache::remember(
            "KEY_COMPETITION_GROUPS_{$school_id}",
            now()->addMinutes(5),
            fn() =>
            $this->competitionGroupRepository->getListGroupFullName()
        );

        $inscription_years = Cache::remember(
            "KEY_INSCRIPTION_YEARS_{$school_id}",
            now()->addMinutes(5),
            fn() =>
            Inscription::query()->where('school_id', $school_id)->distinct('year')->orderBy('year')->select(['year as id', 'year'])->get()
        );

        $document_types = Cache::remember('KEY_DOCUMENT_TYPES', now()->addYear(), fn() => config('variables.KEY_DOCUMENT_TYPES'));

        $jornada = Cache::remember('KEY_JORNADA_TYPES', now()->addYear(), fn() => config('variables.KEY_JORNADA'));

        $schools = [];
        if (isAdmin()) {
            $schools = School::query()->select(['id', 'name'])->get();
        }


        return response()->json([
            't_groups' => $training_groups,
            'categories' => $categories,
            'genders' => $genders,
            'positions' => $positions,
            'blood_types' => $blood_types,
            'averages' => $averages,
            'dominant_profile' => $dominant_profile,
            'relationships' => $relationships,
            'competition_groups' => $competition_groups,
            'inscription_years' => $inscription_years,
            'document_types' => $document_types,
            'jornada' => $jornada,
            'schools' => $schools,
        ]);
    }
}
