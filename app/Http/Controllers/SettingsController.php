<?php

namespace App\Http\Controllers;

use App\Http\ViewComposers\Payments\PaymentsViewComposer;
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
        private CompetitionGroupRepository $competitionGroupRepository)
    {
        //
    }

    public function index(Request $request)
    {
        $school_id = getSchool(auth()->user())->id;

        $training_groups_arr = Cache::remember("KEY_TRAINING_GROUPS_ARR_{$school_id}", now()->addMinutes(5), function () {
            return TrainingGroup::schoolId()->select(['id', 'name'])->where('year_active', now()->year)->get();
        });

        $categories = Cache::remember("KEY_CATEGORIES_SELECT_{$school_id}", now()->addMinutes(5), function() use($school_id){
            return DB::table('inscriptions')->where('school_id', $school_id)->orderBy('category')->groupBy('category')->select(['category'])->get();
        });

        return response()->json([
            't_groups' => $training_groups_arr,
            'categories' => $categories
        ]);
    }

}
