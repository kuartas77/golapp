<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Http\ViewComposers\AdminComposer;
use Illuminate\Database\Eloquent\Collection;
use App\Http\ViewComposers\TemplatesComposer;
use App\Http\ViewComposers\Profile\ProfileComposer;
use App\Http\ViewComposers\Assists\AssistViewComposer;
use App\Http\ViewComposers\Incidents\IncidentComposer;
use App\Http\ViewComposers\Payments\PaymentsViewComposer;
use App\Http\ViewComposers\Competition\MatchesViewComposer;
use App\Http\ViewComposers\Assists\AssistHistoricViewComposer;
use App\Http\ViewComposers\TrainingGroup\TrainingGroupComposer;
use App\Http\ViewComposers\Inscription\InscriptionCreateComposer;
use App\Http\ViewComposers\Payments\PaymentsHistoricViewComposer;

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
        if(env('APP_ENV', null) == 'local'){
            DB::listen(function($query){
                foreach($query->bindings as $key => $binding){
                    if(is_bool($query->bindings[$key])){
                        $query->bindings[$key] = $query->bindings[$key] ? 'true': 'false';
                    }
                }
                logger(Str::replaceArray('?', $query->bindings, $query->sql));
            });
        }
        
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
        ], InscriptionCreateComposer::class);

        View::composer([
            'competition.match.*',
            'templates.competitions.row',
            'templates.competitions.row_edit'
        ], MatchesViewComposer::class);

        View::composer([
            'groups.competition.index', 'groups.training.index'
        ], TrainingGroupComposer::class);

        View::composer(['payments.payment.index'], PaymentsViewComposer::class);

        View::composer(['profile.*'], ProfileComposer::class);

        View::composer(['assists.assist.index'], AssistViewComposer::class);

        View::composer([
            'assists.historic.index', 'assists.historic.show'
        ], AssistHistoricViewComposer::class);

        View::composer([
            'payments.historic.index', 'payments.historic.show'
        ], PaymentsHistoricViewComposer::class);

        View::composer(['incidents.index'], IncidentComposer::class);
        
        View::composer(['templates.*'], TemplatesComposer::class);

        View::composer(['*.*'], AdminComposer::class);
    }
}
