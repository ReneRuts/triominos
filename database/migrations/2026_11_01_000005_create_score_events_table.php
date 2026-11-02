<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            // tile | draw | pass | warning | timeout | game_end
            $table->string('type', 12);
            $table->unsignedTinyInteger('digit_a')->nullable();
            $table->unsignedTinyInteger('digit_b')->nullable();
            $table->unsignedTinyInteger('digit_c')->nullable();
            $table->integer('points');
            $table->boolean('is_manual')->default(false); // total typed by hand instead of calculated
            $table->json('details')->nullable();          // flags + breakdown
            $table->boolean('locked')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['game_id', 'id']);
            $table->index('player_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_events');
    }
};
