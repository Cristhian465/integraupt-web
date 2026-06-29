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
            'archivoUrl' => 'nullable|string',
            'archivoTipo' => 'nullable|string|in:image,video,audio,file',
            'archivoNombre' => 'nullable|string',
            'temaId' => 'nullable|integer',
            'idMensajeRespuesta' => 'nullable|integer',
        ]);

        if (empty($request->input('contenido')) && empty($request->input('archivoUrl'))) {
            return response()->json(['message' => 'El mensaje debe tener contenido o un archivo adjunto.'], 422);
        }

        try {
            $mensaje = $this->mensajeService->enviar([
                'IdCanal' => $idCanal,
                'IdUsuario' => $request->input('usuarioId'),
                'Contenido' => $request->input('contenido', ''),
                'ArchivoUrl' => $request->input('archivoUrl'),
                'ArchivoTipo' => $request->input('archivoTipo'),
                'ArchivoNombre' => $request->input('archivoNombre'),
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

    public function update(Request $request, $idCanal, $idMensaje)
    {
        $request->validate([
            'usuarioId' => 'required|integer',
            'contenido' => 'required|string',
        ]);

        try {
            $mensaje = $this->mensajeService->editar(
                (int) $idMensaje,
                (int) $request->input('usuarioId'),
                $request->input('contenido')
            );

            return response()->json($this->mapear($mensaje));
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
        $eliminado = $mensaje->Estado === 'eliminado';

        $respuestaA = null;
        if ($mensaje->IdMensajeRespuesta && $mensaje->respuestaA) {
            $r = $mensaje->respuestaA;
            $rEliminado = $r->Estado === 'eliminado';
            $respuestaA = [
                'id' => $r->IdMensaje,
                'usuarioNombre' => $r->usuario?->NombreCompleto,
                'contenido' => $rEliminado ? null : $r->Contenido,
                'archivoUrl' => $rEliminado ? null : $r->ArchivoUrl,
                'archivoTipo' => $rEliminado ? null : $r->ArchivoTipo,
                'archivoNombre' => $rEliminado ? null : $r->ArchivoNombre,
                'eliminado' => $rEliminado,
            ];
        }

        $reacciones = $mensaje->reacciones
            ->groupBy('Emoji')
            ->map(fn ($grupo, $emoji) => [
                'emoji' => $emoji,
                'cantidad' => $grupo->count(),
                'usuarios' => $grupo->pluck('IdUsuario')->all(),
                'usuariosNombres' => $grupo->map(fn ($r) => $r->usuario?->NombreCompleto ?? 'Usuario')->all(),
            ])
            ->values()
            ->all();

        return [
            'id' => $mensaje->IdMensaje,
            'canalId' => $mensaje->IdCanal,
            'temaId' => $mensaje->IdTema,
            'usuarioId' => $mensaje->IdUsuario,
            'usuarioNombre' => $mensaje->usuario?->NombreCompleto,
            'contenido' => $eliminado ? null : $mensaje->Contenido,
            'archivoUrl' => $eliminado ? null : $mensaje->ArchivoUrl,
            'archivoTipo' => $eliminado ? null : $mensaje->ArchivoTipo,
            'archivoNombre' => $eliminado ? null : $mensaje->ArchivoNombre,
            'idMensajeRespuesta' => $mensaje->IdMensajeRespuesta,
            'respuestaA' => $respuestaA,
            'reacciones' => $reacciones,
            'eliminado' => $eliminado,
            'editado' => $mensaje->EditadoEn !== null,
            'fechaEnvio' => optional($mensaje->FechaEnvio)->toIso8601String(),
        ];
    }
}
