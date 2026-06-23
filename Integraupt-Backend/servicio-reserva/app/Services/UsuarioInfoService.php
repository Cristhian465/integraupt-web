<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class UsuarioInfoService
{
    private const CONSULTA_DATOS_USUARIO = <<<'SQL'
        SELECT
            TRIM(COALESCE(u.Nombre, '')) AS nombre,
            TRIM(COALESCE(u.Apellido, '')) AS apellido,
            TRIM(COALESCE(r.Nombre, '')) AS rol,
            TRIM(COALESCE(e.Codigo, '')) AS codigo_estudiante,
            TRIM(COALESCE(d.CodigoDocente, '')) AS codigo_docente,
            TRIM(COALESCE(u.NumDoc, '')) AS numero_documento,
            u.IdUsuario AS id_usuario
        FROM usuario u
        JOIN rol r ON r.IdRol = u.Rol
        LEFT JOIN estudiante e ON e.IdUsuario = u.IdUsuario
        LEFT JOIN docente d ON d.IdUsuario = u.IdUsuario
        WHERE u.IdUsuario = ?
        SQL;

    /**
     * @return array{nombreCompleto: string, codigo: string, rol: string}|null
     */
    public function obtenerSolicitante(?int $usuarioId): ?array
    {
        if ($usuarioId === null) {
            return null;
        }

        $fila = DB::selectOne(self::CONSULTA_DATOS_USUARIO, [$usuarioId]);
        if ($fila === null) {
            return null;
        }

        return [
            'nombreCompleto' => $this->construirNombre($fila->nombre, $fila->apellido, $fila->id_usuario),
            'codigo' => $this->seleccionarCodigo(
                $fila->codigo_estudiante,
                $fila->codigo_docente,
                $fila->numero_documento,
                $fila->id_usuario
            ),
            'rol' => $fila->rol !== null && $fila->rol !== '' ? $fila->rol : 'Usuario',
        ];
    }

    private function construirNombre(?string $nombre, ?string $apellido, int $idUsuario): string
    {
        $nombreCompleto = trim(implode(' ', array_filter([$nombre, $apellido], fn ($v) => $v !== null && $v !== '')));

        return $nombreCompleto !== '' ? $nombreCompleto : "Usuario {$idUsuario}";
    }

    private function seleccionarCodigo(
        ?string $codigoEstudiante,
        ?string $codigoDocente,
        ?string $numeroDocumento,
        int $idUsuario
    ): string {
        foreach ([$codigoEstudiante, $codigoDocente, $numeroDocumento] as $valor) {
            $valor = $valor !== null ? trim($valor) : '';
            if ($valor !== '') {
                return $valor;
            }
        }

        return (string) $idUsuario;
    }
}
