<?php

use Illuminate\Support\Facades\Route;



Route::middleware(['auth', 'role:super-admin'])->group(function ($route) {

    // $route->middleware([])->group(function ($route){

    // });

});