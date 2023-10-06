<?php

namespace App\Http\Controllers\SchoolPages;

use App\Models\School;
use App\Traits\ErrorTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Service\API\RegisterService;
use App\Http\Requests\SchoolUpdateRequest;

class SchoolsController extends Controller
{
    use ErrorTrait;

    public function index(Request $request, School $school)
    {
        $school->load(['settingsValues']);

        view()->share('school', $school);
        view()->share('notify_payment_day', $school->settings['NOTIFY_PAYMENT_DAY'] ?? 16);
        view()->share('inscription_amount', $school->settings['INSCRIPTION_AMOUNT'] ?? 70000);
        view()->share('monthly_payment', $school->settings['MONTHLY_PAYMENT'] ?? 50000);
        view()->share('annuity', $school->settings['ANNUITY'] ?? 48333);
        return view('admin.school.index');
    }

    public function update(SchoolUpdateRequest $request, School $school, RegisterService $registerService)
    {
        $registerService->updateSchoolUsesCase($request, $school);

        return redirect(route('school.index', ['school' => $school]));
    }
}
