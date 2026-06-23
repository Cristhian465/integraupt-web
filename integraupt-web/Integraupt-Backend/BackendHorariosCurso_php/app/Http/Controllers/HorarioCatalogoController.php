<?php

namespace App\Http\Controllers;

use App\Services\CatalogoService;
use Illuminate\Http\JsonResponse;

class HorarioCatalogoController extends Controller
{
    public function __construct(protected CatalogoService $service) {}

    public function listarCursos(): JsonResponse
    {
        return response()->json($this->service->listarCursos());
    }

    public function listarDocentes(): JsonResponse
    {
        return response()->json($this->service->listarDocentes());
    }

    public function listarEspacios(): JsonResponse
    {
        return response()->json($this->service->listarEspacios());
    }

    public function listarBloques(): JsonResponse
    {
        return response()->json($this->service->listarBloques());
    }
}
