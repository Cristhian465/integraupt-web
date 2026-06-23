<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function index()
    {
        return response()->json(Reserva::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario' => 'required|integer',
            'espacio' => 'required|integer',
            'bloque' => 'required|integer',
            'curso' => 'required|integer',
            'fechaReserva' => 'required|date',
            'fechaSolicitud' => 'required|date',
            'CantidadEstudiantes' => 'required|integer|min:1',
            'DescripcionUso' => 'nullable|string',
            'Estado' => 'nullable|string'
        ]);

        $reserva = Reserva::create($validated);
        return response()->json($reserva, 201);
    }

    public function show($id)
    {
        $reserva = Reserva::find($id);
        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }
        return response()->json($reserva);
    }

    public function update(Request $request, $id)
    {
        $reserva = Reserva::find($id);
        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        $reserva->update($request->all());
        return response()->json($reserva);
    }

    public function destroy($id)
    {
        $reserva = Reserva::find($id);
        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        $reserva->delete();
        return response()->json(['message' => 'Reserva eliminada']);
    }
}
