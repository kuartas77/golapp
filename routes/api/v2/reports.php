<?php

use App\Http\Controllers\Reports\ReportAssistsController;
use App\Http\Controllers\Reports\ReportAttendancePaymentController;
use App\Http\Controllers\Reports\ReportDebtorController;
use App\Http\Controllers\Reports\ReportInstructorActivityController;
use App\Http\Controllers\Reports\ReportPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('school.permission:school.module.reports')->prefix('reports')->name('reports.')->group(function () {
    Route::get('assists', [ReportAssistsController::class, 'metadata'])->name('assists.metadata');
    Route::get('payments', [ReportPaymentController::class, 'metadata'])->name('payments.metadata');
    Route::post('payments', [ReportPaymentController::class, 'report'])->name('payments.report');
    Route::get('debtors', [ReportDebtorController::class, 'metadata'])->name('debtors.metadata');
    Route::get('debtors/pdf', [ReportDebtorController::class, 'pdf'])->name('debtors.pdf');
    Route::middleware('role:super-admin|school')->prefix('instructors')->group(function () {
        Route::get('activity/metadata', [ReportInstructorActivityController::class, 'metadata'])
            ->name('instructors.activity.metadata');
        Route::get('activity', [ReportInstructorActivityController::class, 'activity'])
            ->name('instructors.activity');
    });
    Route::get('attendance-payment', [ReportAttendancePaymentController::class, 'metadata'])->name('attendance-payment.metadata');
    Route::get('attendance-payment/monthly-by-group', [ReportAttendancePaymentController::class, 'monthlyByGroup'])->name('attendance-payment.monthly-by-group');
    Route::get('attendance-payment/monthly-by-player', [ReportAttendancePaymentController::class, 'monthlyByPlayer'])->name('attendance-payment.monthly-by-player');
    Route::get('attendance/monthly-by-player', [ReportAssistsController::class, 'monthlyByPlayer'])->name('assists.monthly-by-player');
    Route::get('attendance/monthly-by-group', [ReportAssistsController::class, 'monthlyByGroup'])->name('assists.monthly-by-group');
    Route::get('attendance/annual-consolidated', [ReportAssistsController::class, 'annualConsolidated'])->name('assists.annual-consolidated');
});
