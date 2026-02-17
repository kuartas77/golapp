<?php

use App\Http\Controllers\Admin\InvoiceCustomItemController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Groups\{CompetitionGroupController, InscriptionCGroupController, InscriptionTGroupController, TrainingGroupController};
use App\Http\Controllers\ImportController;
use App\Http\Controllers\Invoices\InvoiceController;
use App\Http\Controllers\Invoices\ItemInvoicesController;
use App\Http\Controllers\Notifications\PaymentRequestController;
use App\Http\Controllers\Notifications\TopicNotificationsController;
use App\Http\Controllers\Notifications\UniformRequestsController;
use App\Http\Controllers\Payments\TournamentPayoutsController;
use App\Http\Controllers\PlayerStatsController;
use App\Http\Controllers\Reports\ReportAssistsController;
use App\Http\Controllers\Reports\ReportPaymentController;
use App\Http\Controllers\TrainingSessions\TrainingSessionsController;
use App\Http\Controllers\{Admin\UserController, Assists\AssistController, Players\PlayerController};
use App\Http\Controllers\{Competition\GameController, Payments\PaymentController, Schedule\SchedulesController, SchoolPages\SchoolsController};
use App\Http\Controllers\{HistoricController, IncidentController, DataTableController};
use App\Http\Controllers\{HomeController, ExportController, MasterController, ProfileController};
use App\Http\Controllers\{Players\PlayerExportController, Tournaments\TournamentController, Inscription\InscriptionController};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['register' => false, 'verify' => false]);

Route::get('/', fn() => redirect('login'));

Route::get('img/dynamic/{file}', [FileController::class, 'fileStorageServe'])->where(['file' => '.*'])->name('images');

Route::middleware(['auth', 'verified_school'])->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/birthdays', [HomeController::class, 'birthDays'])->name('birthDays');

    Route::post('inscriptions/activate/{id}', [InscriptionController::class, 'activate'])->name('inscriptions.activate');

    Route::resource("inscriptions", InscriptionController::class)->except(['create','show']);
    Route::resource("payments", PaymentController::class)->only(['index','update', 'show']);
    Route::resource("assists", AssistController::class)->except(['create','edit', 'destroy']);
    Route::resource("matches", GameController::class)->except(['show']);
    Route::resource("players", PlayerController::class);
    Route::resource("tournamentpayout", TournamentPayoutsController::class)->only(['index', 'store', 'update']);

    Route::resource("training-sessions", TrainingSessionsController::class)->only(['index', 'create', 'store', 'update', 'show']);

    Route::get('statuses/payments', [PaymentController::class, 'paymentStatuses'])->name('payments.status');

    Route::prefix('import')->group(function(){
        Route::post('matches/{match}', [ImportController::class, 'importMatchDetail'])->name('import.match');
        Route::post('players', [ImportController::class, 'importPlayers'])->name('import.players');
    });

    Route::resource("profiles", ProfileController::class)->except(['index','create','store','destroy']);

    Route::prefix('admin')->middleware(['role:super-admin|school'])->group(function (){

        Route::resources([
            'users' => UserController::class,
            'schedules' => SchedulesController::class,
            'incidents' => IncidentController::class,
            'tournaments' => TournamentController::class,
            'training_groups' => TrainingGroupController::class,
            'competition_groups' => CompetitionGroupController::class,
        ]);

        Route::get('school/{school}', [SchoolsController::class, 'index'])->name('school.index');
        Route::put('school/{school}', [SchoolsController::class, 'update'])->name('school.update');

        Route::get('filter_training_groups', [TrainingGroupController::class, 'filterGroupYear'])->name('training_groups.filter');
        Route::get('availability_training_groups/{training_group?}', [TrainingGroupController::class, 'availabilityGroup'])->name('training_groups.availability');

        Route::get('inscription_training',[InscriptionTGroupController::class, 'index'])->name('ins_training.index');
        Route::get('inscription_training/{training_group}', [InscriptionTGroupController::class, 'makeRows'])->name('ins_training.make');
        Route::post('inscription_training/{inscription_id}', [InscriptionTGroupController::class, 'assignGroup'])->name('ins_training.assign');

        Route::get('inscription_competition',[InscriptionCGroupController::class, 'index'])->name('ins_competition.index');
        Route::get('inscription_competition/{competition_group}', [InscriptionCGroupController::class, 'makeRows'])->name('ins_competition.make');
        Route::post('inscription_competition/{inscription}', [InscriptionCGroupController::class, 'assignGroup'])->name('ins_competition.change');

        Route::get('availability_competition_groups/{competition_groups?}', [CompetitionGroupController::class, 'availabilityGroup'])->name('competition_groups.availability');

        Route::post('users/activate/{id}', [UserController::class, 'activate'])->name('users.activate');

        Route::resource("invoice-items-custom", InvoiceCustomItemController::class)->except(['create']);

    });

    Route::prefix('datatables')->group(function () {
        Route::get('enabled', [DataTableController::class, 'enabledInscriptions'])->name('inscriptions.enabled');
        Route::get('disabled', [DataTableController::class, 'disabledInscriptions'])->name('inscriptions.disabled');
        Route::get('training_groups_enabled', [DataTableController::class, 'enabledTrainingGroups'])->name('training_groups.enabled');
        Route::get('training_groups_retired', [DataTableController::class, 'disabledTrainingGroups'])->name('training_groups.retired');
        Route::get('competition_groups_enabled', [DataTableController::class, 'enabledCompetitionGroups'])->name('competition_groups.enabled');
        Route::get('competition_groups_retired', [DataTableController::class, 'disabledCompetitionGroups'])->name('competition_groups.retired');
        Route::get('schedules_enabled', [DataTableController::class, 'enabledSchedules'])->name('schedules.enabled');
        Route::get('players_enabled', [DataTableController::class, 'enabledPlayers'])->name('players.enabled');
        Route::get('training_sessions_enabled', [DataTableController::class, 'trainingSessions'])->name('training_sessions.enabled');
    });

    Route::prefix('export')->name('export.')->group(function () {
        Route::get('player/{player}/pdf', [PlayerExportController::class, 'exportPlayerPDF'])->name('player');
        Route::get('inscription/{player_id}/{inscription_id}/{year?}/{quarter?}', [PlayerExportController::class, 'exportInscription'])->name('inscription');
        Route::get('inscriptions/excel', [PlayerExportController::class, 'exportInscriptionsExcel'])->name('inscriptions');

        Route::get('assists/pdf/{training_group_id}/{year}/{month}/{deleted?}', [ExportController::class, 'exportAssistsPDF'])->name('pdf.assists');
        Route::get('matches/pdf/{match}', [ExportController::class, 'exportMatchPDF'])->name('pdf.match');
        Route::get('incidents/pdf/{slug_name}', [ExportController::class, 'exportIncidentsPDF'])->name('pdf.incidents');

        Route::get('payments/excel', [ExportController::class, 'exportPaymentsExcel'])->name('payments.excel');
        Route::get('payments/pdf', [ExportController::class, 'exportPaymentsPDF'])->name('payments.pdf');
        Route::get('assists/excel/{training_group_id}/{year}/{month}/{deleted?}', [ExportController::class, 'exportAssistsExcel'])->name('assists');
        Route::get('matches/create/{competition_group}/format', [ExportController::class, 'exportMatchDetail'])->name('match_detail');
        Route::get('tournament/payouts/excel', [ExportController::class, 'exportTournamentPayoutsExcel'])->name('tournaments.payouts.excel');
        Route::get('tournament/payouts/pdf', [ExportController::class, 'exportTournamentPayoutsPDF'])->name('tournaments.payouts.pdf');
        Route::get('training_sessions/pdf/{id}', [ExportController::class, 'exportTrainingSession'])->name('training_sessions.pdf');
        Route::get('items/invoices', [ExportController::class, 'exportPendingItemsInvoices'])->name('items.invoices');
    });

    Route::prefix('historic')->name('historic.')->group(function () {
        Route::get('assists', [HistoricController::class, 'assists'])->name('assists');
        Route::get('assists/{training_group_id}/{year}/{month?}', [HistoricController::class, 'assistsGroup'])->name('assists.group');
        Route::get('payments', [HistoricController::class, 'payments'])->name('payments');
        Route::get('payments/{training_group_id}/{year}/{month?}', [HistoricController::class, 'paymentsGroup'])->name('payments.group');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('assists', [ReportAssistsController::class, 'index'])->name('assists');
        Route::post('assists', [ReportAssistsController::class, 'report'])->name('assists.report');
        Route::get('payments', [ReportPaymentController::class, 'index'])->name('payments');
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

    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/create/{inscription}', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'addPayment'])->name('invoices.addPayment');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('items/invoices', [ItemInvoicesController::class, 'index'])->name('items.invoices.index');

    Route::get('/player-stats', [PlayerStatsController::class, 'index'])->name('player.stats');
    Route::get('/top-players', [PlayerStatsController::class, 'topPlayers'])->name('players.top');
    Route::get('/player/{id}/detail', [PlayerStatsController::class, 'playerDetail'])->name('player.detail');

    Route::middleware('check_notify_system')->group(function(){
        Route::get('payment-request/invoices', [PaymentRequestController::class, 'index'])->name('payment-request.index');
        Route::get('uniform-request/invoices', [UniformRequestsController::class, 'index'])->name('uniform-request.index');
        Route::get('notifications', [TopicNotificationsController::class, 'index'])->name('notification.index');
        Route::post('notifications', [TopicNotificationsController::class, 'store'])->name('notification.store');
    });
});

Route::middleware(['auth', 'verified_school'])->prefix('v1')->group(function () {
    Route::get('tournamentpayout', [TournamentPayoutsController::class, 'searchRaw']);

    Route::prefix('groups')->group(function () {
        Route::get('training', [TrainingGroupController::class, 'groupList']);
    });

    Route::get('payments', [PaymentController::class, 'searchRaw']);

    Route::get("training_group/classdays", [TrainingGroupController::class, 'getClassDays'])->name('group_classdays');
});
