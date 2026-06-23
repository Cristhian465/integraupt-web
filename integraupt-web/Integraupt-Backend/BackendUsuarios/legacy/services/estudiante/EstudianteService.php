<?php

namespace Services\Estudiante;

use Services\Common\UsuarioServiceHelper;

class EstudianteService extends UsuarioServiceHelper
{
    private const ROL_ESTUDIANTE = 2;

    // ──────────────────────────────────────────────
    // Listar / Obtener
    // ──────────────────────────────────────────────

    public function listar(): array
    {
        $stmt = $this->db->query(
            'SELECT e.IdEstudiante, e.IdUsuario, e.Codigo,
                    es.IdEscuela, es.Nombre AS escuelaNombre
             FROM estudiante e
             LEFT JOIN escuela es ON e.Escuela = es.IdEscuela
             ORDER BY e.IdEstudiante'
        );
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => $this->formatearEstudiante($row), $rows);
    }

    public function obtener(int $id): array
    {
        $row = $this->buscarEstudiantePorId($id);
        return $this->formatearEstudiante($row);
    }

    // ──────────────────────────────────────────────
    // Registrar
    // ──────────────────────────────────────────────

    public function registrar(array $form): array
    {
        $this->validarDocumento($form['numDoc'], null);
        $this->validarCorreo($form['correo'], null);
        $this->validarPasswordObligatoria($form['password']);
        $this->verificarEscuelaObligatoria($form['idEscuela'] ?? null);

        $form['idRol'] = self::ROL_ESTUDIANTE;

        $this->db->beginTransaction();
        try {
            $idUsuario = $this->crearUsuarioConAuth($form, $form['correo'], $form['password']);

            $stmt = $this->db->prepare(
                'INSERT INTO estudiante (IdUsuario, Escuela, Codigo) VALUES (?, ?, ?)'
            );
            $stmt->execute([$idUsuario, $form['idEscuela'], $form['codigo']]);
            $idEstudiante = (int)$this->db->lastInsertId();

            $this->db->commit();
            return $this->obtener($idEstudiante);
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
        $row = $this->buscarEstudiantePorId($id);
        $idUsuario = (int)$row['IdUsuario'];

        $this->validarDocumento($form['numDoc'], $idUsuario);
        $this->validarCorreo($form['correo'], $idUsuario);
        $this->verificarEscuelaObligatoria($form['idEscuela'] ?? null);

        $form['idRol'] = self::ROL_ESTUDIANTE;

        $this->db->beginTransaction();
        try {
            $this->actualizarUsuarioConAuth($idUsuario, $form, $form['correo'], $form['password'] ?? null);

            $stmt = $this->db->prepare(
                'UPDATE estudiante SET Escuela = ?, Codigo = ? WHERE IdEstudiante = ?'
            );
            $stmt->execute([$form['idEscuela'], $form['codigo'], $id]);

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
        $row = $this->buscarEstudiantePorId($id);
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

    private function buscarEstudiantePorId(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.IdEstudiante, e.IdUsuario, e.Codigo,
                    es.IdEscuela, es.Nombre AS escuelaNombre
             FROM estudiante e
             LEFT JOIN escuela es ON e.Escuela = es.IdEscuela
             WHERE e.IdEstudiante = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new \RuntimeException('Estudiante no encontrado.');
        }
        return $row;
    }

    private function formatearEstudiante(array $row): array
    {
        return [
            'idEstudiante' => (int)$row['IdEstudiante'],
            'usuario'      => $this->hidratarUsuario((int)$row['IdUsuario']),
            'escuela'      => $row['IdEscuela'] ? ['idEscuela' => (int)$row['IdEscuela'], 'nombre' => $row['escuelaNombre']] : null,
            'codigo'       => $row['Codigo'],
        ];
    }

    private function verificarEscuelaObligatoria(?int $idEscuela): void
    {
        if ($idEscuela === null) {
            throw new \RuntimeException('La escuela es obligatoria para un estudiante.');
        }
        $stmt = $this->db->prepare('SELECT IdEscuela FROM escuela WHERE IdEscuela = ?');
        $stmt->execute([$idEscuela]);
        if (!$stmt->fetch()) {
            throw new \RuntimeException('Escuela no válida.');
        }
    }
}
