<?php

declare(strict_types=1);

namespace Repository;

use Config\Database;
use PDO;

class AdministrativoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Find administrativo by IdUsuario, joining escuela.
     */
    public function findByUsuarioId(int $usuarioId): ?array
    {
        $sql = "
            SELECT
                a.IdAdministrativo,
                a.IdUsuario,
                a.Turno,
                a.Extension,
                a.FechaIncorporacion,
                a.Escuela         AS a_EscuelaId,
                es.IdEscuela      AS escuela_IdEscuela,
                es.Nombre         AS escuela_Nombre,
                es.IdFacultad     AS escuela_IdFacultad
            FROM administrativo a
            LEFT JOIN escuela es ON es.IdEscuela = a.Escuela
            WHERE a.IdUsuario = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $usuarioId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
