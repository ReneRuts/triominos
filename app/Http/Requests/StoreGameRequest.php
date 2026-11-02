<?php

namespace App\Http\Requests;

/** A standalone spel: no toernooi, no target score. */
class StoreGameRequest extends PlayRulesRequest
{
    protected function specificRules(): array
    {
        return ['name' => ['nullable', 'string', 'max:80']];
    }
}
