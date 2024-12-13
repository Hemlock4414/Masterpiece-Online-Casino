<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function authenticate(Request $request)
    {
        return auth()->check() || session()->has('memoryGuestId')
            ? response()->json(['message' => 'Authenticated'])
            : response()->json(['message' => 'Unauthorized'], 403);
    }
}