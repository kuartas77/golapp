<?php

namespace App\Http\Controllers\Portal;

use App\Models\School;
use App\Http\Controllers\Controller;

class SchoolsController extends Controller
{
    public function index()
    {
        return view('portal.schools.index');
    }

    public function show($slug)
    {
        $school = School::query()->where('is_enable', true)->firstWhere('slug', $slug);
        if(!$school) {
            return response('La escuela está deshabilitada', 404);
        }
        return view('portal.schools.show', compact('school'));
    }
}
