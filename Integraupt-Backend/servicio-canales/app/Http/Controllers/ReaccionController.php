<?php

namespace App\Http\Controllers;

use App\Models\CanalMensaje;
use App\Models\CanalMensajeReaccion;
use Illuminate\Http\Request;

class ReaccionController extends Controller
{
    private const EMOJIS_PERMITIDOS = ['❤️', '👍', '😂', '😮', '😢', '👏'];

    public function toggle(Request $request, $idCanal, $idMensaje)
    {
        $request->validate([
            'usuarioId' => 'required|integer|exists:usuario,IdUsuario',
            'emoji' => 'required|string',
        ]);

        $emoji = $request->input('emoji');

        if (! in_array($emoji, self::EMOJIS_PERMITIDOS, true)) {
            return response()->json(['message' => 'Emoji no permitido.'], 422);
        }

        $mensaje = CanalMensaje::where('IdMensaje', $idMensaje)
            ->where('IdCanal', $idCanal)
            ->first();

        if (! $mensaje) {
            return response()->json(['message' => 'Mensaje no encontrado.'], 404);
        }

        $idUsuario = (int) $request->input('usuarioId');

        // Cada usuario solo puede tener una reacción por mensaje: el mismo
        // emoji la cancela, un emoji distinto la reemplaza.
        $existente = CanalMensajeReaccion::where('IdMensaje', $idMensaje)
            ->where('IdUsuario', $idUsuario)
            ->first();

        if ($existente && $existente->Emoji === $emoji) {
            $existente->delete();
            $accion = 'eliminada';
        } elseif ($existente) {
            $existente->update(['Emoji' => $emoji]);
            $accion = 'reemplazada';
        } else {
            CanalMensajeReaccion::create([
                'IdMensaje' => (int) $idMensaje,
                'IdUsuario' => $idUsuario,
                'Emoji' => $emoji,
            ]);
            $accion = 'agregada';
        }

        $reacciones = $this->agruparReacciones((int) $idMensaje);

        return response()->json(['accion' => $accion, 'reacciones' => $reacciones]);
    }

    private function agruparReacciones(int $idMensaje): array
    {
        return CanalMensajeReaccion::with('usuario')
            ->where('IdMensaje', $idMensaje)
            ->get()
            ->groupBy('Emoji')
            ->map(fn ($grupo, $emoji) => [
                'emoji' => $emoji,
                'cantidad' => $grupo->count(),
                'usuarios' => $grupo->pluck('IdUsuario')->all(),
                'usuariosNombres' => $grupo->map(fn ($r) => $r->usuario?->NombreCompleto ?? 'Usuario')->all(),
            ])
            ->values()
            ->all();
    }
}
