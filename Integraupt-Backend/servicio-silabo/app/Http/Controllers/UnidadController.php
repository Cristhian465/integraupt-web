<?php

namespace App\Http\Controllers;

use App\Models\Silabo;
use App\Models\SilaboUnidad;
use Illuminate\Http\Request;

class UnidadController extends Controller
{
    public function store(Request $request, $silaboId)
    {
        $request->validate([
            'Numero'     => 'required|integer|min:1',
            'Nombre'     => 'required|string|max:200',
            'HorasTotal' => 'nullable|integer|min:0',
        ]);

        $silabo = Silabo::findOrFail($silaboId);

        $unidad = SilaboUnidad::create([
            'SilaboId'   => $silabo->IdSilabo,
            'Numero'     => $request->Numero,
            'Nombre'     => $request->Nombre,
            'HorasTotal' => $request->HorasTotal ?? 0,
        ]);

        return response()->json($unidad->load('temas'), 201);
    }

    public function update(Request $request, $id)
    {
        $unidad = SilaboUnidad::findOrFail($id);
        $unidad->update($request->only(['Numero', 'Nombre', 'HorasTotal']));
        return response()->json($unidad->load('temas'));
    }

    public function destroy($id)
    {
        $unidad = SilaboUnidad::findOrFail($id);
        $unidad->delete();
        return response()->json(['message' => 'Unidad eliminada']);
    }
}
