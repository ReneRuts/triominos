<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A toernooi: a fixed group of players playing several spellen until somebody reaches the target.
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('status', 10)->default('active');   // active | finished
            $table->string('mode', 10)->default('add');        // add | multiply
            $table->unsignedSmallInteger('target_score')->default(400);
            $table->boolean('is_public')->default(true);

            // Time limit per turn (null = no limit). Copied onto every spel of the toernooi.
            $table->unsignedSmallInteger('turn_time_limit')->nullable(); // seconds
            $table->unsignedTinyInteger('warnings_allowed')->default(0);
            $table->smallInteger('timeout_penalty_points')->default(0);
            $table->boolean('timeout_draw_tile')->default(false);

            $table->timestamp('target_reached_at')->nullable();
            $table->string('share_token', 40)->unique();
            $table->string('join_code', 8)->unique();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'finished_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
