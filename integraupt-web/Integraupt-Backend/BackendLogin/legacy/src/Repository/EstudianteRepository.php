<?php

declare(strict_types=1);

namespace Repository;

use Config\Database;
use PDO;

class EstudianteRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Find estudiante by IdUsuario, joining escuela.
     */
    public function findByUsuarioId(int $usuarioId): ?array
    {
        $sql = "
            SELECT
                e.IdEstudiante,
                e.IdUsuario,
                e.Codigo,
                e.Escuela        AS e_EscuelaId,
                es.IdEscuela     AS escuela_IdEscuela,
                es.Nombre        AS escuela_Nombre,
                es.IdFacultad    AS escuela_IdFacultad
            FROM estudiante e
            LEFT JOIN escuela es ON es.IdEscuela = e.Escuela
            WHERE e.IdUsuario = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $usuarioId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find estudiante by codigo (case-insensitive), joining escuela.
     */
    public function findByCodigo(string $codigo): ?array
    {
        $sql = "
            SELECT
                e.IdEstudiante,
                e.IdUsuario,
                e.Codigo,
                e.Escuela        AS e_EscuelaId,
                es.IdEscuela     AS escuela_IdEscuela,
                es.Nombre        AS escuela_Nombre,
                es.IdFacultad    AS escuela_IdFacultad
            FROM estudiante e
            LEFT JOIN escuela es ON es.IdEscuela = e.Escuela
            WHERE LOWER(e.Codigo) = LOWER(:codigo)
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':codigo' => $codigo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
