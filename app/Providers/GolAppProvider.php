<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Http\ViewComposers\AdminComposer;
use App\Http\ViewComposers\TemplatesComposer;
use App\Http\ViewComposers\Public\PortalComposer;
use App\Http\ViewComposers\Profile\ProfileComposer;
use App\Http\ViewComposers\Assists\AssistViewComposer;
use App\Http\ViewComposers\Incidents\IncidentComposer;
use App\Http\ViewComposers\Payments\PaymentsViewComposer;
use App\Http\ViewComposers\Competition\MatchesViewComposer;
use App\Http\ViewComposers\Assists\AssistHistoricViewComposer;
use App\Http\ViewComposers\TrainingGroup\TrainingGroupComposer;
use App\Http\ViewComposers\Inscription\InscriptionCreateComposer;
use App\Http\ViewComposers\Payments\PaymentsHistoricViewComposer;
use App\Http\ViewComposers\Payments\TournamentPaymentsViewComposer;
use App\Http\ViewComposers\TrainingSession\TrainingSessionComposer;

class GolAppProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loggerQueries();

        $this->macros();

        $this->viewComposers();
    }

    private function loggerQueries()
    {
        if (env('APP_ENV', null) == 'local') {
            DB::listen(function ($query) {
                foreach ($query->bindings as $key => $binding) {
                    if (is_bool($query->bindings[$key])) {
                        $query->bindings[$key] = $query->bindings[$key] ? 'true' : 'false';
                    }
                }
                logger()->info(Str::replaceArray('?', $query->bindings, $query->sql));
            });
        }
    }

    private function macros()
    {
        Collection::macro('setAppends', function ($attributes) {
            return $this->map(function ($item) use ($attributes) {
                return $item->setAppends($attributes);
            });
        });

        Collection::macro('obfuscate', function (array $attributes) {
            return $this->map(function ($item, $key) use ($attributes) {
                if (is_array($item) || is_object($item)) {
                    return collect($item)->obfuscate($attributes);
                }
                if (in_array($key, $attributes, true)) {
                    return Str::mask($item, '*', 3, 5);
                }
                return $item;
            });
        });
    }

    private function viewComposers()
    {
        View::composer([
            'inscription.index',
            'inscription.create',
            'inscription.edit',
            'player.index',
            'player.create',
            'player.edit'
        ], InscriptionCreateComposer::class);

        View::composer([
            'competition.match.*',
            'templates.competitions.row',
            'templates.competitions.row_edit'
        ], MatchesViewComposer::class);

        View::composer(['groups.competition.index', 'groups.training.index'], TrainingGroupComposer::class);

        View::composer(['training_sessions.*'], TrainingSessionComposer::class);

        View::composer(['payments.payment.index'], PaymentsViewComposer::class);

        View::composer(['profile.*'], ProfileComposer::class);

        View::composer(['assists.assist.index'], AssistViewComposer::class);

        View::composer(['assists.historic.index', 'assists.historic.show'], AssistHistoricViewComposer::class);

        View::composer(['payments.historic.index', 'payments.historic.show'], PaymentsHistoricViewComposer::class);

        View::composer(['incidents.index'], IncidentComposer::class);

        View::composer(['templates.*', 'modals.modal_attendance', 'assists.assist.index'], TemplatesComposer::class);

        View::composer(['layouts.menu', 'layouts.topbar'], AdminComposer::class);

        View::composer(['layouts.portal.*', 'portal.*', 'welcome'], PortalComposer::class);

        View::composer(['payments.tournaments.index'], TournamentPaymentsViewComposer::class);
    }
}
