<?php

namespace App\Http\Controllers\SchoolPages;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolsController extends Controller
{
    public function index(Request $request)
    {
        
    }

    public function show(School $school)
    {
        dd($school);
    }
}
