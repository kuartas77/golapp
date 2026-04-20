<?php

use App\Http\Controllers\{Admin\UserController, Assists\AssistController, Players\PlayerController};
use App\Http\Controllers\{Competition\GameController, Payments\PaymentController, Schedule\SchedulesController, SchoolPages\SchoolsController};
use App\Http\Controllers\{HomeController, ExportController, MasterController, ProfileController};
use App\Http\Controllers\{Players\PlayerExportController, Tournaments\TournamentController, Inscription\InscriptionController};
use App\Http\Controllers\AppController;
use App\Http\Controllers\{HistoricController, IncidentController, DataTableController};
use App\Http\Controllers\Admin\InvoiceCustomItemController;
use App\Http\Controllers\Evaluations\PlayerEvaluationController;
use App\Http\Controllers\Evaluations\PlayerEvaluationComparisonController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Groups\{CompetitionGroupController, InscriptionCGroupController, InscriptionTGroupController, TrainingGroupController};
use App\Http\Controllers\ImportController;
use App\Http\Controllers\Invoices\InvoiceController;
use App\Http\Controllers\Invoices\ItemInvoicesController;
use App\Http\Controllers\Notifications\PaymentRequestController;
use App\Http\Controllers\Notifications\TopicNotificationsController;
use App\Http\Controllers\Notifications\UniformRequestsController;
use App\Http\Controllers\Payments\TournamentPayoutsController;
use App\Http\Controllers\Reports\AttendancePaymentReportExportController;
use App\Http\Controllers\Reports\AttendanceReportExportController;
use App\Http\Controllers\Reports\ReportAttendancePaymentController;
use App\Http\Controllers\Reports\ReportAssistsController;
use App\Http\Controllers\Reports\ReportPaymentController;
use Illuminate\Support\Facades\Route;

// Route::get('/{any}', [AppController::class, 'index'])->where('any', '.*');
// Auth::routes(['register' => false, 'verify' => false]);

// Route::get('/', fn() => redirect('login'));

Route::get('img/dynamic/{file}', [FileController::class, 'fileStorageServe'])->where(['file' => '.*'])->name('images');

Route::middleware(['auth', 'verified_school'])->group(function () {

    // El dashboard SPA renderiza resources/js/pages/kpi/Index.vue y consume sus datos desde GET /api/v2/kpis.
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/birthdays', [HomeController::class, 'birthDays'])->name('birthDays');

    // La SPA equivalente vive en resources/js/router/index.js y consume sus datos desde routes/api.php.
    Route::middleware('school.permission:school.module.inscriptions')->group(function () {
        Route::post('inscriptions/activate/{id}', [InscriptionController::class, 'activate'])->name('inscriptions.activate');
        Route::resource("inscriptions", InscriptionController::class)->except(['create','show']);
    });

    // La SPA equivalente vive en resources/js/router/index.js y consume sus datos desde routes/api.php.
    Route::middleware('school.permission:school.module.payments')->group(function () {
        Route::resource("payments", PaymentController::class)->only(['index','update', 'show']);
        Route::get('statuses/payments', [PaymentController::class, 'paymentStatuses'])->name('payments.status');
    });

    // La SPA equivalente vive en resources/js/router/index.js y consume sus datos desde routes/api.php.
    Route::middleware('school.permission:school.module.attendances')->group(function () {
        Route::resource("assists", AssistController::class)->except(['create','edit', 'destroy']);
    });

    // La SPA equivalente vive en resources/js/router/index.js y consume sus datos desde routes/api.php.
    Route::middleware('school.permission:school.module.matches')->group(function () {
        Route::resource("matches", GameController::class)->except(['show']);
    });

    // La SPA equivalente vive en resources/js/router/index.js y consume sus datos desde routes/api.php.
    Route::middleware('school.permission:school.module.players')->group(function () {
        Route::resource("players", PlayerController::class);
    });
    // El composable Vue resources/js/composables/tournament_payouts.js consume ahora:
    // GET /api/v2/autocomplete/tournaments,
    // GET /api/v2/autocomplete/competition_groups y
    // GET|POST|PUT /api/v2/tournament-payouts.
    Route::resource("tournamentpayout", TournamentPayoutsController::class)->only(['index', 'store', 'update']);

    // La SPA equivalente vive en resources/js/router/index.js y consume su CRUD/listado desde routes/api.php:
    // GET /api/v2/training-sessions/{trainingSession}, POST|PUT /api/v2/training-sessions y
    // GET /api/v2/datatables/training_sessions_enabled.
    Route::middleware('school.permission:school.module.training_sessions')->group(function () {
        Route::get('training-sessions', [AppController::class, 'index'])->name('training-sessions.index');
        Route::redirect('training-sessions/create', 'training-sessions');
    });

    Route::prefix('import')->group(function(){
        Route::middleware('school.permission:school.module.matches')->group(function () {
            Route::post('matches/{match}', [ImportController::class, 'importMatchDetail'])->name('import.match');
        });
        Route::post('players', [ImportController::class, 'importPlayers'])->name('import.players');
    });

    Route::resource("profiles", ProfileController::class)->except(['index','create','store','destroy']);

    Route::prefix('admin')->middleware(['role:super-admin|school'])->group(function (){

        Route::middleware('school.permission:school.module.user_management')->group(function () {
            Route::resource('users', UserController::class);
        });

        Route::resources([
            'schedules' => SchedulesController::class,
            'incidents' => IncidentController::class,
            'tournaments' => TournamentController::class,
        ]);

        Route::middleware('school.permission:school.module.training_groups')->group(function () {
            Route::resource('training_groups', TrainingGroupController::class);
        });

        Route::middleware('school.permission:school.module.competition_groups')->group(function () {
            Route::resource('competition_groups', CompetitionGroupController::class);
        });

        Route::middleware('school.permission:school.module.school_profile')->group(function () {
            Route::get('school/{school}', [SchoolsController::class, 'index'])->name('school.index');
            Route::put('school/{school}', [SchoolsController::class, 'update'])->name('school.update');
        });

        Route::middleware('school.permission:school.module.training_groups')->group(function () {
            Route::get('filter_training_groups', [TrainingGroupController::class, 'filterGroupYear'])->name('training_groups.filter');
            Route::get('availability_training_groups/{training_group?}', [TrainingGroupController::class, 'availabilityGroup'])->name('training_groups.availability');
        });

        Route::middleware('school.permission:school.module.training_groups')->group(function () {
            Route::get('inscription_training',[InscriptionTGroupController::class, 'index'])->name('ins_training.index');
            Route::get('inscription_training/{training_group}', [InscriptionTGroupController::class, 'makeRows'])->name('ins_training.make');
            Route::post('inscription_training/{inscription_id}', [InscriptionTGroupController::class, 'assignGroup'])->name('ins_training.assign');
        });

        Route::middleware('school.permission:school.module.competition_groups')->group(function () {
            Route::get('inscription_competition',[InscriptionCGroupController::class, 'index'])->name('ins_competition.index');
            Route::get('inscription_competition/{competition_group}', [InscriptionCGroupController::class, 'makeRows'])->name('ins_competition.make');
            Route::post('inscription_competition/{inscription}', [InscriptionCGroupController::class, 'assignGroup'])->name('ins_competition.change');
            Route::get('availability_competition_groups/{competition_groups?}', [CompetitionGroupController::class, 'availabilityGroup'])->name('competition_groups.availability');
        });

        Route::middleware('school.permission:school.module.user_management')->group(function () {
            Route::post('users/activate/{id}', [UserController::class, 'activate'])->name('users.activate');
        });

        Route::middleware('school.permission:school.module.billing')->group(function () {
            // UpdateSchool.vue consume el CRUD equivalente desde /api/v2/admin/invoice-items-custom.
            Route::resource("invoice-items-custom", InvoiceCustomItemController::class)->except(['create']);
        });

    });

    Route::prefix('datatables')->group(function () {
        Route::middleware('school.permission:school.module.inscriptions')->group(function () {
            // La SPA de inscripciones usa los equivalentes en routes/api.php:
            // GET /api/v2/datatables/inscriptions_enabled y /api/v2/datatables/inscriptions_disabled.
            Route::get('inscriptions_enabled', [DataTableController::class, 'enabledInscriptions'])->name('inscriptions.enabled');
            Route::get('inscriptions_disabled', [DataTableController::class, 'disabledInscriptions'])->name('inscriptions.disabled');
        });

        Route::middleware('school.permission:school.module.training_groups')->group(function () {
            Route::get('training_groups_enabled', [DataTableController::class, 'enabledTrainingGroups'])->name('training_groups.enabled');
            Route::get('training_groups_retired', [DataTableController::class, 'disabledTrainingGroups'])->name('training_groups.retired');
        });

        Route::middleware('school.permission:school.module.competition_groups')->group(function () {
            Route::get('competition_groups_enabled', [DataTableController::class, 'enabledCompetitionGroups'])->name('competition_groups.enabled');
            Route::get('competition_groups_retired', [DataTableController::class, 'disabledCompetitionGroups'])->name('competition_groups.retired');
        });

        Route::get('schedules_enabled', [DataTableController::class, 'enabledSchedules'])->name('schedules.enabled');
        Route::middleware('school.permission:school.module.players')->group(function () {
            Route::get('players_enabled', [DataTableController::class, 'enabledPlayers'])->name('players.enabled');
        });
        Route::middleware('school.permission:school.module.training_sessions')->group(function () {
            Route::get('training_sessions_enabled', [DataTableController::class, 'trainingSessions'])->name('training_sessions.enabled');
        });
        Route::middleware('school.permission:school.module.user_management')->group(function () {
            Route::get('users_enabled', [DataTableController::class, 'enabledUsers'])->name('users_enabled');
        });
    });

    Route::prefix('export')->name('export.')->group(function () {
        Route::middleware('school.permission:school.module.players')->group(function () {
            Route::get('player/{player}/pdf', [PlayerExportController::class, 'exportPlayerPDF'])->name('player');
        });

        Route::middleware('school.permission:school.module.inscriptions')->group(function () {
            // La vista SPA resources/js/pages/inscriptions/InscriptionsList.vue mantiene estas rutas web
            // para exportaciones binarias y consume el listado por API desde routes/api.php.
            Route::get('inscription/{player_id}/{inscription_id}/{year?}/{quarter?}', [PlayerExportController::class, 'exportInscription'])->name('inscription');
            Route::get('inscriptions/excel', [PlayerExportController::class, 'exportInscriptionsExcel'])->name('inscriptions');
        });

        Route::middleware('school.permission:school.module.attendances')->group(function () {
            Route::get('assists/pdf/{training_group_id}/{year}/{month}/{deleted?}', [ExportController::class, 'exportAssistsPDF'])->name('pdf.assists');
            Route::get('assists/excel/{training_group_id}/{year}/{month}/{deleted?}', [ExportController::class, 'exportAssistsExcel'])->name('assists');
        });

        Route::middleware('school.permission:school.module.matches')->group(function () {
            Route::get('matches/pdf/{match}', [ExportController::class, 'exportMatchPDF'])->name('pdf.match');
            Route::get('matches/create/{competition_group}/format', [ExportController::class, 'exportMatchDetail'])->name('match_detail');
        });

        Route::get('incidents/pdf/{slug_name}', [ExportController::class, 'exportIncidentsPDF'])->name('pdf.incidents');

        Route::middleware('school.permission:school.module.payments')->group(function () {
            Route::get('payments/excel', [ExportController::class, 'exportPaymentsExcel'])->name('payments.excel');
            Route::get('payments/pdf', [ExportController::class, 'exportPaymentsPDF'])->name('payments.pdf');
        });

        Route::get('tournament/payouts/excel', [ExportController::class, 'exportTournamentPayoutsExcel'])->name('tournaments.payouts.excel');
        Route::get('tournament/payouts/pdf', [ExportController::class, 'exportTournamentPayoutsPDF'])->name('tournaments.payouts.pdf');
        Route::middleware('school.permission:school.module.training_sessions')->group(function () {
            Route::get('training_sessions/pdf/{id}', [ExportController::class, 'exportTrainingSession'])->name('training_sessions.pdf');
        });
        Route::middleware('school.permission:school.module.billing')->group(function () {
            Route::get('items/invoices', [ExportController::class, 'exportPendingItemsInvoices'])->name('items.invoices');
        });

        Route::middleware('school.permission:school.module.reports')->group(function () {
            Route::get('{report}/{format}', [AttendanceReportExportController::class, 'download'])
            ->whereIn('report', ['monthly-player', 'monthly-group', 'annual-consolidated'])
            ->whereIn('format', ['xlsx', 'pdf'])
            ->name('assist.export');

            Route::get('attendance-payment/{report}/{format}', [AttendancePaymentReportExportController::class, 'download'])
                ->whereIn('report', ['monthly-group', 'monthly-player'])
                ->whereIn('format', ['xlsx', 'pdf'])
                ->name('attendance-payment.export');
        });
    });

    Route::prefix('historic')->name('historic.')->group(function () {
        Route::middleware('school.permission:school.module.attendances')->group(function () {
            Route::get('assists', [HistoricController::class, 'assists'])->name('assists');
            Route::get('assists/{training_group_id}/{year}/{month?}', [HistoricController::class, 'assistsGroup'])->name('assists.group');
        });

        Route::middleware('school.permission:school.module.payments')->group(function () {
            Route::get('payments', [HistoricController::class, 'payments'])->name('payments');
            Route::get('payments/{training_group_id}/{year}/{month?}', [HistoricController::class, 'paymentsGroup'])->name('payments.group');
        });
    });

    // Las vistas SPA equivalentes viven en resources/js/router/index.js y usan metadata desde routes/api.php.
    // El envio por correo ahora tambien vive en POST /api/v2/reports/payments.
    Route::middleware('school.permission:school.module.reports')->prefix('reports')->name('reports.')->group(function () {
        Route::get('assists', [ReportAssistsController::class, 'index'])->name('assists');
        Route::get('payments', [ReportPaymentController::class, 'index'])->name('payments');
        // La vista SPA equivalente vive en resources/js/router/index.js y consume:
        // GET /api/v2/reports/attendance-payment,
        // GET /api/v2/reports/attendance-payment/monthly-by-group y
        // GET /api/v2/reports/attendance-payment/monthly-by-player.
        Route::get('attendance-payment', [ReportAttendancePaymentController::class, 'index'])->name('attendance-payment');
        Route::post('payments', [ReportPaymentController::class, 'report'])->name('payments.report');
    });

    Route::prefix('autocomplete')->group(function () {
        Route::get('autocomplete', [MasterController::class, 'autoComplete'])->name('autocomplete.fields');
        Route::get('identification_document_exists', [MasterController::class, 'existDocument'])->name('autocomplete.document_exists');
        Route::get('code_unique_verify', [MasterController::class, 'codeUniqueVerify'])->name('autocomplete.verify_code');
        Route::get('list_code_unique', [MasterController::class, 'listUniqueCode'])->name('autocomplete.list_code_unique');
        Route::get('list_code_unique_inscription', [MasterController::class, 'listUniqueCodeWithInscription'])->name('autocomplete.list_code_unique_inscription');
        Route::get('search_unique_code', [MasterController::class, 'searchUniqueCode'])->name('autocomplete.search_unique_code');
        Route::get('competition_groups', [MasterController::class, 'competitionGroupsByTournament'])->name('autocomplete.competition_groups');

        Route::get('tournaments', [MasterController::class, 'tournamentsBySchool'])->name('autocomplete.tournaments');
    });

    // La SPA equivalente vive en resources/js/router/index.js y consume sus datos desde routes/api.php.
    Route::middleware('school.permission:school.module.billing')->group(function () {
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/create/{inscription}', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'addPayment'])->name('invoices.addPayment');
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::get('items/invoices', [ItemInvoicesController::class, 'index'])->name('items.invoices.index');
    });

    Route::middleware([
        'school.permission:school.module.billing',
        'school.permission:school.feature.system_notify',
    ])->group(function(){
        // Las vistas SPA equivalentes viven en resources/js/router/index.js.
        // El header Vue consume el resumen desde GET /api/v2/notifications/header-summary.
        // PaymentRequests.vue y UniformRequests.vue consumen ahora:
        // GET /api/v2/notifications/payment-requests,
        // GET /api/v2/notifications/uniform-requests y
        // PUT /api/v2/notifications/invoice/{invoice}/payment-request/{paymentRequest}.
        Route::put('invoice/{invoice}/payment-request/{paymentRequest}', [InvoiceController::class, 'update']);
        Route::get('payment-request/invoices', [PaymentRequestController::class, 'index'])->name('payment-request.index');
        Route::get('uniform-request/invoices', [UniformRequestsController::class, 'index'])->name('uniform-request.index');
    });

    Route::middleware('school.permission:school.feature.system_notify')->group(function () {
        // TopicNotifications.vue consume ahora /api/v2/notifications/topics y /api/v2/notifications/topics/options.
        Route::get('notifications/options', [TopicNotificationsController::class, 'options'])->name('notification.options');
        Route::get('notifications', [TopicNotificationsController::class, 'index'])->name('notification.index');
        Route::post('notifications', [TopicNotificationsController::class, 'store'])->name('notification.store');
    });

    // La SPA equivalente vive en resources/js/router/index.js y consume sus datos desde routes/api.php.
    Route::middleware('school.permission:school.module.evaluations')->prefix('player-evaluations')->name('player-evaluations.')->group(function () {
        Route::get('/comparison/pdf', [PlayerEvaluationComparisonController::class, 'pdf'])->name('comparison.pdf');
        // La vista SPA la resuelve Vue desde el catch-all final.
        // Route::get('/comparison', [AppController::class, 'index'])->name('comparison');

        // Route::get('/', [AppController::class, 'index'])->name('index');
        // Route::get('/create', [AppController::class, 'index'])->name('create');
        Route::post('/', [PlayerEvaluationController::class, 'store'])->name('store');

        // Route::get('/{playerEvaluation}/edit', [AppController::class, 'index'])->name('edit');
        Route::put('/{playerEvaluation}', [PlayerEvaluationController::class, 'update'])->name('update');
        Route::delete('/{playerEvaluation}', [PlayerEvaluationController::class, 'destroy'])->name('destroy');

        Route::get('/{playerEvaluation}/pdf', [PlayerEvaluationController::class, 'pdf'])->name('pdf');
        // Route::get('/{playerEvaluation}', [AppController::class, 'index'])->name('show');
    });

    Route::prefix('administracion')->middleware(['role:super-admin'])->group(function () {
        // Las vistas SPA de plantillas de evaluacion ya viven en el router de Vue.
        // Sus datos y escrituras se atienden desde routes/api.php.
        Route::get('plantillas-evaluacion', [AppController::class, 'index']);
        Route::get('plantillas-evaluacion/crear', [AppController::class, 'index']);
        Route::get('plantillas-evaluacion/{any}', [AppController::class, 'index'])->where('any', '.*');
    });

    // Route::prefix('')->group(function () {
    //     Route::get(
    //         'evaluations/inscriptions/{inscription}/compare',
    //         [PlayerEvaluationInsightsController::class, 'compare']
    //     )->name('evaluations.compare');

    //     Route::get(
    //         'evaluations/{evaluation}/inscriptions/{inscription}/guardian-report',
    //         [PlayerEvaluationInsightsController::class, 'guardianReportPdf']
    //     )->name('evaluations.report');

    //     Route::resource('evaluations.inscriptions', PlayerEvaluationController::class)
    //     ->parameters([
    //         'evaluations' => 'evaluation',
    //     ]);
    // });
});

Route::middleware(['auth', 'verified_school'])->prefix('v1')->group(function () {
    // Compatibilidad legacy: el flujo Vue equivalente consume GET /api/v2/tournament-payouts.
    Route::get('tournamentpayout', [TournamentPayoutsController::class, 'searchRaw']);

    Route::prefix('groups')->group(function () {
        Route::get('training', [TrainingGroupController::class, 'groupList']);
    });

    Route::get('payments', [PaymentController::class, 'searchRaw']);

    Route::get("training_group/classdays", [TrainingGroupController::class, 'getClassDays'])->name('group_classdays');

    Route::prefix('admin')->middleware(['role:super-admin|school'])->group(function (){
        Route::get('school', [SchoolsController::class, 'index']);
        Route::put('school/{school}', [SchoolsController::class, 'update']);
    });
});


Route::get('/{any}', [AppController::class, 'index'])->where('any', '.*');
