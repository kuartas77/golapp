<?php

use App\Http\Controllers\API\Admin\InscriptionController;
use App\Http\Controllers\API\Admin\RegisterController;
use App\Http\Controllers\API\Admin\SchoolController;
use App\Http\Controllers\API\Admin\UsersController;
use App\Http\Controllers\API\AuthControllerSPA;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\Instructor\AssistsController;
use App\Http\Controllers\API\Instructor\GroupsController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Notifications\LoginPlayerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PlayersController;
use App\Http\Controllers\Assists\AssistController;
use App\Http\Controllers\BackOffice\SchoolController as BackOfficeShoolController;
use App\Http\Controllers\Competition\GameController;
use App\Http\Controllers\DataTableController;
use App\Http\Controllers\Groups\CompetitionGroupController;
use App\Http\Controllers\Groups\TrainingGroupController;
use App\Http\Controllers\Inscription\InscriptionController as WebInscriptions;
use App\Http\Controllers\Invoices\InvoiceController;
use App\Http\Controllers\Invoices\ItemInvoicesController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\Payments\PaymentController;
use App\Http\Controllers\Players\PlayerController;
use App\Http\Controllers\SchoolPages\SchoolsController;
use App\Http\Controllers\SettingsController;

Route::post('login', [LoginController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function(){

    Route::post('logout', [LoginController::class, 'logout']);
    Route::post('refresh-token', [LoginController::class, 'refresh'])->name('api.refresh');

    Route::get('check', [UserController::class, 'check']);
    Route::get('user', [UserController::class, 'user']);

    Route::get('img/dynamic/{file}', [FileController::class, 'fileStorageServe'])->where(['file' => '.*']);

    Route::prefix('instructor')->name('instructor.')->middleware(['auth:sanctum'])->group(function (){

        Route::apiResource('training_groups', GroupsController::class, ['only' => ['index', 'show']]);

        Route::get('statistics/groups', [GroupsController::class, 'statistics']);
        Route::get('attendances', [AssistsController::class, 'index']);
        Route::post('attendances/upsert', [AssistsController::class, 'upsert']);
    });

    Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum'])->name('v1.')->group(function (){

        Route::post('register', [RegisterController::class, 'register']);
        Route::apiResource('users', UsersController::class);
        Route::apiResource('inscriptions', InscriptionController::class);
        Route::apiResource('schools', SchoolController::class);
    });
});


Route::prefix('v2')->group(function(){

    Route::post('login', [AuthControllerSPA::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function(){

        Route::post('logout', [AuthControllerSPA::class, 'logout']);

        Route::prefix('settings')->group(function(){
            Route::get('general', [SettingsController::class, 'index']);
            Route::get('groups', [SettingsController::class, 'configGroups']);
        });

        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('kpis', [DashboardController::class, 'kpis']);

        Route::get('user', [UserController::class, 'user']);

        Route::prefix('admin')->middleware(['role:super-admin|school'])->group(function (){
            Route::get('school', [SchoolsController::class, 'index']);
            Route::put('school/{school}', [SchoolsController::class, 'update']);
            Route::apiResource('users', UsersController::class);
            Route::apiResource('training_groups', TrainingGroupController::class, ['only' => ['show', 'store', 'update']]);
            Route::apiResource('competition_groups', CompetitionGroupController::class, ['only' => ['show', 'store', 'update']]);

            Route::get('info_campus', [BackOfficeShoolController::class, 'infoCampus']);
            Route::post('change_school', [BackOfficeShoolController::class, 'choose']);

        });

        Route::apiResource('training_groups', GroupsController::class, ['only' => ['index', 'show']]);
        Route::get("training_group/classdays", [TrainingGroupController::class, 'getClassDays']);

        Route::apiResource("players", PlayerController::class, ['only' => ['edit','show', 'update']]);

        Route::apiResource("payments", PaymentController::class)->only(['index','update', 'show']);
        Route::apiResource("assists", AssistController::class)->except(['create','edit', 'destroy']);
        Route::resource("inscriptions", WebInscriptions::class)->except(['index','create','show']);

        Route::apiResource("matches", GameController::class)->except(['index','edit','create']);

        Route::prefix('datatables')->group(function () {
            Route::get('inscriptions_enabled', [DataTableController::class, 'enabledInscriptions']);
            Route::get('inscriptions_disabled', [DataTableController::class, 'disabledInscriptions']);
            Route::get('training_groups_enabled', [DataTableController::class, 'enabledTrainingGroups']);
            Route::get('training_groups_retired', [DataTableController::class, 'disabledTrainingGroups']);
            Route::get('competition_groups_enabled', [DataTableController::class, 'enabledCompetitionGroups']);
            Route::get('competition_groups_retired', [DataTableController::class, 'disabledCompetitionGroups']);
            Route::get('schedules_enabled', [DataTableController::class, 'enabledSchedules']);
            Route::get('players_enabled', [DataTableController::class, 'enabledPlayers']);
            Route::get('training_sessions_enabled', [DataTableController::class, 'trainingSessions']);
            Route::get('users_enabled', [DataTableController::class, 'enabledUsers']);
            Route::get('matches', [DataTableController::class, 'matches']);

            Route::middleware(['role:super-admin'])->group(function (){
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
            // Route::get('competition_groups', [MasterController::class, 'competitionGroupsByTournament'])->name('autocomplete.competition_groups');

            // Route::get('tournaments', [MasterController::class, 'tournamentsBySchool'])->name('autocomplete.tournaments');
        });

        Route::prefix('invoices')->group(function () {
            Route::get('', [InvoiceController::class, 'index']);
            Route::post('', [InvoiceController::class, 'store']);
            Route::get('create/{inscription}', [InvoiceController::class, 'create']);
            Route::get('{invoice}', [InvoiceController::class, 'show']);
            Route::delete('{invoice}', [InvoiceController::class, 'destroy']);
            Route::post('{invoice}/payment', [InvoiceController::class, 'addPayment']);
            Route::get('{invoice}/print', [InvoiceController::class, 'print']);
            Route::get('items/invoices', [ItemInvoicesController::class, 'index']);

        });







    });

});
Route::prefix('notify')->group(function() {

    Route::post('login', [LoginPlayerController::class, 'login']);


    Route::middleware(['auth:sanctum'])->group(function(){

        Route::prefix('notifications')->group(function() {
            Route::get('', function(){
                return response()->json(['hola']);
            });
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
        });
        Route::prefix('payments')->group(function() {
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
        });
        Route::prefix('requests')->group(function() {
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
            // Route::post('login', [LoginPlayerController::class, 'login']);
        });
            // TODO:rutas

    });
});
