<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class ReservaNotFoundException extends Exception
{
    public function render(): Response
    {
        return response($this->getMessage(), 404)->header('Content-Type', 'text/plain');
    }
}
