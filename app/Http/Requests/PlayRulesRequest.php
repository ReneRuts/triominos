<?php

namespace App\Http\Requests;

use App\Enums\ScoringMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Shared by "new toernooi" and "new spel": who plays, and under which rules. */
abstract class PlayRulesRequest extends FormRequest
{
    abstract protected function specificRules(): array;

    protected function specificMessages(): array
    {
        return [];
    }

    public function rules(): array
    {
        return $this->specificRules() + [
            'mode' => ['required', Rule::enum(ScoringMode::class)],
            'is_public' => ['sometimes', 'boolean'],
            'time_limit_enabled' => ['sometimes', 'boolean'],
            'turn_time_limit' => ['nullable', 'required_if:time_limit_enabled,1', 'integer', 'min:10', 'max:900'],
            'warnings_allowed' => ['nullable', 'integer', 'min:0', 'max:2'],
            'timeout_penalty_points' => ['nullable', 'integer', 'min:0', 'max:100'],
            'timeout_draw_tile' => ['sometimes', 'boolean'],
            'players' => ['nullable', 'array', 'max:5'],
            'players.*' => ['string', 'uuid', 'distinct'],
            'guests' => ['nullable', 'array', 'max:5'],
            'guests.*' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function messages(): array
    {
        return $this->specificMessages() + [
            'players.max' => 'Je kan met maximaal 6 spelers spelen (jij + 5).',
            'turn_time_limit.required_if' => 'Vul in hoeveel seconden een beurt mag duren.',
        ];
    }

    /** Checks that need more than one field. */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $guests = $this->guestNames();
            if ($guests->map(fn($n) => mb_strtolower($n))->duplicates()->isNotEmpty()) {
                $validator->errors()->add('guests', 'Twee gasten hebben dezelfde naam.');

                return;
            }

            $count = 1 + count($this->friendIds()) + $guests->count();
            if ($count < 2) {
                $validator->errors()->add('players', 'Kies minstens één medespeler of voeg een gast toe.');
            } elseif ($count > 6) {
                $validator->errors()->add('players', 'Je kan met maximaal 6 spelers spelen.');
            } elseif (array_diff($this->friendIds(), $this->user()->friendIds())) {
                $validator->errors()->add('players', 'Je kan enkel vrienden toevoegen.');
            }
        }];
    }

    /** @return array<int,string> */
    public function friendIds(): array
    {
        return array_map('strval', (array) $this->input('players', []));
    }

    /** @return Collection<int,string> guests without account (empty fields dropped) */
    public function guestNames(): Collection
    {
        return collect((array) $this->input('guests', []))
            ->map(fn($n) => trim((string) $n))
            ->filter()
            ->values();
    }

    /** The rule columns shared by tournaments and games. */
    public function ruleAttributes(): array
    {
        $limit = $this->boolean('time_limit_enabled');

        return [
            'mode' => $this->validated('mode'),
            'is_public' => $this->boolean('is_public'),
            'turn_time_limit' => $limit ? (int) $this->validated('turn_time_limit') : null,
            'warnings_allowed' => $limit ? (int) ($this->validated('warnings_allowed') ?? 0) : 0,
            'timeout_penalty_points' => $limit ? (int) ($this->validated('timeout_penalty_points') ?? 0) : 0,
            'timeout_draw_tile' => $limit && $this->boolean('timeout_draw_tile'),
        ];
    }
}
