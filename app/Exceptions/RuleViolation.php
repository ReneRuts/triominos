<?php

namespace App\Exceptions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * A game rule was broken (e.g. "passing needs 3 draws first").
 * Actions throw this; Laravel turns it into a redirect back with the message, so controllers need no try/catch.
 */
class RuleViolation extends RuntimeException
{
    public function __construct(string $message, public readonly string $field = 'game')
    {
        parent::__construct($message);
    }

    public function render(Request $request): RedirectResponse
    {
        return back()->withInput()->withErrors([$this->field => $this->getMessage()]);
    }
}
