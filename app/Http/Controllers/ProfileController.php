<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\ProfileUpdate;
use App\Models\Profile;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class ProfileController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param Profile $profile
     * @return Application|Factory|RedirectResponse|View
     */
    public function show(Profile $profile)
    {
        if (auth()->id() == $profile->user_id || isAdmin() || isSchool()) {
            $profile->load('user');
            view()->share('profile', $profile);
            return view('profile.show');
        }
        Alert::error(config('app.name'), __('messages.denied'));
        return redirect()->to(route('home'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Profile $profile
     * @return Application|Factory|RedirectResponse|View
     */
    public function edit(Profile $profile)
    {
        if (auth()->id() == $profile->user_id || isAdmin() || isSchool()) {
            $profile->load('user');
            view()->share('profile', $profile);
            return view('profile.edit');
        }
        Alert::error(config('app.name'), __('messages.denied'));
        return redirect()->to(route('home'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProfileUpdate $request
     * @param Profile $profile
     * @return RedirectResponse
     */
    public
    function update(ProfileUpdate $request, Profile $profile): RedirectResponse
    {
        $profile->fill($request->validated())->save();
        Alert::success(config('app.name'), __('messages.profile_save'));
        return redirect()->to(route('profiles.show', [$profile->id]));
    }
}
