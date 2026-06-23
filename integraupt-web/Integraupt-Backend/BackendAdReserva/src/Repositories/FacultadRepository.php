<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Facultad;
use PDO;

class FacultadRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * @return Facultad[]
     */
    public function findAll(): array {
        $sql = "SELECT IdFacultad, Nombre, Abreviatura FROM facultad";
        $stmt = $this->db->query($sql);
        
        $facultades = [];
        while ($row = $stmt->fetch()) {
            $facultades[] = new Facultad(
                (int)$row['IdFacultad'],
                $row['Nombre'],
                $row['Abreviatura']
            );
        }

        return $facultades;
    }

    public function findById(int $id): ?Facultad {
        $sql = "SELECT IdFacultad, Nombre, Abreviatura FROM facultad WHERE IdFacultad = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return new Facultad(
            (int)$row['IdFacultad'],
            $row['Nombre'],
            $row['Abreviatura']
        );
    }
}
