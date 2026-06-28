<?php

namespace App\Http\Controllers;

use App\Models\AvanceTema;
use App\Models\SilaboTema;
use Illuminate\Http\Request;

class AvanceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'SilaboTemaId'   => 'required|integer|exists:silabo_tema,IdTema',
            'DocenteId'      => 'required|integer',
            'HorarioCursoId' => 'nullable|integer',
            'FechaClase'     => 'required|date',
            'Comentario'     => 'nullable|string',
        ]);

        $existente = AvanceTema::where('SilaboTemaId', $request->SilaboTemaId)
            ->where('DocenteId', $request->DocenteId)
            ->whereIn('Estado', ['pendiente', 'aprobado'])
            ->first();

        if ($existente) {
            return response()->json([
                'message' => 'Ya existe un avance registrado para este tema.',
                'avance'  => $existente,
            ], 409);
        }

        $avance = AvanceTema::create([
            'SilaboTemaId'   => $request->SilaboTemaId,
            'DocenteId'      => $request->DocenteId,
            'HorarioCursoId' => $request->HorarioCursoId,
            'FechaClase'     => $request->FechaClase,
            'Comentario'     => $request->Comentario,
            'Estado'         => 'pendiente',
        ]);

        return response()->json($avance->load('tema'), 201);
    }

    public function index(Request $request)
    {
        $query = AvanceTema::with('tema.unidad.silabo');

        if ($request->has('docenteId')) {
            $query->where('DocenteId', $request->docenteId);
        }
        if ($request->has('horarioCursoId')) {
            $query->where('HorarioCursoId', $request->horarioCursoId);
        }
        if ($request->has('estado')) {
            $query->where('Estado', $request->estado);
        }

        return response()->json($query->orderByDesc('FechaClase')->get());
    }

    public function byDocente($docenteId)
    {
        $avances = AvanceTema::with('tema.unidad.silabo')
            ->where('DocenteId', $docenteId)
            ->orderByDesc('FechaClase')
            ->get();

        return response()->json($avances);
    }

    public function updateEstado(Request $request, $id)
    {
        $request->validate([
            'Estado'                  => 'required|in:aprobado,rechazado',
            'ObservacionCoordinador'  => 'nullable|string',
        ]);

        $avance = AvanceTema::findOrFail($id);

        if ($avance->Estado !== 'pendiente') {
            return response()->json(['message' => 'Solo se pueden revisar avances en estado pendiente.'], 400);
        }

        $avance->update([
            'Estado'                 => $request->Estado,
            'ObservacionCoordinador' => $request->ObservacionCoordinador,
        ]);

        return response()->json($avance->load('tema'));
    }

    public function destroy($id)
    {
        $avance = AvanceTema::findOrFail($id);
        $avance->delete();
        return response()->json(['message' => 'Avance eliminado']);
    }
}
