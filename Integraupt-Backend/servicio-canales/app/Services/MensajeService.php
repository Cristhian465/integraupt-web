<?php

namespace App\Services;

use App\Models\Canal;
use App\Models\CanalMensaje;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class MensajeService
{
    public function __construct(private CanalService $canalService)
    {
    }

    public function listar(int $idCanal, ?int $temaId = null, int $limite = 200): Collection
    {
        Canal::findOrFail($idCanal);

        $query = CanalMensaje::with(['usuario', 'respuestaA.usuario', 'reacciones.usuario'])
            ->where('IdCanal', $idCanal);

        if ($temaId !== null) {
            $query->where('IdTema', $temaId);
        }

        return $query->orderByDesc('FechaEnvio')
            ->limit($limite)
            ->get()
            ->reverse()
            ->values();
    }

    public function enviar(array $datos): CanalMensaje
    {
        $idCanal = (int) $datos['IdCanal'];
        $idUsuario = (int) $datos['IdUsuario'];

        Canal::findOrFail($idCanal);
        Usuario::findOrFail($idUsuario);

        if (! $this->canalService->esMiembro($idCanal, $idUsuario)) {
            throw new InvalidArgumentException('Solo los miembros del canal pueden enviar mensajes.');
        }

        $contenido = trim($datos['Contenido'] ?? '');
        $archivoUrl = $datos['ArchivoUrl'] ?? null;

        if ($contenido === '' && ! $archivoUrl) {
            throw new InvalidArgumentException('El mensaje debe tener contenido o un archivo adjunto.');
        }

        $payload = [
            'IdCanal' => $idCanal,
            'IdUsuario' => $idUsuario,
            'Contenido' => $contenido,
            'ArchivoUrl' => $archivoUrl ?: null,
            'ArchivoTipo' => $archivoUrl ? ($datos['ArchivoTipo'] ?? 'file') : null,
            'ArchivoNombre' => $archivoUrl ? ($datos['ArchivoNombre'] ?? null) : null,
            'IdTema' => isset($datos['IdTema']) && $datos['IdTema'] ? (int) $datos['IdTema'] : null,
            'IdMensajeRespuesta' => isset($datos['IdMensajeRespuesta']) && $datos['IdMensajeRespuesta'] ? (int) $datos['IdMensajeRespuesta'] : null,
        ];

        $mensaje = CanalMensaje::create($payload);

        return $mensaje->refresh()->load(['usuario', 'respuestaA.usuario', 'reacciones.usuario']);
    }

    public function eliminar(int $idMensaje, int $idUsuarioSolicitante, bool $esAdmin): void
    {
        $mensaje = CanalMensaje::find($idMensaje);

        if (! $mensaje) {
            throw new ModelNotFoundException('El mensaje indicado no existe.');
        }

        $esAutor = (int) $mensaje->IdUsuario === $idUsuarioSolicitante;
        $esCreadorCanal = $this->canalService->esCreador($mensaje->IdCanal, $idUsuarioSolicitante);

        if (! $esAutor && ! $esCreadorCanal && ! $esAdmin) {
            throw new InvalidArgumentException('No tienes permiso para eliminar este mensaje.');
        }

        $mensaje->update([
            'Estado' => 'eliminado',
            'Contenido' => '',
            'ArchivoUrl' => null,
            'ArchivoTipo' => null,
            'ArchivoNombre' => null,
        ]);
    }

    public function editar(int $idMensaje, int $idUsuarioSolicitante, string $contenido): CanalMensaje
    {
        $mensaje = CanalMensaje::find($idMensaje);

        if (! $mensaje) {
            throw new ModelNotFoundException('El mensaje indicado no existe.');
        }

        if ((int) $mensaje->IdUsuario !== $idUsuarioSolicitante) {
            throw new InvalidArgumentException('Solo el autor puede editar este mensaje.');
        }

        if ($mensaje->Estado === 'eliminado') {
            throw new InvalidArgumentException('No se puede editar un mensaje eliminado.');
        }

        $contenido = trim($contenido);

        if ($contenido === '' && ! $mensaje->ArchivoUrl) {
            throw new InvalidArgumentException('El mensaje debe tener contenido.');
        }

        $mensaje->update([
            'Contenido' => $contenido,
            'EditadoEn' => now(),
        ]);

        return $mensaje->refresh()->load(['usuario', 'respuestaA.usuario', 'reacciones.usuario']);
    }
}
