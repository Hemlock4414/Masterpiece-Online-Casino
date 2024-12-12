<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memory_players', function (Blueprint $table) {
            $table->id('player_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('guest_id')->nullable()->unique(); // Für Gast-Identifizierung
            $table->string('name')->default('Gast');
            $table->enum('status', ['available', 'waiting', 'in_game', 'offline'])->default('available');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            // Indizes für bessere Performance
            $table->index('guest_id');
            $table->index('status');
            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memory_players');
    }
};