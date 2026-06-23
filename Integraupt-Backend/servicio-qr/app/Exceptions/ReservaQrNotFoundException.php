<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ReservaQrNotFoundException extends Exception
{
    public function __construct(string $token)
    {
        parent::__construct("No se encontró un QR asociado al token: {$token}");
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'status' => 404,
            'error' => 'Not Found',
            'mensajes' => [$this->getMessage()],
        ], 404);
    }
}
