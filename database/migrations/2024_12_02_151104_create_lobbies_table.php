<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lobbies', function (Blueprint $table) {
            $table->id('lobby_id');
            $table->foreignId('challenger_id')->constrained('memory_players', 'player_id');
            $table->foreignId('challenged_id')->constrained('memory_players', 'player_id');
            $table->enum('status', ['pending', 'accepted', 'declined', 'in_game'])->default('pending');
            $table->string('game_type')->default('memory'); // für zukünftige Spiele
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lobbies');
    }
};
