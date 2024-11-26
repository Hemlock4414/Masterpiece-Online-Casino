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
        Schema::create('games', function (Blueprint $table) {
            $table->id('game_id');
            $table->enum('status', ['waiting', 'in_progress', 'finished']);
            $table->foreignId('player_turn')->nullable()->constrained('players')->onDelete('set null');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('stopped_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memory_games');
    }
};
