<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Administrativo;
use PDO;

class AdministrativoRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findByUsuarioId(int $usuarioId): ?Administrativo {
        $sql = "SELECT IdAdministrativo, IdUsuario, Escuela FROM administrativo WHERE IdUsuario = :usuarioId LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuarioId' => $usuarioId]);
        
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return new Administrativo(
            (int)$row['IdAdministrativo'],
            (int)$row['IdUsuario'],
            $row['Escuela'] !== null ? (int)$row['Escuela'] : null
        );
    }
}
