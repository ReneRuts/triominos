<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DestroyScoreEventRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
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
}