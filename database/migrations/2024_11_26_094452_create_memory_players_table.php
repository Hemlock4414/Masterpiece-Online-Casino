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
        Schema::create('memory_players', function (Blueprint $table) {
            $table->id('player_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Verknüpfung mit der Users-Tabelle
            $table->timestamps();   // Erstellt created_at und updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memory_players');
    }
};
