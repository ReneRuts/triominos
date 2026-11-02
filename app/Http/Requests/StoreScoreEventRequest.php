<?php

namespace App\Http\Requests;

use App\Models\Player;
use App\Scoring\ScoreCalculator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScoreEventRequest extends FormRequest
{
    public function rules(): array
    {
        $digit = ['required_if:type,tile', 'nullable', 'integer', 'min:0', 'max:'.ScoreCalculator::MAX_DIGIT];

        return [
            'type' => ['required', 'in:tile,draw,pass'],
            'player_id' => ['required', 'integer', Rule::in($this->route('game')->players->pluck('id')->all())],
            'digit_a' => $digit,
            'digit_b' => $digit,
            'digit_c' => $digit,
            'hexagons' => ['nullable', 'integer', 'min:0', 'max:3'],
            'manual_points' => ['nullable', 'integer', 'min:-500', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        $range = 'Cijfers gaan van 0 tot '.ScoreCalculator::MAX_DIGIT.'.';
        $all = 'Vul alle drie de cijfers van de steen in.';

        return [
            'digit_a.required_if' => $all, 'digit_b.required_if' => $all, 'digit_c.required_if' => $all,
            'digit_a.max' => $range, 'digit_b.max' => $range, 'digit_c.max' => $range,
        ];
    }

    public function player(): Player
    {
        return $this->route('game')->players->firstWhere('id', (int) $this->validated('player_id'));
    }

    /** @return array{0:int,1:int,2:int} */
    public function digits(): array
    {
        return [(int) $this->validated('digit_a'), (int) $this->validated('digit_b'), (int) $this->validated('digit_c')];
    }
}
