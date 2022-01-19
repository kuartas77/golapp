<?php

namespace App\Providers;


use Illuminate\Auth\Events\Registered;
use App\Models\{Game, School, Inscription, TrainingGroup};
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Observers\{MatchObserver, SchoolObserver, InscriptionObserver, TrainingGroupObserver};

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Game::observe(MatchObserver::class);
        School::observe(SchoolObserver::class);
        Inscription::observe(InscriptionObserver::class);
        TrainingGroup::observe(TrainingGroupObserver::class);
    }
}
