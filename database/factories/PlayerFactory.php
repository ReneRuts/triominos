<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition(): array
    {
        return [
            'tournament_id' => null,
            'game_id' => null, // Typically overridden in tests or set via state
            'user_id' => User::factory(),
            'guest_name' => null,
            'position' => $this->faker->numberBetween(1, 6),
            'warnings_used' => 0,
            'final_points' => null,
            'is_winner' => false,
        ];
    }

    /**
     * Indicate that the player is a guest without a user account.
     */
    public function guest(?string $name = null): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => null,
            'guest_name' => $name ?? $this->faker->firstName(),
        ]);
    }

    /**
     * Associate the player with a standalone game.
     */
    public function forGame(?Game $game = null): static
    {
        return $this->state(fn(array $attributes) => [
            'game_id' => $game?->id ?? Game::factory(),
            'tournament_id' => null,
        ]);
    }

    /**
     * Associate the player with a tournament.
     */
    public function forTournament(?Tournament $tournament = null): static
    {
        return $this->state(fn(array $attributes) => [
            'tournament_id' => $tournament?->id ?? Tournament::factory(),
            'game_id' => null,
        ]);
    }
}
