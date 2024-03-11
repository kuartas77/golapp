<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function logout(Request $request)
    {
        $school_id = isAdmin() ? 0 : getSchool(auth()->user())->id;
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        Cache::forget("BIRTHDAYS_{$school_id}");
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        Cache::forget("KEY_USERS_{$school_id}");
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        Cache::forget("KEY_DAYS_{$school_id}");
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        Cache::forget("KEY_TOURNAMENT_{$school_id}");
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        Cache::forget("KEY_TRAINING_GROUPS_{$school_id}");
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        Cache::forget("KEY_COMPETITION_GROUPS_{$school_id}");
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        Cache::forget("KEY_MIN_YEAR_{$school_id}");
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        Cache::forget("KEY_ASSIST_{$school_id}");

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect('/');
    }

    /**
     * The user has been authenticated.
     *
     * @param Request $request
     * @param mixed $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->hasAnyRole(['school', 'instructor'])) {
            /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
            Cache::remember(School::KEY_SCHOOL_CACHE . "_{$user->school_id}",
                now()->addMinutes(env('SESSION_LIFETIME', 120)),
                fn() => $user->school->load(['settingsValues']));
        }
    }
}
