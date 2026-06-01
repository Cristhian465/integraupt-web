<?php

namespace Services\Common;

use Config\Database;

/**
 * Clase base con utilidades compartidas para todos los servicios de usuario.
 * Encapsula: validaciones, codificación de contraseña, manejo de fechas,
 * consultas comunes a usuario / usuario_auth / escuela.
 */
abstract class UsuarioServiceHelper
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ──────────────────────────────────────────────
    // Validaciones
    // ──────────────────────────────────────────────

    protected function validarDocumento(string $numDoc, ?int $idUsuarioActual): void
    {
        $stmt = $this->db->prepare('SELECT IdUsuario FROM usuario WHERE NumDoc = ?');
        $stmt->execute([$numDoc]);
        $row = $stmt->fetch();

        if ($row && ($idUsuarioActual === null || (int)$row['IdUsuario'] !== $idUsuarioActual)) {
            throw new \RuntimeException('El documento ya está registrado.');
        }
    }

    protected function validarCorreo(string $correo, ?int $idUsuarioActual): void
    {
        $stmt = $this->db->prepare('SELECT ua.IdUsuario FROM usuario_auth ua WHERE ua.CorreoU = ?');
        $stmt->execute([$correo]);
        $row = $stmt->fetch();

        if ($row && ($idUsuarioActual === null || (int)$row['IdUsuario'] !== $idUsuarioActual)) {
            throw new \RuntimeException('El correo ya está registrado.');
        }
    }

    protected function validarPasswordObligatoria(?string $password): void
    {
        if ($password === null || trim($password) === '') {
            throw new \RuntimeException('La contraseña es obligatoria.');
        }
    }

    // ──────────────────────────────────────────────
    // Contraseña
    // ──────────────────────────────────────────────

    protected function codificarPassword(string $password): string
    {
        return base64_encode($password);
    }

    protected function actualizarPasswordSiCorresponde(int $idAuth, ?string $password): void
    {
        if ($password !== null && trim($password) !== '') {
            $hash = $this->codificarPassword($password);
            $stmt = $this->db->prepare('UPDATE usuario_auth SET Password = ? WHERE IdAuth = ?');
            $stmt->execute([$hash, $idAuth]);
        }
    }

    // ──────────────────────────────────────────────
    // Estado de usuario
    // ──────────────────────────────────────────────

    protected function actualizarEstadoUsuario(int $idUsuario, bool $activo): void
    {
        $estado = $activo ? 1 : 0;
        $stmt = $this->db->prepare('UPDATE usuario SET Estado = ? WHERE IdUsuario = ?');
        $stmt->execute([$estado, $idUsuario]);
    }

    // ──────────────────────────────────────────────
    // Credenciales (usuario_auth)
    // ──────────────────────────────────────────────

    protected function obtenerCredencial(int $idUsuario): array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuario_auth WHERE IdUsuario = ?');
        $stmt->execute([$idUsuario]);
        $auth = $stmt->fetch();

        if (!$auth) {
            throw new \RuntimeException('No se encontraron credenciales para el usuario indicado.');
        }
        return $auth;
    }

    // ──────────────────────────────────────────────
    // Fechas
    // ──────────────────────────────────────────────

    protected function parsearFecha(string $fecha): string
    {
        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$d || $d->format('Y-m-d') !== $fecha) {
            throw new \RuntimeException('Formato de fecha inválido. Utiliza AAAA-MM-DD.');
        }
        return $fecha;
    }

    protected function fechaActualSiVacia(?string $fecha): string
    {
        if ($fecha === null || trim($fecha) === '') {
            return date('Y-m-d');
        }
        return $this->parsearFecha($fecha);
    }

    // ──────────────────────────────────────────────
    // Escuela opcional
    // ──────────────────────────────────────────────

    protected function obtenerEscuelaOpcional(?int $idEscuela): ?array
    {
        if ($idEscuela === null) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT * FROM escuela WHERE IdEscuela = ?');
        $stmt->execute([$idEscuela]);
        $escuela = $stmt->fetch();

        if (!$escuela) {
            throw new \RuntimeException('Escuela no válida.');
        }
        return $escuela;
    }

    // ──────────────────────────────────────────────
    // Creación / actualización de usuario + auth
    // ──────────────────────────────────────────────

    /**
     * Inserta un nuevo usuario y su registro de autenticación.
     * Devuelve el idUsuario generado.
     */
    protected function crearUsuarioConAuth(array $datos, string $correo, string $password): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuario (Nombre, Apellido, TipoDoc, NumDoc, Celular, Genero, Rol, Estado, FechaRegistro)
             VALUES (:nombre, :apellido, :tipoDoc, :numDoc, :celular, :genero, :rol, 1, NOW())'
        );
        $stmt->execute([
            ':nombre'   => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':tipoDoc'  => $datos['idTipoDoc'],
            ':numDoc'   => $datos['numDoc'],
            ':celular'  => $datos['celular']  ?? null,
            ':genero'   => isset($datos['genero']) ? (int)$datos['genero'] : null,
            ':rol'      => $datos['idRol'],
        ]);
        $idUsuario = (int)$this->db->lastInsertId();

        $stmt = $this->db->prepare(
            'INSERT INTO usuario_auth (IdUsuario, CorreoU, Password) VALUES (?, ?, ?)'
        );
        $stmt->execute([$idUsuario, $correo, $this->codificarPassword($password)]);

        return $idUsuario;
    }

    /**
     * Actualiza datos del usuario y opcionalmente la contraseña.
     */
    protected function actualizarUsuarioConAuth(int $idUsuario, array $datos, string $correo, ?string $password): void
    {
        $stmt = $this->db->prepare(
            'UPDATE usuario
             SET Nombre=:nombre, Apellido=:apellido, TipoDoc=:tipoDoc,
                 NumDoc=:numDoc, Celular=:celular, Genero=:genero, Rol=:rol
             WHERE IdUsuario=:id'
        );
        $stmt->execute([
            ':nombre'   => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':tipoDoc'  => $datos['idTipoDoc'],
            ':numDoc'   => $datos['numDoc'],
            ':celular'  => $datos['celular']  ?? null,
            ':genero'   => isset($datos['genero']) ? (int)$datos['genero'] : null,
            ':rol'      => $datos['idRol'],
            ':id'       => $idUsuario,
        ]);

        $stmt = $this->db->prepare('UPDATE usuario_auth SET CorreoU=? WHERE IdUsuario=?');
        $stmt->execute([$correo, $idUsuario]);

        $auth = $this->obtenerCredencial($idUsuario);
        $this->actualizarPasswordSiCorresponde((int)$auth['IdAuth'], $password);
    }

    // ──────────────────────────────────────────────
    // Hidratar usuario completo para respuesta JSON
    // ──────────────────────────────────────────────

    protected function hidratarUsuario(int $idUsuario): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, td.Nombre AS tipoDocNombre, td.Abreviatura AS tipoDocAbrev,
                    r.Nombre AS rolNombre,
                    ua.IdAuth, ua.CorreoU, ua.UltimoLogin
             FROM usuario u
             LEFT JOIN tipodocumento td ON u.TipoDoc = td.IdTipoDoc
             LEFT JOIN rol r            ON u.Rol      = r.IdRol
             LEFT JOIN usuario_auth ua  ON ua.IdUsuario = u.IdUsuario
             WHERE u.IdUsuario = ?'
        );
        $stmt->execute([$idUsuario]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new \RuntimeException('Usuario no encontrado.');
        }

        return [
            'idUsuario'     => (int)$row['IdUsuario'],
            'nombre'        => $row['Nombre'],
            'apellido'      => $row['Apellido'],
            'tipoDoc'       => ['idTipoDoc' => (int)$row['TipoDoc'], 'nombre' => $row['tipoDocNombre'], 'abreviatura' => $row['tipoDocAbrev']],
            'numDoc'        => $row['NumDoc'],
            'rol'           => ['idRol' => (int)$row['Rol'], 'nombre' => $row['rolNombre']],
            'celular'       => $row['Celular'],
            'genero'        => $row['Genero'] !== null ? (bool)$row['Genero'] : null,
            'estado'        => (int)$row['Estado'],
            'fechaRegistro' => $row['FechaRegistro'],
            'auth'          => $row['IdAuth'] ? [
                'idAuth'      => (int)$row['IdAuth'],
                'correoU'     => $row['CorreoU'],
                'ultimoLogin' => $row['UltimoLogin'],
            ] : null,
        ];
    }
}
