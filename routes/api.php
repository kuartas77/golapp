<?php

use App\Http\Controllers\API\Admin\ContractController as AdminContractController;
use App\Http\Controllers\API\Admin\GroupAssignmentController;
use App\Http\Controllers\API\Admin\InscriptionController;
use App\Http\Controllers\API\Admin\InscriptionCustomChargeController;
use App\Http\Controllers\API\Admin\InscriptionLimitController;
use App\Http\Controllers\API\Admin\InscriptionSummaryController;
use App\Http\Controllers\API\Admin\InvoiceCustomItemController as AdminInvoiceCustomItemController;
use App\Http\Controllers\API\Admin\RegisterController;
use App\Http\Controllers\API\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\API\Admin\SchoolController;
use App\Http\Controllers\API\Admin\SchoolDataExportController;
use App\Http\Controllers\API\Admin\TournamentController as AdminTournamentController;
use App\Http\Controllers\API\Admin\UsersController;
use App\Http\Controllers\API\AttendanceQrController;
use App\Http\Controllers\API\AuthControllerSPA;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\Instructor\AssistsController;
use App\Http\Controllers\API\Instructor\GroupsController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\MethodologyRecordController;
use App\Http\Controllers\API\Notifications\HeaderNotificationsController;
use App\Http\Controllers\API\Portal\GuardianAuthController;
use App\Http\Controllers\API\Portal\GuardianEvaluationController;
use App\Http\Controllers\API\Portal\GuardianPlayerController;
use App\Http\Controllers\API\Portal\GuardianProfileController;
use App\Http\Controllers\API\ProfileController as ApiProfileController;
use App\Http\Controllers\API\TrainingSessionsController as ApiTrainingSessionsController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\Assists\AssistController;
use App\Http\Controllers\BackOffice\SchoolController as BackOfficeShoolController;
use App\Http\Controllers\Competition\GameController;
use App\Http\Controllers\DataTableController;
use App\Http\Controllers\Evaluations\EvaluationTemplateController;
use App\Http\Controllers\Evaluations\PlayerEvaluationComparisonController;
use App\Http\Controllers\Evaluations\PlayerEvaluationController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Groups\CompetitionGroupController;
use App\Http\Controllers\Groups\TrainingGroupController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\Inscription\InscriptionController as WebInscriptions;
use App\Http\Controllers\Inventory\InventoryProductController;
use App\Http\Controllers\Invoices\InvoiceController;
use App\Http\Controllers\Invoices\ItemInvoicesController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\Notifications\PaymentRequestController;
use App\Http\Controllers\Notifications\TopicNotificationsController;
use App\Http\Controllers\Notifications\UniformRequestsController;
use App\Http\Controllers\Payments\MonthlyPaymentReceiptController;
use App\Http\Controllers\Payments\PaymentController;
use App\Http\Controllers\Payments\TournamentPayoutsController;
use App\Http\Controllers\Players\PlayerController;
use App\Http\Controllers\PlayerStatsController;
use App\Http\Controllers\Competition\CompetitionStatsController;
use App\Http\Controllers\Portal\ContractController as PortalContract;
use App\Http\Controllers\Portal\InscriptionsController as PortalInscription;
use App\Http\Controllers\Portal\SchoolsController as PortalSchool;
use App\Http\Controllers\Reports\ReportAssistsController;
use App\Http\Controllers\Reports\ReportAttendancePaymentController;
use App\Http\Controllers\Reports\ReportDebtorController;
use App\Http\Controllers\Reports\ReportInstructorActivityController;
use App\Http\Controllers\Reports\ReportPaymentController;
use App\Http\Controllers\SchoolPages\SchoolsController;
use App\Http\Controllers\SchoolOutings\SchoolOutingController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('logout', [LoginController::class, 'logout']);
    Route::post('refresh-token', [LoginController::class, 'refresh'])->name('api.refresh');

    Route::get('check', [UserController::class, 'check']);
    Route::get('user', [UserController::class, 'user']);

    Route::get('img/dynamic/{file}', [FileController::class, 'fileStorageServe'])->where(['file' => '.*']);

    Route::prefix('instructor')->name('instructor.')->middleware(['auth:sanctum'])->group(function () {

        Route::apiResource('training_groups', GroupsController::class, ['only' => ['index', 'show']]);

        Route::get('statistics/groups', [GroupsController::class, 'statistics']);
        Route::get('attendances', [AssistsController::class, 'index']);
        Route::post('attendances/upsert', [AssistsController::class, 'upsert']);
    });

    // Legacy API admin surface disabled during security review.
    // Keep the block here for traceability until we confirm why these routes still exist.
    // Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum'])->name('v1.')->group(function (){
    //
    //     Route::post('register', [RegisterController::class, 'register']);
    //     Route::apiResource('users', UsersController::class);
    //     Route::apiResource('inscriptions', InscriptionController::class);
    //     Route::apiResource('schools', SchoolController::class);
    // });
});

Route::prefix('v2')->group(function () {

    Route::post('login', [AuthControllerSPA::class, 'login']);
    Route::post('forgot-password', [AuthControllerSPA::class, 'forgotPassword']);
    Route::post('reset-password', [AuthControllerSPA::class, 'resetPassword']);

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post('logout', [AuthControllerSPA::class, 'logout']);

        Route::prefix('settings')->group(function () {
            Route::get('general', [SettingsController::class, 'index']);
            Route::get('groups', [SettingsController::class, 'configGroups']);
        });

        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('kpis', [DashboardController::class, 'kpis'])->middleware('role:super-admin|school|instructor');

        Route::get('user', [UserController::class, 'user']);
        Route::get('profile', [ApiProfileController::class, 'show']);
        Route::put('profile', [ApiProfileController::class, 'update']);

        Route::prefix('admin')->group(function () {
            Route::get('info_campus', [BackOfficeShoolController::class, 'infoCampus']);
            Route::post('change_school', [BackOfficeShoolController::class, 'choose']);
        });

        Route::prefix('admin')->middleware(['role:super-admin|school'])->group(function () {
            Route::middleware('school.permission:school.module.school_profile')->group(function () {
                Route::get('school', [SchoolsController::class, 'index']);
                Route::put('school/{school}', [SchoolsController::class, 'update']);
            });

            Route::middleware('school.permission:school.module.contracts')->prefix('contracts')->group(function () {
                Route::get('', [AdminContractController::class, 'index']);
                Route::put('{contractTypeCode}', [AdminContractController::class, 'update']);
            });

            Route::middleware('school.permission:school.module.billing')->name('api.')->group(function () {
                Route::apiResource('invoice-items-custom', AdminInvoiceCustomItemController::class);
                Route::get('inscription-custom-charges', [InscriptionCustomChargeController::class, 'index']);
                Route::put('inscription-custom-charges/{charge}', [InscriptionCustomChargeController::class, 'update']);
                Route::delete('inscription-custom-charges/{charge}', [InscriptionCustomChargeController::class, 'destroy']);
            });

            Route::middleware('school.permission:school.module.user_management')->group(function () {
                Route::get('users/{user}/profile', [UsersController::class, 'profile']);
                Route::apiResource('users', UsersController::class);
            });

            Route::middleware('school.permission:school.module.training_groups')->group(function () {
                Route::apiResource('training_groups', TrainingGroupController::class, ['only' => ['show', 'store', 'update']]);
                Route::apiResource('schedules', AdminScheduleController::class, ['except' => ['create', 'edit']])
                    ->names('admin.schedules');
                Route::get('training-groups/board', [GroupAssignmentController::class, 'trainingBoard']);
                Route::post('training-groups/move', [GroupAssignmentController::class, 'moveTraining']);
            });

            Route::middleware('school.permission:school.module.competition_groups')->group(function () {
                Route::apiResource('competition_groups', CompetitionGroupController::class, ['only' => ['show', 'store', 'update']]);
                Route::apiResource('tournaments', AdminTournamentController::class, ['except' => ['create', 'edit']])
                    ->names('admin.tournaments');
                Route::get('competition-groups/board', [GroupAssignmentController::class, 'competitionBoard']);
                Route::post('competition-groups/move', [GroupAssignmentController::class, 'moveCompetition']);
            });

            Route::middleware(['role:super-admin'])->group(function () {
                Route::get('schools/options', [SchoolController::class, 'options']);
                Route::get('schools/{school}/permissions', [SchoolController::class, 'permissions']);
                Route::put('schools/{school}/permissions', [SchoolController::class, 'updatePermissions']);
                Route::get('schools/{school}/data-exports', [SchoolDataExportController::class, 'index']);
                Route::post('schools/{school}/data-exports', [SchoolDataExportController::class, 'store']);
                Route::get('schools/{school}/data-exports/{dataExport}', [SchoolDataExportController::class, 'show']);
                Route::get('schools/{school}/data-exports/{dataExport}/download', [SchoolDataExportController::class, 'download']);
                Route::get('schools/{school}', [SchoolController::class, 'show']);
                Route::post('schools', [SchoolController::class, 'store']);
                Route::put('schools/{school}', [SchoolController::class, 'update']);
            });

            Route::middleware(['role:super-admin'])->prefix('evaluation-templates')->group(function () {
                Route::get('options', [EvaluationTemplateController::class, 'options']);
                Route::get('', [EvaluationTemplateController::class, 'index']);
                Route::post('', [EvaluationTemplateController::class, 'store']);
                Route::get('{evaluationTemplate}', [EvaluationTemplateController::class, 'show']);
                Route::put('{evaluationTemplate}', [EvaluationTemplateController::class, 'update']);
                Route::patch('{evaluationTemplate}', [EvaluationTemplateController::class, 'update']);
                Route::patch('{evaluationTemplate}/status', [EvaluationTemplateController::class, 'updateStatus']);
                Route::post('{evaluationTemplate}/duplicate', [EvaluationTemplateController::class, 'duplicate']);
                Route::delete('{evaluationTemplate}', [EvaluationTemplateController::class, 'destroy']);
            });
        });

        Route::apiResource('training_groups', GroupsController::class, ['only' => ['index', 'show']]);
        Route::get('training_group/classdays', [TrainingGroupController::class, 'getClassDays']);

        Route::middleware([
            'role:super-admin|school',
            'school.permission:school.module.players',
        ])->group(function () {
            Route::post('import/players', [ImportController::class, 'importPlayers']);
            Route::apiResource('players', PlayerController::class, ['only' => ['edit', 'show', 'update']]);
        });

        Route::middleware('school.permission:school.module.payments')->group(function () {
            Route::get('payments/monthly-receipts', [MonthlyPaymentReceiptController::class, 'index'])
                ->name('api.payments.monthly-receipts.index');
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
            Route::get('{trainingSession}', [ApiTrainingSessionsController::class, 'show']);
            Route::put('{trainingSession}', [ApiTrainingSessionsController::class, 'update']);
            Route::delete('{trainingSession}', [ApiTrainingSessionsController::class, 'destroy'])
                ->middleware('role:super-admin|school');
        });

        Route::middleware('school.permission:school.module.methodology')->prefix('methodology-records')->group(function () {
            Route::get('', [MethodologyRecordController::class, 'index']);
            Route::post('', [MethodologyRecordController::class, 'store']);
            Route::get('{methodologyRecord}', [MethodologyRecordController::class, 'show']);
            Route::put('{methodologyRecord}', [MethodologyRecordController::class, 'update']);
            Route::delete('{methodologyRecord}', [MethodologyRecordController::class, 'destroy']);
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
            'school.permission:school.module.players',
        ])->group(function () {
            Route::get('/player-stats', [PlayerStatsController::class, 'index']);
            Route::get('/top-players', [PlayerStatsController::class, 'topPlayers']);
            Route::get('/player/{id}/detail', [PlayerStatsController::class, 'playerDetail']);
        });

        Route::prefix('datatables')->group(function () {
            Route::middleware([
                'role:super-admin|school',
                'school.permission:school.module.inscriptions',
            ])->group(function () {
                Route::get('inscriptions_enabled', [DataTableController::class, 'enabledInscriptions']);
                Route::get('inscriptions_disabled', [DataTableController::class, 'disabledInscriptions']);
            });

            Route::middleware('school.permission:school.module.training_groups')->group(function () {
                Route::get('training_groups_enabled', [DataTableController::class, 'enabledTrainingGroups']);
                Route::get('training_groups_retired', [DataTableController::class, 'disabledTrainingGroups']);
            });

            Route::middleware('school.permission:school.module.competition_groups')->group(function () {
                Route::get('competition_groups_enabled', [DataTableController::class, 'enabledCompetitionGroups']);
                Route::get('competition_groups_retired', [DataTableController::class, 'disabledCompetitionGroups']);
            });

            Route::middleware('school.permission:school.module.training_groups')->group(function () {
                Route::get('schedules_enabled', [DataTableController::class, 'enabledSchedules']);
            });
            Route::middleware([
                'role:super-admin|school',
                'school.permission:school.module.players',
            ])->group(function () {
                Route::get('players_enabled', [DataTableController::class, 'enabledPlayers']);
            });

            Route::middleware('school.permission:school.module.training_sessions')->group(function () {
                Route::get('training_sessions_enabled', [DataTableController::class, 'trainingSessions']);
            });
            Route::middleware('school.permission:school.module.methodology')->group(function () {
                Route::get('methodology_records', [DataTableController::class, 'methodologyRecords']);
            });
            Route::middleware('school.permission:school.module.evaluations')->group(function () {
                Route::get('player_evaluations', [DataTableController::class, 'playerEvaluations']);
            });
            Route::middleware([
                'role:super-admin|school',
                'school.permission:school.module.inventory',
            ])->group(function () {
                Route::get('inventory_products', [DataTableController::class, 'inventoryProducts']);
                Route::get('inventory_movements', [DataTableController::class, 'inventoryMovements']);
            });
            Route::middleware('school.permission:school.module.user_management')->group(function () {
                Route::get('users_enabled', [DataTableController::class, 'enabledUsers']);
            });

            Route::middleware('school.permission:school.module.matches')->group(function () {
                Route::get('matches', [DataTableController::class, 'matches']);
            });

            Route::middleware(['role:super-admin'])->group(function () {
                Route::get('schools', [DataTableController::class, 'schools']);
                Route::get('schools_info', [DataTableController::class, 'schoolsInfo']);
            });
        });

        Route::prefix('autocomplete')->group(function () {
            // Route::get('autocomplete', [MasterController::class, 'autoComplete'])->name('autocomplete.fields');
            // Route::get('identification_document_exists', [MasterController::class, 'existDocument'])->name('autocomplete.document_exists');
            // Route::get('code_unique_verify', [MasterController::class, 'codeUniqueVerify'])->name('autocomplete.verify_code');
            Route::get('list_code_unique', [MasterController::class, 'listUniqueCode']);
            Route::get('search_unique_code', [MasterController::class, 'searchUniqueCode']);
            // Route::get('list_code_unique_inscription', [MasterController::class, 'listUniqueCodeWithInscription'])->name('autocomplete.list_code_unique_inscription');
            Route::get('competition_groups', [MasterController::class, 'competitionGroupsByTournament']);
            Route::get('tournaments', [MasterController::class, 'tournamentsBySchool']);
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

    });

    Route::prefix('portal')->name('portal.')->group(function () {

        Route::get('escuelas/data', [PortalSchool::class, 'indexData'])->name('school.index.data');
        Route::get('escuelas/{school}/data', [PortalSchool::class, 'showData'])->name('school.show.data');
        Route::get('escuelas/{school}/contracts/{contractTypeCode}', [PortalContract::class, 'show'])->name('school.contract.show');

        Route::post('{school}/inscripcion', [PortalInscription::class, 'store'])->name('school.inscription.store');

        Route::prefix('autocomplete')->group(function () {
            Route::get('autocomplete', [MasterController::class, 'autoComplete'])->name('autocomplete.fields');
            Route::get('search_doc', [MasterController::class, 'searchDoc'])->name('autocomplete.search_doc');
        });

        Route::get('dynamic/{file}', [FileController::class, 'fileStorageServe'])->where(['file' => '.*'])->name('player.images');

        Route::prefix('acudientes')->name('guardians.')->group(function () {
            Route::post('login', [GuardianAuthController::class, 'login'])->name('login');
            Route::post('forgot-password', [GuardianAuthController::class, 'forgotPassword'])->name('forgot-password');
            Route::post('reset-password', [GuardianAuthController::class, 'resetPassword'])->name('reset-password');

            Route::middleware(['auth:sanctum', 'ensure.guardian'])->group(function () {
                Route::get('me', [GuardianAuthController::class, 'me'])->name('me');
                Route::post('logout', [GuardianAuthController::class, 'logout'])->name('logout');
                Route::put('profile', [GuardianProfileController::class, 'update'])->name('profile.update');

                Route::get('players', [GuardianPlayerController::class, 'index'])->name('players.index');
                Route::get('players/{player}', [GuardianPlayerController::class, 'show'])->name('players.show');
                Route::put('players/{player}', [GuardianPlayerController::class, 'update'])->name('players.update');
                Route::get('players/{player}/inscription-report/{inscription?}', [GuardianPlayerController::class, 'inscriptionReport'])->name('players.inscription-report');
                Route::get('evaluations/{evaluation}/pdf', [GuardianEvaluationController::class, 'pdf'])->name('evaluations.pdf');
                Route::get('inscriptions/{inscription}/comparison', [GuardianPlayerController::class, 'comparison'])->name('inscriptions.comparison');
            });
        });
    });

});
