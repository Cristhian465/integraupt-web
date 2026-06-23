<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class EspacioRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * @return string[]
     */
    public function obtenerTiposDeEspacio(): array {
        $sql = "SELECT DISTINCT Tipo FROM espacio ORDER BY Tipo";
        $stmt = $this->db->query($sql);
        
        $tipos = [];
        while ($row = $stmt->fetch()) {
            if ($row['Tipo'] !== null && trim($row['Tipo']) !== '') {
                $tipos[] = $row['Tipo'];
            }
        }
        
        return $tipos;
    }
}
