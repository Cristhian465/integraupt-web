<?php
require_once __DIR__ . '/../config/Database.php';

class CatalogoController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function processRequest($method, $id, $uri, $apiIndex) {
        if ($method === 'GET') {
            $resource = isset($uri[$apiIndex + 2]) ? $uri[$apiIndex + 2] : null;
            if ($resource === 'facultades') {
                $this->listarFacultades();
            } elseif ($resource === 'escuelas') {
                $facultadId = isset($_GET['facultadId']) ? $_GET['facultadId'] : null;
                $this->listarEscuelas($facultadId);
            }
        }
    }

    private function listarFacultades() {
        $query = "SELECT * FROM facultad";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function listarEscuelas($facultadId) {
        $query = "SELECT e.*, f.nombre as facultad_nombre FROM escuela e LEFT JOIN facultad f ON e.facultad_id = f.id";
        if ($facultadId) {
            $query .= " WHERE e.facultad_id = :facultad_id";
        }
        $stmt = $this->db->prepare($query);
        if ($facultadId) {
            $stmt->bindParam(':facultad_id', $facultadId);
        }
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
?>
