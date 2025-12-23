<?php

namespace App\Http\Controllers;

use App\Http\ViewComposers\Payments\PaymentsViewComposer;
use App\Models\Inscription;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Repositories\CompetitionGroupRepository;
use App\Repositories\TrainingGroupRepository;
use App\Traits\Commons;
use Closure;
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

        $firstGroup = TrainingGroup::orderBy('id')->firstWhere('school_id', $school_id);
        $allGroups = $training_groups;
        $allGroups->prepend($firstGroup);

        $categories = Cache::remember(
            "KEY_CATEGORIES_SELECT_{$school_id}",
            now()->addMinutes(5),
            fn() =>
            DB::table('inscriptions')->where('school_id', $school_id)->where('year', now()->year)->orderBy('category')->groupBy('category')->select(['category'])->get()
        );

        $genders = Cache::remember('KEY_GENDERS', now()->addYear(), fn() => config('variables.KEY_GENDERS'));

        $positions = Cache::remember('KEY_POSITIONS', now()->addYear(), fn() =>
            collect(config('variables.KEY_POSITIONS'))
                ->values()
                ->map(fn($item) => ['id'=>$item, 'name'=>$item])
        );

        $blood_types = Cache::rememberForever('KEY_BLOOD_TYPES', fn() => config('variables.KEY_BLOOD_TYPES'));

        $averages = Cache::rememberForever('KEY_AVERAGES', fn() => config('variables.KEY_AVERAGES'));

        $dominant_profile = Cache::rememberForever('KEY_DOMINANT_PROFILE', fn() => config('variables.KEY_DOMINANT_PROFILE'));

        $relationships = Cache::remember('KEY_RELATIONSHIPS_SELECT', now()->addYear(), fn() => config('variables.KEY_RELATIONSHIPS_SELECT'));

        $optionsAssist = Cache::remember("KEY_ASSIST_{$school_id}", now()->addYear(), fn() => config('variables.KEY_ASSIST'));
        $optionsPayment = Cache::remember("KEY_PAYMENTS_SELECT_{$school_id}", now()->addYear(), fn() => config('variables.KEY_PAYMENTS_SELECT'));

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
            'all_t_groups' => $allGroups,
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
            'type_assistance' => $optionsAssist,
            'type_payments' => $optionsPayment,
        ]);
    }

    public function configGroups()
    {
        $school = getSchool(auth()->user());
        $school_id = $school->id;

        $users = Cache::remember("KEY_USERS_{$school_id}", now()->addMinute(), function() use($school){
            return $school->users()->get(['users.id', 'users.name'])->map(fn($user) => ['id'=>$user->id, 'name'=>$user->name]);
        });

        $schedules = Cache::remember("SCHEDULES_{$school_id}",
            now()->addMinute(),
            fn() => Schedule::query()->schoolId()->get(['schedule']))->map(fn($item) => ['id'=>$item->schedule, 'name'=>$item->schedule]);

        $tournaments = Cache::remember("KEY_TOURNAMENT_{$school_id}", now()->addMinutes(10), fn() => Tournament::orderBy('name')->schoolId()->get(['name', 'id']))->map(fn($item) => ['id'=>$item->id, 'name'=>$item->name]);

        $year_active = Cache::remember("KEY_YEARS_{$school_id}", now()->addDay(), function () {
            $now = now();
            $years = [];
            $years[$now->format('Y')] = $now->format('Y');

            if(in_array($now->month, [10, 11, 12])) {
                $year = $now->addYear()->format('Y');
                $years[$year] = $year;
            }
            return $years;
        });

        $categories = Cache::remember("KEY_CATEGORIES_{$school_id}", now()->addDay(), function () {
            $categories = [];
            for ($i = now()->subYears(18)->year; $i <= now()->subYears(2)->year; $i++) {
                $categorie = categoriesName($i); //SUB-
                array_push($categories, ['id' => $categorie, 'name' => $categorie]);
            }
            return $categories;
        });

        return response()->json([
            'users' => $users,
            'year_active' => $year_active,
            'schedules' => $schedules,
            'categories' => $categories,
            'tournaments' => $tournaments,
        ]);
    }
}
