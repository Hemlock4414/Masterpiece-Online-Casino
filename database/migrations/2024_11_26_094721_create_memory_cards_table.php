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
        Schema::create('cards', function (Blueprint $table) {
            $table->id('card_id');
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->boolean('is_matched')->default(false);
            $table->boolean('is_flipped')->default(false);
            $table->string('card_image');
            $table->integer('group_id'); // Die Gruppe für ein Paar
            $table->timestamps();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memory_cards');
    }
};
