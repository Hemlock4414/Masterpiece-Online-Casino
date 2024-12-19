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
        Schema::create('memory_games', function (Blueprint $table) {
            $table->id('game_id');
            $table->enum('status', ['waiting', 'in_progress', 'finished', 'aborted']);
            $table->string('theme')->default('emojis');
            $table->foreignId('player_turn')
                ->nullable()
                ->constrained('memory_players', 'player_id')
                ->onDelete('set null');
            $table->timestamp('stopped_at')->nullable();
            $table->timestamps();
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