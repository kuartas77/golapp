<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('welcome');
    }
}
