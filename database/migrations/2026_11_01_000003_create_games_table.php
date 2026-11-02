<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A spel (was: ronde). Part of a toernooi, or played on its own (tournament_id = null).
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->foreignId('tournament_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 80)->nullable();            // standalone games; tournament games show "Spel {number}"
            $table->unsignedSmallInteger('number')->nullable(); // position inside the toernooi
            $table->string('status', 10)->default('active');   // active | finished

            // Rules. Always filled: a spel in a toernooi gets a copy of the toernooi rules when it starts.
            $table->string('mode', 10)->default('add');
            $table->boolean('is_public')->default(true);
            $table->unsignedSmallInteger('turn_time_limit')->nullable();
            $table->unsignedTinyInteger('warnings_allowed')->default(0);
            $table->smallInteger('timeout_penalty_points')->default(0);
            $table->boolean('timeout_draw_tile')->default(false);

            // Who started and who won. Plain ids on purpose: players is created after games
            // (players.game_id points back here), so there is no foreign key on these two.
            $table->unsignedBigInteger('starting_player_id')->nullable()->index();
            $table->unsignedBigInteger('winner_player_id')->nullable()->index();
            $table->string('ended_by', 10)->nullable();        // emptied | blocked
            $table->json('remaining')->nullable();             // remaining tile value per player id

            // Only standalone games have their own live link; tournament games use the toernooi link.
            $table->string('share_token', 40)->nullable()->unique();
            $table->string('join_code', 8)->nullable()->unique();

            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['tournament_id', 'number']);
            $table->index(['status', 'finished_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
