<?php

namespace App\Services;

use App\Models\Canal;
use App\Models\CanalMiembro;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class CanalService
{
    private const ESTADOS_VALIDOS = [Canal::ESTADO_ACTIVO, Canal::ESTADO_ARCHIVADO];

    public function listarParaUsuario(int $idUsuario, bool $esAdmin): Collection
    {
        $query = Canal::with(['creador', 'miembros.usuario'])->orderByDesc('FechaCreacion');

        if (! $esAdmin) {
            $query->whereHas('miembros', function ($q) use ($idUsuario) {
                $q->where('IdUsuario', $idUsuario);
            });
        }

        return $query->get();
    }

    public function encontrar(int $id): Canal
    {
        return Canal::with(['creador', 'miembros.usuario'])->findOrFail($id);
    }

    public function crear(array $datos): Canal
    {
        $creador = Usuario::findOrFail($datos['IdCreador']);

        if (! $creador->esAdmin() && ! $creador->esDocente()) {
            throw new InvalidArgumentException('Solo administradores y docentes pueden crear canales.');
        }

        $canal = Canal::create([
            'Nombre' => $datos['Nombre'],
            'Descripcion' => $datos['Descripcion'] ?? null,
            'IdCreador' => $creador->IdUsuario,
            'TipoCreador' => $creador->esAdmin() ? Canal::TIPO_CREADOR_ADMIN : Canal::TIPO_CREADOR_DOCENTE,
            'Estado' => Canal::ESTADO_ACTIVO,
            'Color' => $datos['Color'] ?? null,
            'FotoUrl' => $datos['FotoUrl'] ?? null,
        ]);

        CanalMiembro::create([
            'IdCanal' => $canal->IdCanal,
            'IdUsuario' => $creador->IdUsuario,
            'Rol' => CanalMiembro::ROL_CREADOR,
        ]);

        $miembros = array_filter(array_unique($datos['Miembros'] ?? []), fn ($id) => (int) $id !== (int) $creador->IdUsuario);
        $this->agregarMiembros($canal->IdCanal, $miembros);

        return $canal->refresh()->load(['creador', 'miembros.usuario']);
    }

    public function actualizar(int $id, array $datos): Canal
    {
        $canal = Canal::findOrFail($id);

        $canal->update([
            'Nombre' => $datos['Nombre'] ?? $canal->Nombre,
            'Descripcion' => $datos['Descripcion'] ?? $canal->Descripcion,
            'Color' => array_key_exists('Color', $datos) ? $datos['Color'] : $canal->Color,
            'FotoUrl' => array_key_exists('FotoUrl', $datos) ? $datos['FotoUrl'] : $canal->FotoUrl,
        ]);

        return $canal->refresh()->load(['creador', 'miembros.usuario']);
    }

    public function cambiarEstado(int $id, string $estado): Canal
    {
        if (! in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new InvalidArgumentException("Estado de canal invalido: {$estado}");
        }

        $canal = Canal::findOrFail($id);
        $canal->update(['Estado' => $estado]);

        return $canal->refresh();
    }

    public function agregarMiembros(int $idCanal, array $idsUsuarios): Canal
    {
        $canal = Canal::findOrFail($idCanal);

        $existentes = $canal->miembros()->pluck('IdUsuario')->all();

        foreach ($idsUsuarios as $idUsuario) {
            $idUsuario = (int) $idUsuario;

            if (in_array($idUsuario, $existentes, true)) {
                continue;
            }

            if (! Usuario::where('IdUsuario', $idUsuario)->exists()) {
                throw new ModelNotFoundException("El usuario {$idUsuario} no existe.");
            }

            CanalMiembro::create([
                'IdCanal' => $canal->IdCanal,
                'IdUsuario' => $idUsuario,
                'Rol' => CanalMiembro::ROL_MIEMBRO,
            ]);

            $existentes[] = $idUsuario;
        }

        return $canal->refresh()->load(['creador', 'miembros.usuario']);
    }

    public function quitarMiembro(int $idCanal, int $idUsuario): void
    {
        $miembro = CanalMiembro::where('IdCanal', $idCanal)->where('IdUsuario', $idUsuario)->first();

        if (! $miembro) {
            throw new ModelNotFoundException('El usuario indicado no es miembro de este canal.');
        }

        if ($miembro->Rol === CanalMiembro::ROL_CREADOR) {
            throw new InvalidArgumentException('No se puede quitar al creador del canal.');
        }

        $miembro->delete();
    }

    public function esMiembro(int $idCanal, int $idUsuario): bool
    {
        return CanalMiembro::where('IdCanal', $idCanal)->where('IdUsuario', $idUsuario)->exists();
    }

    public function esCreador(int $idCanal, int $idUsuario): bool
    {
        return CanalMiembro::where('IdCanal', $idCanal)
            ->where('IdUsuario', $idUsuario)
            ->where('Rol', CanalMiembro::ROL_CREADOR)
            ->exists();
    }
}
