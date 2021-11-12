<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Collection;

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
    public function boot()
    {
        Collection::macro('setAppends', function ($attributes) {
            return $this->map(function ($item) use ($attributes) {
                return $item->setAppends($attributes);
            });
        });

        View::composer([
            'inscription.index',
            'inscription.create',
            'inscription.edit',
            'player.index',
            'player.create',
            'player.edit'
        ], 'App\Http\ViewComposers\Inscription\InscriptionCreateComposer');

        View::composer([
            'competition.match.*',
            'templates.competitions.row',
            'templates.competitions.row_edit'
        ], 'App\Http\ViewComposers\Competition\MatchesViewComposer');

        View::composer([
            'groups.competition.index', 'groups.training.index'
        ], 'App\Http\ViewComposers\TrainingGroup\TrainingGroupComposer');

        View::composer(['day.index'], 'App\Http\ViewComposers\DayComposer');

        View::composer(['payments.payment.index'], 'App\Http\ViewComposers\Payments\PaymentsViewComposer');

        View::composer(['profile.*'], 'App\Http\ViewComposers\Profile\ProfileComposer');

        View::composer(['assists.assist.index'], 'App\Http\ViewComposers\Assists\AssistViewComposer');

        View::composer([
            'assists.historic.index', 'assists.historic.show'
        ], 'App\Http\ViewComposers\Assists\HistoricViewComposer');

        View::composer([
            'payments.historic.index', 'payments.historic.show'
        ], 'App\Http\ViewComposers\Payments\HistoricViewComposer');

        View::composer(['incidents.index'], 'App\Http\ViewComposers\Incidents\IncidentComposer');

        View::composer(['templates.*'], 'App\Http\ViewComposers\TemplatesComposer');
    }
}
