<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A seat at the table. Belongs to a toernooi (shared by all its spellen) OR to one standalone spel.
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete(); // null = guest without account
            $table->string('guest_name', 40)->nullable();
            $table->unsignedTinyInteger('position');          // seating order, clockwise
            $table->unsignedTinyInteger('warnings_used')->default(0);
            $table->integer('final_points')->nullable();      // result of the toernooi / standalone spel
            $table->boolean('is_winner')->default(false);
            $table->timestamps();

            $table->unique(['tournament_id', 'user_id']);
            $table->unique(['game_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
