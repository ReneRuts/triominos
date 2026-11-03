<?php

namespace Database\Factories;

use App\Enums\GameEnding;
use App\Enums\ScoringMode;
use App\Enums\Status;
use App\Models\Game;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        return [
            'public_id' => Str::random(26), // Handled by HasPublicId trait in reality, but good for testing
            'tournament_id' => null, // Standalone by default
            'creator_id' => User::factory(),
            'name' => $this->faker->optional()->catchPhrase(),
            'number' => null,
            'status' => Status::Active,
            'mode' => ScoringMode::Add, // Adjust enum case based on your app
            'is_public' => false,
            'turn_time_limit' => null,
            'warnings_allowed' => 0,
            'timeout_penalty_points' => 0,
            'timeout_draw_tile' => false,
            'starting_player_id' => null, // Usually set later when players join
            'winner_player_id' => null,
            'ended_by' => null,
            'remaining' => null,
            'share_token' => Str::random(40), // Handled by HasShareLink trait in reality
            'join_code' => Str::random(8),    // Handled by HasShareLink trait in reality
            'finished_at' => null,
        ];
    }

    /**
     * Indicate that the game belongs to a tournament.
     */
    public function inTournament(?Tournament $tournament = null, int $number = 1): static
    {
        return $this->state(fn(array $attributes) => [
            'tournament_id' => $tournament?->id ?? Tournament::factory(),
            'name' => null, // Tournaments usually use "Spel {number}"
            'number' => $number,
            'share_token' => null, // Tournament games don't have their own share links
            'join_code' => null,
        ]);
    }

    /**
     * Indicate that the game has finished.
     */
    public function finished(?int $winnerId = null, GameEnding $endedBy = GameEnding::Emptied): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Status::Finished,
            'winner_player_id' => $winnerId,
            'ended_by' => $endedBy,
            'remaining' => $winnerId ? [$winnerId => 0] : [],
            'finished_at' => now(),
        ]);
    }
}