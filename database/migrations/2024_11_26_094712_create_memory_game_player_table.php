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
        Schema::create('memory_game_player', function (Blueprint $table) {
            $table->id('game_player_id');
            $table->foreignId('game_id')->constrained('memory_games', 'game_id')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('memory_players', 'player_id')->onDelete('cascade');
            $table->integer('player_score')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memory_game_player');
    }
};
