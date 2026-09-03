<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\SettingValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SettingValueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): RedirectResponse
    {
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
     * @return Response
     */
    public function show(SettingValue $settingValue): RedirectResponse
    {
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(SettingValue $settingValue): RedirectResponse
    {
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, SettingValue $settingValue): RedirectResponse
    {
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(SettingValue $settingValue): RedirectResponse
    {
        return back();
    }
}
