<?php

namespace App\Http\Controllers;

use App\Models\Canal;
use App\Models\CanalTema;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TemaController extends Controller
{
    public function index($idCanal)
    {
        try {
            Canal::findOrFail($idCanal);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Canal no encontrado.'], 404);
        }

        $temas = CanalTema::where('IdCanal', $idCanal)
            ->orderBy('Orden')
            ->orderBy('FechaCreacion')
            ->get();

        return response()->json([
            'data' => $temas->map(fn (CanalTema $t) => $this->mapear($t))->all(),
        ]);
    }

    public function store(Request $request, $idCanal)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer|min:0',
        ]);

        try {
            Canal::findOrFail($idCanal);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Canal no encontrado.'], 404);
        }

        $tema = CanalTema::create([
            'IdCanal' => (int) $idCanal,
            'Nombre' => $request->input('nombre'),
            'Descripcion' => $request->input('descripcion'),
            'Orden' => $request->input('orden', 0),
        ]);

        return response()->json($this->mapear($tema), 201);
    }

    public function update(Request $request, $idCanal, $idTema)
    {
        $request->validate([
            'nombre' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string',
            'orden' => 'nullable|integer|min:0',
        ]);

        $tema = CanalTema::where('IdCanal', $idCanal)->where('IdTema', $idTema)->first();

        if (! $tema) {
            return response()->json(['message' => 'Tema no encontrado.'], 404);
        }

        $tema->update([
            'Nombre' => $request->input('nombre', $tema->Nombre),
            'Descripcion' => $request->input('descripcion', $tema->Descripcion),
            'Orden' => $request->input('orden', $tema->Orden),
        ]);

        return response()->json($this->mapear($tema->refresh()));
    }

    public function destroy($idCanal, $idTema)
    {
        $tema = CanalTema::where('IdCanal', $idCanal)->where('IdTema', $idTema)->first();

        if (! $tema) {
            return response()->json(['message' => 'Tema no encontrado.'], 404);
        }

        $tema->delete();

        return response()->json(['message' => 'Tema eliminado.']);
    }

    private function mapear(CanalTema $tema): array
    {
        return [
            'id' => $tema->IdTema,
            'canalId' => $tema->IdCanal,
            'nombre' => $tema->Nombre,
            'descripcion' => $tema->Descripcion,
            'orden' => $tema->Orden,
            'fechaCreacion' => optional($tema->FechaCreacion)->toIso8601String(),
        ];
    }
}
