<?php

namespace App\Http\Controllers;

use App\Models\SilaboUnidad;
use App\Models\SilaboTema;
use Illuminate\Http\Request;

class TemaController extends Controller
{
    public function store(Request $request, $unidadId)
    {
        $request->validate([
            'Semana'                   => 'required|integer|min:1',
            'ContenidoConceptual'      => 'nullable|string',
            'ContenidoProcedimental'   => 'nullable|string',
            'Orden'                    => 'nullable|integer|min:0',
        ]);

        $unidad = SilaboUnidad::findOrFail($unidadId);

        $tema = SilaboTema::create([
            'UnidadId'                 => $unidad->IdUnidad,
            'Semana'                   => $request->Semana,
            'ContenidoConceptual'      => $request->ContenidoConceptual,
            'ContenidoProcedimental'   => $request->ContenidoProcedimental,
            'Orden'                    => $request->Orden ?? 0,
        ]);

        return response()->json($tema, 201);
    }

    public function update(Request $request, $id)
    {
        $tema = SilaboTema::findOrFail($id);
        $tema->update($request->only([
            'Semana', 'ContenidoConceptual', 'ContenidoProcedimental', 'Orden',
        ]));
        return response()->json($tema);
    }

    public function destroy($id)
    {
        $tema = SilaboTema::findOrFail($id);
        $tema->delete();
        return response()->json(['message' => 'Tema eliminado']);
    }
}
