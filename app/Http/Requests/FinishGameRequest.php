<?php

namespace App\Http\Requests;

use App\Enums\GameEnding;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FinishGameRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ended_by' => ['required', Rule::enum(GameEnding::class)],
            'winner_player_id' => ['required', 'integer', Rule::in($this->route('game')->players->pluck('id')->all())],
            'remaining' => ['required', 'array'],
            'remaining.*' => ['nullable', 'integer', 'min:0', 'max:5000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $game = $this->route('game');

            if ($game && ! $game->isActive()) {
                $validator->errors()->add('game', 'Het spel is al afgesloten.');
            }
        }];
    }

    public function messages(): array
    {
        return ['winner_player_id.required' => 'Kies wie het spel won.'];
    }
}