<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\SettingValue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SettingValueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): \Illuminate\Http\RedirectResponse
    {
        return back();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): \Illuminate\Http\RedirectResponse
    {
        return back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param SettingValue $settingValue
     * @return Response
     */
    public function show(SettingValue $settingValue): \Illuminate\Http\RedirectResponse
    {
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param SettingValue $settingValue
     * @return Response
     */
    public function edit(SettingValue $settingValue): \Illuminate\Http\RedirectResponse
    {
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param SettingValue $settingValue
     * @return Response
     */
    public function update(Request $request, SettingValue $settingValue): \Illuminate\Http\RedirectResponse
    {
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SettingValue $settingValue
     * @return Response
     */
    public function destroy(SettingValue $settingValue): \Illuminate\Http\RedirectResponse
    {
        return back();
    }
}
