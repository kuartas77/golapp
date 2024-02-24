<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function index(Request $request)
    {
        return view('welcome');
    }

    public function show(School $school)
    {
        return view('schoolPages.school', compact('school'));
    }
}
