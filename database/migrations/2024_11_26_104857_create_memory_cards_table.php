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
        Schema::create('memory_cards', function (Blueprint $table) {
            $table->id('card_id');
            $table->foreignId('game_id')
                ->constrained('memory_games', 'game_id')
                ->onDelete('cascade');
            $table->foreignId('matched_by')
                ->nullable()
                ->constrained('memory_players', 'player_id')
                ->onDelete('set null');
            $table->boolean('is_matched')->default(false);
            $table->string('card_image');
            $table->unsignedInteger('group_id')->index();
            $table->timestamps();
    
            // Index für häufige Abfragen
            $table->index(['game_id', 'is_matched']);
            $table->index(['game_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
