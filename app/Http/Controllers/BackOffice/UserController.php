<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): RedirectResponse
    {
        //  Auth::loginUsingId(6);
        //  dd(auth()->user());

        // $user = User::find(6);
        // dd($user->school);
        // Auth::loginUsingId(1);
        // auth()->user()->assignRole('administrador');

        // $users = User::whereDoesntHave('profile')->chunk(5, function($users){
        //     foreach ($users as $user) {
        //         $user->profile()->create();
        //     }
        // });
        return back();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): RedirectResponse
    {
        return back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request): RedirectResponse
    {
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return Response
     */
    public function show(User $user): RedirectResponse
    {
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return Response
     */
    public function edit(User $user): RedirectResponse
    {
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\User  $user
     * @return Response
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return Response
     */
    public function destroy(User $user): RedirectResponse
    {
        return back();
    }
}
