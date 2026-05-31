<?php

namespace Services\Docente;

use Models\Docente;
use Services\Common\UsuarioServiceHelper;

class DocenteService extends UsuarioServiceHelper
{
    private const ROL_DOCENTE = 1;

    // ──────────────────────────────────────────────
    // Listar / Obtener
    // ──────────────────────────────────────────────

    public function listar(): array
    {
        $stmt = $this->db->query(
            'SELECT d.IdDocente, d.IdUsuario, d.CodigoDocente, d.TipoContrato, d.Especialidad, d.FechaIncorporacion,
                    es.IdEscuela, es.Nombre AS escuelaNombre
             FROM docente d
             LEFT JOIN escuela es ON d.Escuela = es.IdEscuela
             ORDER BY d.IdDocente'
        );
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => $this->formatearDocente($row), $rows);
    }

    public function obtener(int $id): array
    {
        $row = $this->buscarDocentePorId($id);
        return $this->formatearDocente($row);
    }

    // ──────────────────────────────────────────────
    // Registrar
    // ──────────────────────────────────────────────

    public function registrar(array $form): array
    {
        $this->validarDocumento($form['numDoc'], null);
        $this->validarCorreo($form['correo'], null);
        $this->validarPasswordObligatoria($form['password']);

        $tipoContrato = Docente::normalizarTipoContrato($form['tipoContrato'] ?? null);
        $escuela      = $this->obtenerEscuelaOpcional($form['idEscuela'] ?? null);
        $fechaIncorp  = $this->fechaActualSiVacia($form['fechaIncorporacion'] ?? null);

        $form['idRol'] = self::ROL_DOCENTE;

        $this->db->beginTransaction();
        try {
            $idUsuario = $this->crearUsuarioConAuth($form, $form['correo'], $form['password']);

            $stmt = $this->db->prepare(
                'INSERT INTO docente (IdUsuario, CodigoDocente, Escuela, TipoContrato, Especialidad, FechaIncorporacion)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $idUsuario,
                $form['codigoDocente'],
                $escuela ? $escuela['IdEscuela'] : null,
                $tipoContrato,
                $form['especialidad'] ?? null,
                $fechaIncorp,
            ]);
            $idDocente = (int)$this->db->lastInsertId();

            $this->db->commit();
            return $this->obtener($idDocente);
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
        $row = $this->buscarDocentePorId($id);
        $idUsuario = (int)$row['IdUsuario'];

        $this->validarDocumento($form['numDoc'], $idUsuario);
        $this->validarCorreo($form['correo'], $idUsuario);

        $tipoContrato = Docente::normalizarTipoContrato($form['tipoContrato'] ?? null);
        $escuela      = $this->obtenerEscuelaOpcional($form['idEscuela'] ?? null);

        $form['idRol'] = self::ROL_DOCENTE;

        $this->db->beginTransaction();
        try {
            $this->actualizarUsuarioConAuth($idUsuario, $form, $form['correo'], $form['password'] ?? null);

            $stmt = $this->db->prepare(
                'UPDATE docente
                 SET CodigoDocente=?, Escuela=?, TipoContrato=?, Especialidad=?, FechaIncorporacion=?
                 WHERE IdDocente=?'
            );
            $fechaIncorp = (!empty($form['fechaIncorporacion']))
                ? $this->parsearFecha($form['fechaIncorporacion'])
                : $row['FechaIncorporacion'];

            $stmt->execute([
                $form['codigoDocente'],
                $escuela ? $escuela['IdEscuela'] : null,
                $tipoContrato,
                $form['especialidad'] ?? null,
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
        $row = $this->buscarDocentePorId($id);
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

    private function buscarDocentePorId(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT d.IdDocente, d.IdUsuario, d.CodigoDocente, d.TipoContrato, d.Especialidad, d.FechaIncorporacion,
                    es.IdEscuela, es.Nombre AS escuelaNombre
             FROM docente d
             LEFT JOIN escuela es ON d.Escuela = es.IdEscuela
             WHERE d.IdDocente = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new \RuntimeException('Docente no encontrado.');
        }
        return $row;
    }

    private function formatearDocente(array $row): array
    {
        return [
            'idDocente'          => (int)$row['IdDocente'],
            'usuario'            => $this->hidratarUsuario((int)$row['IdUsuario']),
            'codigoDocente'      => $row['CodigoDocente'],
            'escuela'            => $row['IdEscuela'] ? ['idEscuela' => (int)$row['IdEscuela'], 'nombre' => $row['escuelaNombre']] : null,
            'tipoContrato'       => $row['TipoContrato'],
            'especialidad'       => $row['Especialidad'],
            'fechaIncorporacion' => $row['FechaIncorporacion'],
        ];
    }
}
