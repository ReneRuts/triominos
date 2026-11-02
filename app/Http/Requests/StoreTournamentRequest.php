<?php

namespace App\Http\Requests;

class StoreTournamentRequest extends PlayRulesRequest
{
    protected function specificRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'target_score' => ['required', 'integer', 'min:50', 'max:5000'],
        ];
    }

    protected function specificMessages(): array
    {
        return ['name.required' => 'Geef het toernooi een naam.'];
    }
}
