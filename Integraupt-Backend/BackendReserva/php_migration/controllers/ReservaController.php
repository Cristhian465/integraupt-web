<?php
require_once __DIR__ . '/../config/Database.php';

class ReservaController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function processRequest($method, $id, $uri, $apiIndex) {
        if ($method === 'GET') {
            if ($id) {
                if ($id === 'usuario') {
                    $usuarioId = isset($uri[$apiIndex + 3]) ? $uri[$apiIndex + 3] : null;
                    $action = isset($uri[$apiIndex + 4]) ? $uri[$apiIndex + 4] : null;
                    if ($action === 'resumen') {
                        $this->obtenerResumenReservasUsuario($usuarioId);
                    } else {
                        $this->obtenerReservasPorUsuario($usuarioId);
                    }
                } else {
                    $action = isset($uri[$apiIndex + 3]) ? $uri[$apiIndex + 3] : null;
                    if ($action === 'qr') {
                        $this->obtenerQrReserva($id);
                    } else {
                        $this->obtenerReserva($id);
                    }
                }
            } else {
                $this->listarReservas();
            }
        } elseif ($method === 'POST') {
            $this->crearReserva();
        } elseif ($method === 'PUT') {
            if ($id) {
                $this->actualizarReserva($id);
            }
        } elseif ($method === 'DELETE') {
            if ($id) {
                $this->eliminarReserva($id);
            }
        }
    }

    private function listarReservas() {
        $query = "SELECT * FROM reserva";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }

    private function obtenerReserva($id) {
        $query = "SELECT * FROM reserva WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Not found']);
        }
    }

    private function obtenerReservasPorUsuario($usuarioId) {
        $query = "SELECT r.*, e.nombre as espacio_nombre, b.hora_inicio as bloque_hora_inicio, b.hora_final as bloque_hora_fin 
                  FROM reserva r 
                  LEFT JOIN espacio e ON r.espacio_id = e.id 
                  LEFT JOIN bloque_horario b ON r.bloque_id = b.id 
                  WHERE r.usuario_id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }

    private function obtenerResumenReservasUsuario($usuarioId) {
        // Implementar lógica de resumen
        echo json_encode([]);
    }

    private function crearReserva() {
        $data = json_decode(file_get_contents("php://input"), true);
        // Implementar inserción
        http_response_code(201);
        echo json_encode(['message' => 'Reserva creada']);
    }

    private function obtenerQrReserva($id) {
        // Implementar QR logic
        echo json_encode(['qr' => 'base64_data']);
    }

    private function actualizarReserva($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        // Implementar actualización
        echo json_encode(['message' => 'Reserva actualizada']);
    }

    private function eliminarReserva($id) {
        $query = "DELETE FROM reserva WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            http_response_code(204);
        } else {
            http_response_code(500);
        }
    }
}
?>
