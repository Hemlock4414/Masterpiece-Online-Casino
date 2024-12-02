<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
use Illuminate\Http\Request;

class LobbyController extends Controller
{
    public function getOnlinePlayers()
    {
        return MemoryPlayer::where('last_seen_at', '>', now()->subMinutes(5))
            ->where('user_id', '!=', auth()->id())
            ->with('user')
            ->get();
    }

    public function updateStatus(Request $request)
    {
        $player = MemoryPlayer::where('user_id', auth()->id())->first();
        $player->update([
            'status' => $request->status,
            'last_seen_at' => now()
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Lobby $lobby)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lobby $lobby)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lobby $lobby)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lobby $lobby)
    {
        //
    }
}
