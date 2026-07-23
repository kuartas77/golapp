<?php

use App\Http\Controllers\API\Admin\InscriptionCustomChargeController;
use App\Http\Controllers\API\Admin\InscriptionLimitController;
use App\Http\Controllers\API\Admin\InscriptionSummaryController;
use App\Http\Controllers\API\AttendanceQrController;
use App\Http\Controllers\API\Instructor\GroupsController;
use App\Http\Controllers\API\MethodologyRecordController;
use App\Http\Controllers\API\Notifications\HeaderNotificationsController;
use App\Http\Controllers\API\SchoolDocumentController;
use App\Http\Controllers\API\SessionPlanningController;
use App\Http\Controllers\API\TrainingSessionsController as ApiTrainingSessionsController;
use App\Http\Controllers\Assists\AssistController;
use App\Http\Controllers\Competition\CompetitionStatsController;
use App\Http\Controllers\Competition\GameController;
use App\Http\Controllers\Evaluations\PlayerEvaluationComparisonController;
use App\Http\Controllers\Evaluations\PlayerEvaluationController;
use App\Http\Controllers\Groups\TrainingGroupController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\Inscription\InscriptionController as WebInscriptions;
use App\Http\Controllers\Inventory\InventoryProductController;
use App\Http\Controllers\Invoices\InvoiceController;
use App\Http\Controllers\Invoices\ItemInvoicesController;
use App\Http\Controllers\Notifications\PaymentRequestController;
use App\Http\Controllers\Notifications\TopicNotificationsController;
use App\Http\Controllers\Notifications\UniformRequestsController;
use App\Http\Controllers\Payments\MonthlyPaymentReceiptController;
use App\Http\Controllers\Payments\PaymentController;
use App\Http\Controllers\Payments\TournamentPayoutsController;
use App\Http\Controllers\Players\PlayerController;
use App\Http\Controllers\PlayerCredits\PlayerCreditController;
use App\Http\Controllers\PlayerStatsController;
use App\Http\Controllers\SchoolOutings\SchoolOutingController;
use Illuminate\Support\Facades\Route;

Route::apiResource('training_groups', GroupsController::class, ['only' => ['index', 'show']]);
Route::get('training_group/classdays', [TrainingGroupController::class, 'getClassDays']);

Route::middleware([
    'role:super-admin|school',
    'school.permission:school.module.players',
])->group(function () {
    Route::post('import/players', [ImportController::class, 'importPlayers']);
    Route::get('players/{player}/financial-clearance', [PlayerController::class, 'financialClearanceStatus']);
    Route::get('players/{player}/financial-clearance/pdf', [PlayerController::class, 'financialClearancePdf']);
    Route::apiResource('players', PlayerController::class, ['only' => ['edit', 'show', 'update']]);
});

Route::middleware('school.permission:school.module.payments')->group(function () {
    Route::get('payments/monthly-receipts', [MonthlyPaymentReceiptController::class, 'index'])
        ->name('payments.monthly-receipts.index');
    Route::get('payments/status-catalog', [PaymentController::class, 'statusCatalog'])
        ->name('payments.status-catalog');
    Route::post('payments/bulk-update', [PaymentController::class, 'bulkUpdate'])
        ->name('payments.bulk-update');
    Route::get('payments/{payment}/history', [PaymentController::class, 'history'])
        ->name('payments.history');
    Route::apiResource('payments', PaymentController::class)->only(['index', 'update', 'show']);
});

Route::middleware([
    'role:super-admin|school',
    'school.permission:school.module.school_outings',
])->prefix('school-outings')->group(function () {
    Route::get('', [SchoolOutingController::class, 'index']);
    Route::post('', [SchoolOutingController::class, 'store']);
    Route::get('{outing}', [SchoolOutingController::class, 'show']);
    Route::put('{outing}', [SchoolOutingController::class, 'update']);
    Route::patch('{outing}/status', [SchoolOutingController::class, 'updateStatus']);
    Route::get('{outing}/eligible-inscriptions', [SchoolOutingController::class, 'eligibleInscriptions']);
    Route::post('{outing}/participants', [SchoolOutingController::class, 'addParticipants']);
    Route::delete('{outing}/participants/{participant}', [SchoolOutingController::class, 'removeParticipant']);
    Route::post('{outing}/activities', [SchoolOutingController::class, 'storeActivity']);
    Route::put('{outing}/activities/{activity}', [SchoolOutingController::class, 'updateActivity']);
    Route::delete('{outing}/activities/{activity}', [SchoolOutingController::class, 'destroyActivity']);
    Route::post('{outing}/contributions', [SchoolOutingController::class, 'storeContribution']);
});

Route::middleware([
    'role:super-admin|school',
    'school.permission:school.module.player_credits',
])->prefix('player-credits')->group(function () {
    Route::get('', [PlayerCreditController::class, 'index']);
    Route::get('datatable', [PlayerCreditController::class, 'datatable']);
    Route::get('{player:id}', [PlayerCreditController::class, 'show']);
    Route::post('{player:id}/movements', [PlayerCreditController::class, 'storeMovement']);
});

Route::middleware('school.permission:school.module.attendances')->group(function () {
    Route::post('assists/bulk-update', [AssistController::class, 'bulkUpdate']);
    Route::apiResource('assists', AssistController::class)->except(['create', 'edit', 'destroy']);
});

Route::middleware([
    'school.permission:school.module.attendances',
    'role:super-admin|school|instructor',
])->group(function () {
    Route::get('attendance-qr/{unique_code}', [AttendanceQrController::class, 'show']);
    Route::post('attendance-qr/{assist}/take', [AttendanceQrController::class, 'take']);
});

Route::middleware([
    'role:super-admin|school',
    'school.permission:school.module.inscriptions',
])->group(function () {
    Route::get('inscriptions/limit-summary', InscriptionLimitController::class);
    Route::get('inscriptions/{inscription}/summary', [InscriptionSummaryController::class, 'show']);
    Route::get('inscriptions/{inscription}/custom-charges', [InscriptionCustomChargeController::class, 'byInscription']);
    Route::resource('inscriptions', WebInscriptions::class)->except(['index', 'create', 'show']);
});

Route::middleware('school.permission:school.module.matches')->group(function () {
    Route::apiResource('matches', GameController::class)->except(['index', 'edit', 'create']);
});

Route::middleware([
    'role:super-admin|school|instructor',
    'school.permission:school.module.matches',
])->prefix('competition-stats')->group(function () {
    Route::get('', [CompetitionStatsController::class, 'index']);
    Route::get('groups/{group}', [CompetitionStatsController::class, 'show']);
});

Route::middleware('school.permission:school.module.training_sessions')->prefix('training-sessions')->group(function () {
    Route::post('', [ApiTrainingSessionsController::class, 'store']);
    Route::get('attendance-context', [ApiTrainingSessionsController::class, 'attendanceContext']);
    Route::get('{trainingSession}', [ApiTrainingSessionsController::class, 'show']);
    Route::put('{trainingSession}', [ApiTrainingSessionsController::class, 'update']);
    Route::delete('{trainingSession}', [ApiTrainingSessionsController::class, 'destroy'])
        ->middleware('role:super-admin|school');
});

Route::middleware('school.permission:school.module.session_planning')->prefix('session-plannings')->group(function () {
    Route::post('', [SessionPlanningController::class, 'store']);
    Route::get('attendance-context', [SessionPlanningController::class, 'attendanceContext']);
    Route::get('{sessionPlanning}', [SessionPlanningController::class, 'show']);
    Route::put('{sessionPlanning}', [SessionPlanningController::class, 'update']);
    Route::delete('{sessionPlanning}', [SessionPlanningController::class, 'destroy']);
});

Route::middleware('school.permission:school.module.methodology')->prefix('methodology-records')->group(function () {
    Route::get('', [MethodologyRecordController::class, 'index']);
    Route::post('', [MethodologyRecordController::class, 'store']);
    Route::get('{methodologyRecord}', [MethodologyRecordController::class, 'show']);
    Route::put('{methodologyRecord}', [MethodologyRecordController::class, 'update']);
    Route::delete('{methodologyRecord}', [MethodologyRecordController::class, 'destroy']);
});

Route::middleware([
    'role:super-admin|school',
    'school.permission:school.module.club_documents',
])->prefix('club-documents')->name('club-documents.')->group(function () {
    Route::get('', [SchoolDocumentController::class, 'index'])->name('index');
    Route::post('', [SchoolDocumentController::class, 'store'])->name('store');
    Route::get('{schoolDocument}/download', [SchoolDocumentController::class, 'download'])->name('download');
    Route::delete('{schoolDocument}', [SchoolDocumentController::class, 'destroy'])->name('destroy');
});

Route::middleware([
    'role:super-admin|school|instructor',
    'school.permission:school.module.document_planning',
])->prefix('document-planning')->name('document-planning.')->group(function () {
    Route::get('', [SchoolDocumentController::class, 'index'])->name('index');
    Route::post('', [SchoolDocumentController::class, 'store'])->name('store');
    Route::get('{schoolDocument}/download', [SchoolDocumentController::class, 'download'])->name('download');
    Route::delete('{schoolDocument}', [SchoolDocumentController::class, 'destroy'])->name('destroy');
});

Route::middleware([
    'school.permission:school.module.billing',
    'school.permission:school.feature.system_notify',
])->prefix('notifications')->group(function () {
    Route::get('header-summary', [HeaderNotificationsController::class, 'index']);
    Route::get('payment-requests', [PaymentRequestController::class, 'index']);
    Route::get('payment-requests/{paymentRequest}/proof', [PaymentRequestController::class, 'proof'])->name('notifications.payment-requests.proof');
    Route::get('uniform-requests', [UniformRequestsController::class, 'index']);
    Route::put('invoice/{invoice}/payment-request/{paymentRequest}', [InvoiceController::class, 'update']);
});

Route::middleware('school.permission:school.feature.system_notify')->prefix('notifications/topics')->group(function () {
    Route::get('', [TopicNotificationsController::class, 'index']);
    Route::get('options', [TopicNotificationsController::class, 'options']);
    Route::post('', [TopicNotificationsController::class, 'store']);
});

Route::middleware([
    'role:super-admin|school|instructor',
    'school.permission:school.module.matches',
])->group(function () {
    Route::get('/player-stats', [PlayerStatsController::class, 'index']);
    Route::get('/top-players', [PlayerStatsController::class, 'topPlayers']);
    Route::get('/player/{id}/detail', [PlayerStatsController::class, 'playerDetail']);
});

Route::prefix('tournament-payouts')->group(function () {
    Route::get('', [TournamentPayoutsController::class, 'searchRaw']);
    Route::post('', [TournamentPayoutsController::class, 'store']);
    Route::put('{tournamentpayout}', [TournamentPayoutsController::class, 'update']);
});

Route::middleware('school.permission:school.module.billing')->prefix('invoices')->group(function () {
    Route::get('', [InvoiceController::class, 'index']);
    Route::post('', [InvoiceController::class, 'store']);
    Route::get('create/{inscription}', [InvoiceController::class, 'create']);
    Route::get('{invoice}', [InvoiceController::class, 'show']);
    Route::delete('{invoice}', [InvoiceController::class, 'destroy']);
    Route::post('{invoice}/payment', [InvoiceController::class, 'addPayment']);
    Route::get('{invoice}/print', [InvoiceController::class, 'print']);
    Route::get('items/invoices', [ItemInvoicesController::class, 'index']);
    Route::get('items/invoices/export-pending', [ItemInvoicesController::class, 'exportPending']);
});

Route::middleware('school.permission:school.module.evaluations')->prefix('player-evaluations')->group(function () {
    Route::get('options', [PlayerEvaluationController::class, 'options']);
    Route::get('comparison', [PlayerEvaluationComparisonController::class, 'index']);
    Route::get('create', [PlayerEvaluationController::class, 'create']);
    Route::get('', [PlayerEvaluationController::class, 'index']);
    Route::post('', [PlayerEvaluationController::class, 'store']);
    Route::get('{playerEvaluation}/edit', [PlayerEvaluationController::class, 'edit']);
    Route::get('{playerEvaluation}', [PlayerEvaluationController::class, 'show']);
    Route::put('{playerEvaluation}', [PlayerEvaluationController::class, 'update']);
    Route::patch('{playerEvaluation}', [PlayerEvaluationController::class, 'update']);
    Route::delete('{playerEvaluation}', [PlayerEvaluationController::class, 'destroy']);
});

Route::middleware([
    'role:super-admin|school',
    'school.permission:school.module.inventory',
])->prefix('inventory')->group(function () {
    Route::get('products', [InventoryProductController::class, 'index']);
    Route::post('products', [InventoryProductController::class, 'store']);
    Route::get('products/{product}', [InventoryProductController::class, 'show']);
    Route::put('products/{product}', [InventoryProductController::class, 'update']);
    Route::post('products/{product}/movements', [InventoryProductController::class, 'movement']);
});
