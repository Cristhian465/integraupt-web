<?php

namespace App\Http\Controllers;

use App\Models\CanalMensaje;
use App\Services\MensajeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MensajeController extends Controller
{
    public function __construct(private MensajeService $mensajeService)
    {
    }

    public function index(Request $request, $idCanal)
    {
        $temaId = $request->query('temaId') !== null ? (int) $request->query('temaId') : null;

        try {
            $mensajes = $this->mensajeService->listar((int) $idCanal, $temaId);

            return response()->json([
                'data' => $mensajes->map(fn (CanalMensaje $m) => $this->mapear($m))->all(),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Canal no encontrado.'], 404);
        }
    }

    public function store(Request $request, $idCanal)
    {
        $request->validate([
            'usuarioId' => 'required|integer|exists:usuario,IdUsuario',
            'contenido' => 'nullable|string',
            'imagenUrl' => 'nullable|string',
            'temaId' => 'nullable|integer',
            'idMensajeRespuesta' => 'nullable|integer',
        ]);

        if (empty($request->input('contenido')) && empty($request->input('imagenUrl'))) {
            return response()->json(['message' => 'El mensaje debe tener contenido o imagen.'], 422);
        }

        try {
            $mensaje = $this->mensajeService->enviar([
                'IdCanal' => $idCanal,
                'IdUsuario' => $request->input('usuarioId'),
                'Contenido' => $request->input('contenido', ''),
                'ImagenUrl' => $request->input('imagenUrl'),
                'IdTema' => $request->input('temaId'),
                'IdMensajeRespuesta' => $request->input('idMensajeRespuesta'),
            ]);

            return response()->json($this->mapear($mensaje), 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Request $request, $idCanal, $idMensaje)
    {
        $request->validate([
            'usuarioId' => 'required|integer',
        ]);

        try {
            $this->mensajeService->eliminar(
                (int) $idMensaje,
                (int) $request->input('usuarioId'),
                $request->boolean('esAdmin')
            );

            return response()->json(['message' => 'Mensaje eliminado.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function mapear(CanalMensaje $mensaje): array
    {
        $respuestaA = null;
        if ($mensaje->IdMensajeRespuesta && $mensaje->respuestaA) {
            $r = $mensaje->respuestaA;
            $respuestaA = [
                'id' => $r->IdMensaje,
                'usuarioNombre' => $r->usuario?->NombreCompleto,
                'contenido' => $r->Contenido,
                'imagenUrl' => $r->ImagenUrl,
            ];
        }

        $reacciones = $mensaje->reacciones
            ->groupBy('Emoji')
            ->map(fn ($grupo, $emoji) => [
                'emoji' => $emoji,
                'cantidad' => $grupo->count(),
                'usuarios' => $grupo->pluck('IdUsuario')->all(),
            ])
            ->values()
            ->all();

        return [
            'id' => $mensaje->IdMensaje,
            'canalId' => $mensaje->IdCanal,
            'temaId' => $mensaje->IdTema,
            'usuarioId' => $mensaje->IdUsuario,
            'usuarioNombre' => $mensaje->usuario?->NombreCompleto,
            'contenido' => $mensaje->Contenido,
            'imagenUrl' => $mensaje->ImagenUrl,
            'idMensajeRespuesta' => $mensaje->IdMensajeRespuesta,
            'respuestaA' => $respuestaA,
            'reacciones' => $reacciones,
            'fechaEnvio' => optional($mensaje->FechaEnvio)->toIso8601String(),
        ];
    }
}
