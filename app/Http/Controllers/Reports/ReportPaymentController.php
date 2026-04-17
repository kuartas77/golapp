<?php

namespace App\Http\Controllers\Reports;

use App\Models\Payment;
use App\Traits\ErrorTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\TrainingGroup;
use App\Exports\PaymentsExport;
use App\Http\Controllers\Controller;
use Illuminate\Filesystem\Filesystem;
use App\Jobs\NotifyUserOfCompletedExport;
use RealRashid\SweetAlert\Facades\Alert;

class ReportPaymentController extends Controller
{
    use ErrorTrait;

    public function index(Request $request)
    {
        return view('theme');
    }

    public function metadata(Request $request): JsonResponse
    {
        $years = Payment::query()
            ->select('year')
            ->schoolId()
            ->distinct()
            ->orderBy('year')
            ->pluck('year')
            ->prepend(now()->year)
            ->unique()
            ->sort()
            ->values();

        $defaultYear = (int) ($request->input('year') ?: $years->last() ?: now()->year);
        $groups = $this->paymentGroupOptions($defaultYear);

        return response()->json([
            'years' => $years->map(fn ($year) => [
                'value' => (int) $year,
                'label' => (string) $year,
            ])->values(),
            'groups' => $groups,
            'defaultYear' => $defaultYear,
        ]);
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

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'El archivo será enviado al correo electrónico registrado.',
                ], 202);
            }

            Alert::info("El Archivo será enviado al correo electronico.");

        } catch (\Throwable $th) {
            $this->logError("ReportPaymentController@report", $th);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('messages.error_general'),
                ], 500);
            }

            Alert::error(env('APP_NAME'), __('messages.error_general'));
        }

        return back();
    }

    private function paymentGroupOptions(int $year)
    {
        return TrainingGroup::whereRelation('payments', 'year', '=', $year)
            ->schoolId()
            ->orderBy('name')
            ->get()
            ->map(fn ($group) => [
                'value' => $group->id,
                'label' => $group->full_schedule_group,
            ])
            ->values();
    }
}
