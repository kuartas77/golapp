<?php

use App\Models\Player;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\Admin\UserController;
use App\Notifications\RegisterPlayerNotification;
use App\Http\Controllers\Assists\AssistController;
use App\Http\Controllers\Players\PlayerController;
use App\Http\Controllers\Competition\GameController;
use App\Http\Controllers\Payments\PaymentController;
use App\Http\Controllers\Schedule\SchedulesController;
use App\Http\Controllers\SchoolPages\SchoolsController;
use App\Http\Controllers\Players\PlayerExportController;
use App\Http\Controllers\Tournaments\TournamentController;
use App\Http\Controllers\Inscription\InscriptionController;
use App\Http\Controllers\Payments\TournamentPayoutsController;
use App\Http\Controllers\{HistoricController, IncidentController, DataTableController};
use App\Http\Controllers\{HomeController, ExportController, MasterController, ProfileController};
use App\Http\Controllers\Groups\{CompetitionGroupController, InscriptionCGroupController, InscriptionTGroupController, TrainingGroupController};

Auth::routes(['register' => false, 'verify' => false]);

Route::get('/', function () {
    return redirect(\route('login'));
});


Route::middleware(['auth', 'verified_school'])->group(function ($route) {

    $route->get('img/dynamic/{file}', [FileController::class, 'fileStorageServe'])->where(['file' => '.*'])->name('images');

    $route->get('/home', [HomeController::class, 'index'])->name('home');
    $route->get('/birthdays', [HomeController::class, 'birthDays'])->name('birthDays');

    $route->post('inscriptions/activate/{id}', [InscriptionController::class, 'activate'])->name('inscriptions.activate');

    $route->resource("inscriptions", InscriptionController::class)->except(['create','show']);
    $route->resource("payments", PaymentController::class)->only(['index','update']);
    $route->resource("assists", AssistController::class)->except(['create','edit', 'destroy']);
    $route->resource("matches", GameController::class)->except(['show']);
    $route->resource("players", PlayerController::class);
    $route->resource("tournamentpayout", TournamentPayoutsController::class)->only(['index', 'store', 'update']);

    $route->prefix('import')->group(function($route){
        $route->post('matches/{competition_group}', [ImportController::class, 'importMatchDetail'])->name('import.match');
        $route->post('players', [ImportController::class, 'importPlayers'])->name('import.players');
    });

    $route->resource("profiles", ProfileController::class)->except(['index','create','store','destroy']);

    $route->prefix('admin')->middleware(['role:super-admin|school'])->group(function ($route){

        $route->resources([
            'users' => UserController::class,
            'schedules' => SchedulesController::class,
            'incidents' => IncidentController::class,
            'tournaments' => TournamentController::class,
            'training_groups' => TrainingGroupController::class,
            'competition_groups' => CompetitionGroupController::class,
        ]);

        $route->get('school/{school}', [SchoolsController::class, 'index'])->name('school.index');
        $route->put('school/{school}', [SchoolsController::class, 'update'])->name('school.update');

        $route->get('filter_training_groups', [TrainingGroupController::class, 'filterGroupYear'])->name('training_groups.filter');
        $route->get('availability_training_groups/{training_group?}', [TrainingGroupController::class, 'availabilityGroup'])->name('training_groups.availability');

        $route->get('inscription_training',[InscriptionTGroupController::class, 'index'])->name('ins_training.index');
        $route->get('inscription_training/{training_group}', [InscriptionTGroupController::class, 'makeRows'])->name('ins_training.make');
        $route->post('inscription_training/{inscription_id}', [InscriptionTGroupController::class, 'assignGroup'])->name('ins_training.assign');

        $route->get('inscription_competition',[InscriptionCGroupController::class, 'index'])->name('ins_competition.index');
        $route->get('inscription_competition/{competition_group}', [InscriptionCGroupController::class, 'makeRows'])->name('ins_competition.make');
        $route->post('inscription_competition/{inscription}', [InscriptionCGroupController::class, 'assignGroup'])->name('ins_competition.change');

        $route->get('availability_competition_groups/{competition_groups?}', [CompetitionGroupController::class, 'availabilityGroup'])->name('competition_groups.availability');

        $route->post('users/activate/{id}', [UserController::class, 'activate'])->name('users.activate');

    });

    $route->prefix('datatables')->group(function ($route) {
        $route->get('enabled', [DataTableController::class, 'enabledInscriptions'])->name('inscriptions.enabled');
        $route->get('disabled', [DataTableController::class, 'disabledInscriptions'])->name('inscriptions.disabled');
        $route->get('training_groups_enabled', [DataTableController::class, 'enabledTrainingGroups'])->name('training_groups.enabled');
        $route->get('training_groups_retired', [DataTableController::class, 'disabledTrainingGroups'])->name('training_groups.retired');
        $route->get('competition_groups_enabled', [DataTableController::class, 'enabledCompetitionGroups'])->name('competition_groups.enabled');
        $route->get('competition_groups_retired', [DataTableController::class, 'disabledCompetitionGroups'])->name('competition_groups.retired');
        $route->get('schedules_enabled', [DataTableController::class, 'enabledSchedules'])->name('schedules.enabled');
        $route->get('players_enabled', [DataTableController::class, 'enabledPlayers'])->name('players.enabled');

    });

    $route->prefix('export')->name('export.')->group(function ($route) {
        $route->get('player/{player}/pdf', [PlayerExportController::class, 'exportPlayerPDF'])->name('player');
        $route->get('inscription/{player_id}/{inscription_id}/{year?}/{quarter?}', [PlayerExportController::class, 'exportInscription'])->name('inscription');
        $route->get('inscriptions/excel', [PlayerExportController::class, 'exportInscriptionsExcel'])->name('inscriptions');

        $route->get('assists/pdf/{training_group_id}/{year}/{month}/{deleted?}', [ExportController::class, 'exportAssistsPDF'])->name('pdf.assists');
        $route->get('matches/pdf/{match}', [ExportController::class, 'exportMatchPDF'])->name('pdf.match');
        $route->get('incidents/pdf/{slug_name}', [ExportController::class, 'exportIncidentsPDF'])->name('pdf.incidents');

        $route->get('payments/excel', [ExportController::class, 'exportPaymentsExcel'])->name('payments.excel');
        $route->get('payments/pdf', [ExportController::class, 'exportPaymentsPDF'])->name('payments.pdf');
        $route->get('assists/excel/{training_group_id}/{year}/{month}/{deleted?}', [ExportController::class, 'exportAssistsExcel'])->name('assists');
        $route->get('matches/create/{competition_group}/format', [ExportController::class, 'exportMatchDetail'])->name('match_detail');
        $route->get('tournament/payouts/excel', [ExportController::class, 'exportTournamentPayoutsExcel'])->name('tournaments.payouts.excel');
        $route->get('tournament/payouts/pdf', [ExportController::class, 'exportTournamentPayoutsPDF'])->name('tournaments.payouts.pdf');



    });

    $route->prefix('historic')->name('historic.')->group(function ($route) {
        $route->get('assists', [HistoricController::class, 'assists'])->name('assists');
        $route->get('assists/{training_group_id}/{year}/{month?}', [HistoricController::class, 'assistsGroup'])->name('assists.group');
        $route->get('payments', [HistoricController::class, 'payments'])->name('payments');
        $route->get('payments/{training_group_id}/{year}/{month?}', [HistoricController::class, 'paymentsGroup'])->name('payments.group');
    });

    $route->prefix('autocomplete')->group(function ($route) {
        $route->get('autocomplete', [MasterController::class, 'autoComplete'])->name('autocomplete.fields');
        $route->get('identification_document_exists', [MasterController::class, 'existDocument'])->name('autocomplete.document_exists');
        $route->get('code_unique_verify', [MasterController::class, 'codeUniqueVerify'])->name('autocomplete.verify_code');
        $route->get('list_code_unique', [MasterController::class, 'listUniqueCode'])->name('autocomplete.list_code_unique');
        $route->get('search_unique_code', [MasterController::class, 'searchUniqueCode'])->name('autocomplete.search_unique_code');
        $route->get('competition_groups', [MasterController::class, 'competitionGroupsByTournament'])->name('autocomplete.competition_groups');
    });
});
