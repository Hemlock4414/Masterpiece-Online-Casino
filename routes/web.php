<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

Route::get('/{any}', function () {
    return view('app'); // Change this to the view that includes your Vue app
})->where('any', '^(?!api).*$');

Broadcast::routes(['middleware' => ['web']]);