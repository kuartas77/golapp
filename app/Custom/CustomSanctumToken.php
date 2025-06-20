<?php

namespace App\Custom;

use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Route;

class CustomSanctumToken
{
    const ROUTE_REFRESH = 'api.refresh';

    public static function authenticateAccessTokensUsing()
    {
        (new self())->overrideSanctumConfigurationToSupportRefreshToken();
    }

    private function overrideSanctumConfigurationToSupportRefreshToken(): void
    {
        Sanctum::$accessTokenAuthenticationCallback = function ($accessToken, $isValid) {

            $tokenValid = collect([self::ROUTE_REFRESH])->contains(Route::currentRouteName()) ?
            $this->isRefreshTokenValid($accessToken) :
            $this->isAuthTokenValid($accessToken);

            return $isValid && $tokenValid;
        };
    }

    private function isAuthTokenValid(PersonalAccessToken $token): bool
    {
        return $token->can('auth');
    }

    private function isRefreshTokenValid(PersonalAccessToken $token): bool
    {
        return $token->can('refresh') && $token->cant('auth');
    }
}
