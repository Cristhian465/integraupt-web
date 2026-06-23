<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class ReservaValidationException extends Exception
{
    public function render(): Response
    {
        return response($this->getMessage(), 400)->header('Content-Type', 'text/plain');
    }
}
