<?php

namespace App\Http\Controllers;

use App\Models\CanalMensaje;
use App\Models\CanalMensajeReaccion;
use App\Models\Usuario;
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

        $existente = CanalMensajeReaccion::where('IdMensaje', $idMensaje)
            ->where('IdUsuario', $idUsuario)
            ->where('Emoji', $emoji)
            ->first();

        if ($existente) {
            $existente->delete();
            $accion = 'eliminada';
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
        return CanalMensajeReaccion::where('IdMensaje', $idMensaje)
            ->get()
            ->groupBy('Emoji')
            ->map(fn ($grupo, $emoji) => [
                'emoji' => $emoji,
                'cantidad' => $grupo->count(),
                'usuarios' => $grupo->pluck('IdUsuario')->all(),
            ])
            ->values()
            ->all();
    }
}
