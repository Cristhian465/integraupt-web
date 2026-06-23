<?php

declare(strict_types=1);

namespace Repository;

use Config\Database;
use PDO;

class UsuarioAuthRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Find by email (case-insensitive) OR by usuario.NumDoc.
     * Returns a full row joining usuario and rol.
     */
    public function findByCorreoUOrNumDoc(string $correoU, string $numDoc): ?array
    {
        $sql = "
            SELECT
                ua.IdAuth,
                ua.IdUsuario,
                ua.CorreoU,
                ua.Password,
                ua.UltimoLogin,
                ua.SesionToken,
                ua.SesionExpira,
                ua.SesionTipo,
                u.IdUsuario  AS u_IdUsuario,
                u.Nombre     AS u_Nombre,
                u.Apellido   AS u_Apellido,
                u.NumDoc     AS u_NumDoc,
                u.Estado     AS u_Estado,
                u.FechaRegistro AS u_FechaRegistro,
                r.IdRol      AS r_IdRol,
                r.Nombre     AS r_Nombre
            FROM usuario_auth ua
            INNER JOIN usuario u  ON u.IdUsuario = ua.IdUsuario
            LEFT  JOIN rol     r  ON r.IdRol     = u.Rol
            WHERE LOWER(ua.CorreoU) = LOWER(:correoU)
               OR u.NumDoc = :numDoc
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':correoU' => $correoU, ':numDoc' => $numDoc]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find by session token.
     */
    public function findBySesionToken(string $token): ?array
    {
        $sql = "
            SELECT
                ua.IdAuth,
                ua.IdUsuario,
                ua.CorreoU,
                ua.Password,
                ua.UltimoLogin,
                ua.SesionToken,
                ua.SesionExpira,
                ua.SesionTipo,
                u.IdUsuario  AS u_IdUsuario,
                u.Nombre     AS u_Nombre,
                u.Apellido   AS u_Apellido,
                u.NumDoc     AS u_NumDoc,
                u.Estado     AS u_Estado,
                r.IdRol      AS r_IdRol,
                r.Nombre     AS r_Nombre
            FROM usuario_auth ua
            INNER JOIN usuario u ON u.IdUsuario = ua.IdUsuario
            LEFT  JOIN rol     r ON r.IdRol     = u.Rol
            WHERE ua.SesionToken = :token
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find by usuarioId.
     */
    public function findByUsuarioId(int $usuarioId): ?array
    {
        $sql = "
            SELECT
                ua.IdAuth,
                ua.IdUsuario,
                ua.CorreoU,
                ua.Password,
                ua.UltimoLogin,
                ua.SesionToken,
                ua.SesionExpira,
                ua.SesionTipo
            FROM usuario_auth ua
            WHERE ua.IdUsuario = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $usuarioId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Update session fields (token, expiry, tipo, ultimoLogin).
     */
    public function updateSession(int $idAuth, ?string $token, ?string $expira, ?string $tipo, ?string $ultimoLogin): void
    {
        $sql = "
            UPDATE usuario_auth
            SET SesionToken  = :token,
                SesionExpira = :expira,
                SesionTipo   = :tipo,
                UltimoLogin  = :ultimoLogin
            WHERE IdAuth = :idAuth
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':token'       => $token,
            ':expira'      => $expira,
            ':tipo'        => $tipo,
            ':ultimoLogin' => $ultimoLogin,
            ':idAuth'      => $idAuth,
        ]);
    }

    /**
     * Clear session (set token/expira/tipo to NULL).
     */
    public function clearSession(int $idAuth): void
    {
        $this->updateSession($idAuth, null, null, null, null);
    }
}
