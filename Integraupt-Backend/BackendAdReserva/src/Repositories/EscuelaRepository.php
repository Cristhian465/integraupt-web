<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Escuela;
use PDO;

class EscuelaRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * @return Escuela[]
     */
    public function findAll(): array {
        $sql = "SELECT IdEscuela, IdFacultad, Nombre FROM escuela";
        $stmt = $this->db->query($sql);
        
        $escuelas = [];
        while ($row = $stmt->fetch()) {
            $escuelas[] = new Escuela(
                (int)$row['IdEscuela'],
                (int)$row['IdFacultad'],
                $row['Nombre']
            );
        }

        return $escuelas;
    }

    public function findById(int $id): ?Escuela {
        $sql = "SELECT IdEscuela, IdFacultad, Nombre FROM escuela WHERE IdEscuela = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return new Escuela(
            (int)$row['IdEscuela'],
            (int)$row['IdFacultad'],
            $row['Nombre']
        );
    }
}
