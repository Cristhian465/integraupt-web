<?php
require_once __DIR__ . '/../config/Database.php';

class EspacioController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function processRequest($method, $id, $uri, $apiIndex) {
        if ($method === 'GET') {
            if ($id) {
                $action = isset($uri[$apiIndex + 3]) ? $uri[$apiIndex + 3] : null;
                if ($action === 'cursos') {
                    $this->listarCursosPorEspacio($id);
                } elseif ($action === 'bloques') {
                    $this->listarBloquesPorEspacio($id);
                }
            } else {
                $incluirInactivos = isset($_GET['incluirInactivos']) ? filter_var($_GET['incluirInactivos'], FILTER_VALIDATE_BOOLEAN) : false;
                $escuelaId = isset($_GET['escuelaId']) ? $_GET['escuelaId'] : null;
                $this->listarEspacios($incluirInactivos, $escuelaId);
            }
        }
    }

    private function listarEspacios($incluirInactivos, $escuelaId) {
        $query = "SELECT * FROM espacio";
        $conditions = [];
        if (!$incluirInactivos) {
            $conditions[] = "activo = 1";
        }
        if ($escuelaId) {
            $conditions[] = "escuela_id = :escuela_id";
        }
        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->db->prepare($query);
        if ($escuelaId) {
            $stmt->bindParam(':escuela_id', $escuelaId);
        }
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function listarCursosPorEspacio($espacioId) {
        // Asumiendo relación espacio-curso
        $query = "SELECT c.id, c.nombre, c.ciclo FROM curso c INNER JOIN espacio_curso ec ON c.id = ec.curso_id WHERE ec.espacio_id = :espacio_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':espacio_id', $espacioId);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function listarBloquesPorEspacio($espacioId) {
        $query = "SELECT * FROM bloque_horario WHERE espacio_id = :espacio_id ORDER BY orden";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':espacio_id', $espacioId);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
?>
