<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lobbies', function (Blueprint $table) {
            $table->id('lobby_id');
            
            // Challenger
            $table->foreignId('challenger_id');
            $table->string('challenger_type'); 
            $table->string('challenger_name');
            $table->foreignId('challenger_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('challenger_status')->default('waiting');
            
            // Challenged
            $table->foreignId('challenged_id');
            $table->string('challenged_type');
            $table->string('challenged_name');
            $table->foreignId('challenged_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('challenged_status')->default('available');
            
            $table->enum('status', ['pending', 'accepted', 'declined', 'in_game', 'finished'])->default('pending');
            $table->string('game_type')->default('memory');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            // Indizes
            $table->index(['challenger_type', 'challenger_id']);
            $table->index(['challenged_type', 'challenged_id']);
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lobbies');
    }
};