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
            $table->string('guest_id')->nullable()->unique(); // Für Session-basierte Gast-Identifikation
            $table->string('name');
            $table->timestamps();

            // Index für effiziente Gast-Lookups
            $table->index('guest_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memory_players');
    }
};