<?php

namespace App\Services;

use App\Models\Docente;
use App\Models\Estudiante;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UsuarioBusquedaService
{
    private const DEFAULT_LIMIT = 20;

    public function buscarUsuarios(
        ?string $tipoUsuarioValor,
        ?string $rolValor,
        ?string $query,
        ?int $facultadId,
        ?int $escuelaId,
        ?int $escuelaContextoId,
        ?int $limite
    ): array {
        $tipoUsuario = $this->normalizarTipoUsuario($tipoUsuarioValor);
        $rolBusqueda = $this->normalizarRol($rolValor);
        $termino = $query !== null && trim($query) !== '' ? trim($query) : null;
        $limit = ($limite !== null && $limite > 0) ? $limite : self::DEFAULT_LIMIT;

        $facultadFiltro = $facultadId;
        $escuelaFiltro = $escuelaId;

        if ($rolBusqueda === 'SUPERVISOR') {
            $escuelaRestriccion = $escuelaContextoId ?? $escuelaId;
            if ($escuelaRestriccion === null) {
                throw new InvalidArgumentException('Los supervisores deben tener una escuela asociada para realizar búsquedas.');
            }
            $escuelaFiltro = $escuelaRestriccion;
            $facultadFiltro = null;
        }

        if ($tipoUsuario === 'ESTUDIANTE') {
            return $this->buscarEstudiantes($termino, $facultadFiltro, $escuelaFiltro, $limit);
        }

        return $this->buscarDocentes($termino, $facultadFiltro, $escuelaFiltro, $limit);
    }

    private function buscarEstudiantes(?string $termino, ?int $facultadId, ?int $escuelaId, int $limit): array
    {
        $query = Estudiante::query()
            ->with(['usuario', 'escuela.facultad'])
            ->join('usuario', 'usuario.IdUsuario', '=', 'estudiante.IdUsuario')
            ->join('escuela', 'escuela.IdEscuela', '=', 'estudiante.Escuela')
            ->join('facultad', 'facultad.IdFacultad', '=', 'escuela.IdFacultad')
            ->select('estudiante.*');

        $this->aplicarFiltros($query, $termino, $facultadId, $escuelaId, 'estudiante.Codigo');

        $estudiantes = $query->orderBy('usuario.Apellido')->orderBy('usuario.Nombre')
            ->limit($limit)
            ->get();

        return $estudiantes->map(fn (Estudiante $estudiante) => $this->mapearUsuario(
            $estudiante->usuario,
            $estudiante->Codigo,
            $estudiante->escuela
        ))->all();
    }

    private function buscarDocentes(?string $termino, ?int $facultadId, ?int $escuelaId, int $limit): array
    {
        $query = Docente::query()
            ->with(['usuario', 'escuela.facultad'])
            ->join('usuario', 'usuario.IdUsuario', '=', 'docente.IdUsuario')
            ->leftJoin('escuela', 'escuela.IdEscuela', '=', 'docente.Escuela')
            ->leftJoin('facultad', 'facultad.IdFacultad', '=', 'escuela.IdFacultad')
            ->select('docente.*');

        $this->aplicarFiltros($query, $termino, $facultadId, $escuelaId, 'docente.CodigoDocente');

        $docentes = $query->orderBy('usuario.Apellido')->orderBy('usuario.Nombre')
            ->limit($limit)
            ->get();

        return $docentes->map(fn (Docente $docente) => $this->mapearUsuario(
            $docente->usuario,
            $docente->CodigoDocente,
            $docente->escuela
        ))->all();
    }

    private function aplicarFiltros($query, ?string $termino, ?int $facultadId, ?int $escuelaId, string $codigoColumna): void
    {
        if ($escuelaId !== null) {
            $query->where('escuela.IdEscuela', $escuelaId);
        }

        if ($facultadId !== null) {
            $query->where('facultad.IdFacultad', $facultadId);
        }

        if ($termino !== null) {
            $like = '%' . strtolower($termino) . '%';
            $query->where(function ($q) use ($like, $codigoColumna) {
                $q->whereRaw('LOWER(usuario.Nombre) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(usuario.Apellido) LIKE ?', [$like])
                    ->orWhereRaw("LOWER(CONCAT(usuario.Nombre, ' ', usuario.Apellido)) LIKE ?", [$like])
                    ->orWhereRaw("LOWER({$codigoColumna}) LIKE ?", [$like]);
            });
        }
    }

    private function mapearUsuario($usuario, ?string $codigo, $escuela): array
    {
        return [
            'id' => $usuario?->IdUsuario,
            'nombreCompleto' => $usuario?->nombre_completo,
            'codigo' => $codigo,
            'escuelaId' => $escuela?->IdEscuela,
            'escuelaNombre' => $escuela?->Nombre,
            'facultadId' => $escuela?->facultad?->IdFacultad,
            'facultadNombre' => $escuela?->facultad?->Nombre,
        ];
    }

    private function normalizarTipoUsuario(?string $valor): string
    {
        if ($valor === null || trim($valor) === '') {
            throw new InvalidArgumentException('El tipo de usuario no puede ser vacío');
        }

        $normalizado = strtoupper(trim($valor));
        if (!in_array($normalizado, ['ESTUDIANTE', 'DOCENTE'], true)) {
            throw new InvalidArgumentException("Tipo de usuario desconocido: {$valor}");
        }

        return $normalizado;
    }

    private function normalizarRol(?string $valor): string
    {
        if ($valor === null || trim($valor) === '') {
            return 'ADMINISTRATIVO';
        }

        $normalizado = strtoupper(trim($valor));
        if (!in_array($normalizado, ['ADMINISTRATIVO', 'SUPERVISOR'], true)) {
            throw new InvalidArgumentException("Rol de búsqueda desconocido: {$valor}");
        }

        return $normalizado;
    }
}
