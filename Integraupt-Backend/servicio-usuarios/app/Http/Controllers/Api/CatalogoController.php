<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoDocumento;
use App\Models\Rol;
use App\Models\Escuela;
use Illuminate\Http\JsonResponse;

class CatalogoController extends Controller
{
    public function tiposDocumento(): JsonResponse
    {
        return response()->json(TipoDocumento::all());
    }

    public function roles(): JsonResponse
    {
        return response()->json(Rol::all());
    }

    public function escuelas(): JsonResponse
    {
        return response()->json(Escuela::all());
    }
}
