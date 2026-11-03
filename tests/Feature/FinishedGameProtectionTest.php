<?php

namespace Tests\Feature;

use App\Enums\GameEnding;
use App\Enums\Status;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinishedGameProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_store_score_event_when_game_is_finished(): void
    {
        // 1. Arrange: Create a user, a finished game, and a player
        $user = User::factory()->create();
        $game = Game::factory()->create([
            'status' => Status::Finished, // Adjust based on your actual Status enum/logic
        ]);
        $player = Player::factory()->create(['game_id' => $game->id]);

        // 2. Act: Attempt to submit a new score event
        // Note: Update the route name to match your actual route definitions
        $response = $this->actingAs($user)->postJson(route('events.store', $game), [
            'type' => 'tile',
            'player_id' => $player->id,
            'digit_a' => 1,
            'digit_b' => 2,
            'digit_c' => 3,
        ]);

        // 3. Assert: Verify the custom after() validation error is triggered
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'game' => 'Het spel is al afgesloten.',
            ]);
    }

    public function test_cannot_finish_a_game_that_is_already_finished(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        $game = Game::factory()->create([
            'status' => Status::Finished,
        ]);
        $player = Player::factory()->create(['game_id' => $game->id]);

        // 2. Act: Attempt to finish the game again
        $response = $this->actingAs($user)->postJson(route('games.finish', $game), [
            'ended_by' => GameEnding::Emptied->value,
            'winner_player_id' => $player->id,
            'remaining' => [
                $player->id => 0,
            ],
        ]);

        // 3. Assert: Verify the validation blocks it
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'game' => 'Het spel is al afgesloten.',
            ]);
    }
}