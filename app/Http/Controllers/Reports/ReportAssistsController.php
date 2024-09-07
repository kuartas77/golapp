<?php

namespace App\Http\Controllers\Reports;

use App\Models\Assist;
use Illuminate\Http\Request;
use App\Models\TrainingGroup;
use App\Http\Controllers\Controller;
use App\Traits\ErrorTrait;

class ReportAssistsController extends Controller
{
    use ErrorTrait;

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $groups = TrainingGroup::whereRelation('assists', 'year', '=', $request->input('year', now()->year))
                ->schoolId()->get()->map(function ($group) {
                return ['id' => $group->id, 'text' => $group->full_schedule_group];
            });
            $groups->prepend(['id' => 0, 'text' => 'Selecciona una opción...']);
            return response()->json($groups);
        }
        $years = Assist::schoolId()->distinct()->pluck('year', 'year');

        return view('reports.assists.index', compact('years'));
    }

    public function report(Request $request)
    {
        try {
            $date = now()->format('U');
            $year = $request->input('year');
            $groupName = ' ';

            if($request->filled('training_group_id')){
                $groupName = TrainingGroup::find($request->training_group_id)->full_group;
                $groupName = " grupo {$groupName} ";
            }

            $filename = "Asistencias del año {$year}{$groupName}{$date}.xlsx";







        } catch (\Throwable $th) {
            $this->logError("ReportAssistsController@report", $th);
            alert()->error(env('APP_NAME'), __('messages.error_general'));
        }
    }
}
