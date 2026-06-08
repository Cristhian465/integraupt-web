<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use Illuminate\Http\Request;

class EspacioController extends Controller
{
    public function index()
    {
        return response()->json(Espacio::all());
    }

    public function show($id)
    {
        $espacio = Espacio::find($id);
        if (!$espacio) {
            return response()->json(['message' => 'Espacio no encontrado'], 404);
        }
        return response()->json($espacio);
    }
}
