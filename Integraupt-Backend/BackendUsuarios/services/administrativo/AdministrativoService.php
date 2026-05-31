<?php

namespace Services\Administrativo;

use Models\Administrativo;
use Services\Common\UsuarioServiceHelper;

class AdministrativoService extends UsuarioServiceHelper
{
    // ──────────────────────────────────────────────
    // Listar / Obtener
    // ──────────────────────────────────────────────

    public function listar(): array
    {
        $stmt = $this->db->query(
            'SELECT a.IdAdministrativo, a.IdUsuario, a.Turno, a.Extension, a.FechaIncorporacion,
                    es.IdEscuela, es.Nombre AS escuelaNombre
             FROM administrativo a
             LEFT JOIN escuela es ON a.Escuela = es.IdEscuela
             ORDER BY a.IdAdministrativo'
        );
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => $this->formatearAdministrativo($row), $rows);
    }

    public function obtener(int $id): array
    {
        $row = $this->buscarAdministrativoPorId($id);
        return $this->formatearAdministrativo($row);
    }

    // ──────────────────────────────────────────────
    // Registrar
    // ──────────────────────────────────────────────

    public function registrar(array $form): array
    {
        $this->validarDocumento($form['numDoc'], null);
        $this->validarCorreo($form['correo'], null);
        $this->validarPasswordObligatoria($form['password']);
        $this->validarRolAdministrativo($form['idRol'] ?? null);

        $turno       = Administrativo::normalizarTurno($form['turno'] ?? null);
        $escuela     = $this->obtenerEscuelaOpcional($form['idEscuela'] ?? null);
        $fechaIncorp = $this->fechaActualSiVacia($form['fechaIncorporacion'] ?? null);

        $this->db->beginTransaction();
        try {
            $idUsuario = $this->crearUsuarioConAuth($form, $form['correo'], $form['password']);

            $stmt = $this->db->prepare(
                'INSERT INTO administrativo (IdUsuario, Escuela, Turno, Extension, FechaIncorporacion)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $idUsuario,
                $escuela ? $escuela['IdEscuela'] : null,
                $turno,
                $form['extension'] ?? null,
                $fechaIncorp,
            ]);
            $idAdministrativo = (int)$this->db->lastInsertId();

            $this->db->commit();
            return $this->obtener($idAdministrativo);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ──────────────────────────────────────────────
    // Actualizar
    // ──────────────────────────────────────────────

    public function actualizar(int $id, array $form): array
    {
        $row = $this->buscarAdministrativoPorId($id);
        $idUsuario = (int)$row['IdUsuario'];

        $this->validarDocumento($form['numDoc'], $idUsuario);
        $this->validarCorreo($form['correo'], $idUsuario);
        $this->validarRolAdministrativo($form['idRol'] ?? null);

        $turno   = Administrativo::normalizarTurno($form['turno'] ?? null);
        $escuela = $this->obtenerEscuelaOpcional($form['idEscuela'] ?? null);

        $this->db->beginTransaction();
        try {
            $this->actualizarUsuarioConAuth($idUsuario, $form, $form['correo'], $form['password'] ?? null);

            $fechaIncorp = (!empty($form['fechaIncorporacion']))
                ? $this->parsearFecha($form['fechaIncorporacion'])
                : $row['FechaIncorporacion'];

            $stmt = $this->db->prepare(
                'UPDATE administrativo
                 SET Escuela=?, Turno=?, Extension=?, FechaIncorporacion=?
                 WHERE IdAdministrativo=?'
            );
            $stmt->execute([
                $escuela ? $escuela['IdEscuela'] : null,
                $turno,
                $form['extension'] ?? null,
                $fechaIncorp,
                $id,
            ]);

            $this->db->commit();
            return $this->obtener($id);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ──────────────────────────────────────────────
    // Estado / Eliminar
    // ──────────────────────────────────────────────

    public function actualizarEstado(int $id, bool $activo): array
    {
        $row = $this->buscarAdministrativoPorId($id);
        $this->actualizarEstadoUsuario((int)$row['IdUsuario'], $activo);
        return $this->obtener($id);
    }

    public function eliminar(int $id): void
    {
        $this->actualizarEstado($id, false);
    }

    // ──────────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────────

    private function buscarAdministrativoPorId(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.IdAdministrativo, a.IdUsuario, a.Turno, a.Extension, a.FechaIncorporacion,
                    es.IdEscuela, es.Nombre AS escuelaNombre
             FROM administrativo a
             LEFT JOIN escuela es ON a.Escuela = es.IdEscuela
             WHERE a.IdAdministrativo = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new \RuntimeException('Administrativo no encontrado.');
        }
        return $row;
    }

    private function formatearAdministrativo(array $row): array
    {
        return [
            'idAdministrativo'   => (int)$row['IdAdministrativo'],
            'usuario'            => $this->hidratarUsuario((int)$row['IdUsuario']),
            'escuela'            => $row['IdEscuela'] ? ['idEscuela' => (int)$row['IdEscuela'], 'nombre' => $row['escuelaNombre']] : null,
            'turno'              => $row['Turno'],
            'extension'          => $row['Extension'],
            'fechaIncorporacion' => $row['FechaIncorporacion'],
        ];
    }

    private function validarRolAdministrativo(?int $idRol): void
    {
        if ($idRol === null) {
            throw new \RuntimeException('El rol es obligatorio para un administrativo.');
        }
        $stmt = $this->db->prepare('SELECT IdRol FROM rol WHERE IdRol = ?');
        $stmt->execute([$idRol]);
        if (!$stmt->fetch()) {
            throw new \RuntimeException('Rol Administrativo no válido.');
        }
    }
}
