<?php

namespace App\Http\Controllers;

use App\Service\Settings\SettingsCatalogService;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function __construct(private SettingsCatalogService $catalogs) {}

    public function index(): JsonResponse
    {
        return response()->json($this->catalogs->general(getSchool(auth()->user()), (int) auth()->id(), isInstructor(), isAdmin()));
    }

    public function configGroups(): JsonResponse
    {
        return response()->json($this->catalogs->groups(getSchool(auth()->user())));
    }
}
