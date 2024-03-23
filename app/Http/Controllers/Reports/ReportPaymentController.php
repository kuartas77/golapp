<?php

namespace App\Http\Controllers\Reports;

use App\Models\Payment;
use App\Traits\ErrorTrait;
use Illuminate\Http\Request;
use App\Models\TrainingGroup;
use App\Exports\PaymentsExport;
use App\Http\Controllers\Controller;
use Illuminate\Filesystem\Filesystem;
use App\Jobs\NotifyUserOfCompletedExport;

class ReportPaymentController extends Controller
{
    use ErrorTrait;

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $groups = TrainingGroup::whereRelation('payments', 'year', '=', $request->input('year', now()->year))
                ->schoolId()->get()->map(function ($group) {
                return ['id' => $group->id, 'text' => $group->full_schedule_group];
            });
            $groups->prepend(['id' => 0, 'text' => 'Selecciona una opción...']);
            return response()->json($groups);
        }

        $years = Payment::schoolId()->distinct()->pluck('year', 'year');

        return view('reports.payments.index', compact('years'));
    }

    public function report(Request $request)
    {
        try {
            $date = now()->format('U');
            $year = $request->input('year');
            $groupName = ' ';
            $request->merge(['school_id' => getSchool(auth()->user())->id]);

            if($request->filled('training_group_id') && $request->training_group_id != 0){
                $groupName = TrainingGroup::find($request->training_group_id)->full_group;
                $groupName = " grupo {$groupName} ";
            }

            $file = new Filesystem;
            $file->cleanDirectory(storage_path('app/public/exports'));

            $filename = "Pagos del año {$year}{$groupName}{$date}.xlsx";

            (new PaymentsExport($request->all(), $request->input('deleted', false)))->queue($filename, 'export')->chain([
                new NotifyUserOfCompletedExport(auth()->user(), $filename),
            ]);

            alert()->info("El Archivo será enviado al correo electronico.");

        } catch (\Throwable $th) {
            $this->logError("ReportPaymentController@report", $th);
            alert()->error(env('APP_NAME'), __('messages.error_general'));
        }

        return back();
    }
}
