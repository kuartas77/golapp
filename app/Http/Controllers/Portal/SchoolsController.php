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

    public function show(School $school)
    {
        return view('portal.schools.show', compact('school'));
    }
}
