<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class InterfazEspaciosController extends Controller
{
    public function mostrarInterfaz(): JsonResponse
    {
        return response()->json(['mensaje' => 'Servicio disponible']);
    }
}
