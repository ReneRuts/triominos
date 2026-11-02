<?php

namespace App\Http\Requests;

use App\Models\Game;
use App\Models\Tournament;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Start a spel: who has the highest stone (always), and the seating order (once).
 * Used on a toernooi (starts a new spel) and on a standalone spel that is not started yet.
 */
class StartGameRequest extends FormRequest
{
    public function rules(): array
    {
        $ids = $this->players()->pluck('id')->all();

        return [
            'starting_player_id' => ['required', 'integer', Rule::in($ids)],
            'order' => [$this->seatingRequired() ? 'required' : 'nullable', 'array', 'size:'.count($ids)],
            'order.*' => ['integer', 'distinct', Rule::in($ids)],
        ];
    }

    public function messages(): array
    {
        return [
            'starting_player_id.required' => 'Kies wie het hoogste blokje heeft en begint.',
            'order.required' => 'Zet de spelers in de volgorde waarin ze aan tafel zitten.',
            'order.size' => 'De zitvolgorde moet alle spelers bevatten.',
        ];
    }

    private function subject(): Tournament|Game
    {
        return $this->route('tournament') ?? $this->route('game');
    }

    private function players(): Collection
    {
        return $this->subject()->players;
    }

    /** Seating is asked once: before the first spel of a toernooi, or when a standalone spel starts. */
    private function seatingRequired(): bool
    {
        $subject = $this->subject();

        return $subject instanceof Game || $subject->games()->doesntExist();
    }
}
