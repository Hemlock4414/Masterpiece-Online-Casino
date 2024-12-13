<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

// Main Route für Vue.js SPA
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
