<?php

namespace App\Providers;

use App\Channels\FirebaseTopicChannel;
use App\Custom\CustomRecaptchaV3;
use App\Custom\CustomSanctumToken;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Lunaweb\RecaptchaV3\RecaptchaV3;

class GolAppProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(FirebaseTopicChannel::class, function ($app) {
            return new FirebaseTopicChannel();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        CustomSanctumToken::authenticateAccessTokensUsing();

        $this->custombinding();

        $this->loggerQueries();

        $this->macros();

        $this->registerChannels();
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
            return collect($this)->map(function ($item) use ($attributes) {
                return $item->setAppends($attributes);
            });
        });

        Collection::macro('obfuscate', function (array $attributes, string $character = '*', int $index = 3, int $length = 5) {
            return collect($this)->map(function ($item, $key) use ($attributes, $index, $length, $character) {
                if (is_array($item) || is_object($item)) {
                    return collect($item)->obfuscate($attributes, $character, $index, $length);
                }
                if (in_array($key, $attributes, true)) {
                    return Str::mask($item, $character, $index, $length);
                }
                return $item;
            });
        });
    }

    private function custombinding()
    {
        $this->app->bind(RecaptchaV3::class, function ($app) {
            return new CustomRecaptchaV3(config(), new Client, request(), $app);
        });
    }

    private function registerChannels()
    {
        Notification::extend('firebase-topic', function ($app) {
            return new FirebaseTopicChannel();
        });
    }
}
