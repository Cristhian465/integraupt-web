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
        $query = "SELECT estado, COUNT(*) as cantidad FROM reserva WHERE usuario_id = :usuario_id GROUP BY estado";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }

    private function crearReserva() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $query = "INSERT INTO reserva (usuario_id, espacio_id, bloque_id, curso_id, fecha_reserva, fecha_solicitud, descripcion_uso, cantidad_estudiantes, estado) 
                  VALUES (:usuario_id, :espacio_id, :bloque_id, :curso_id, :fecha_reserva, NOW(), :descripcion_uso, :cantidad_estudiantes, 'PENDIENTE')";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':usuario_id', $data['usuarioId']);
        $stmt->bindParam(':espacio_id', $data['espacioId']);
        $stmt->bindParam(':bloque_id', $data['bloqueId']);
        $stmt->bindParam(':curso_id', $data['cursoId']);
        $stmt->bindParam(':fecha_reserva', $data['fechaReserva']);
        $stmt->bindParam(':descripcion_uso', $data['descripcionUso']);
        $stmt->bindParam(':cantidad_estudiantes', $data['cantidadEstudiantes']);
        
        if ($stmt->execute()) {
            $id = $this->db->lastInsertId();
            http_response_code(201);
            echo json_encode(['id' => $id, 'message' => 'Reserva creada']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Error al crear la reserva']);
        }
    }

    private function obtenerQrReserva($id) {
        // Implementar QR logic
        echo json_encode(['qr' => 'base64_data']);
    }

    private function actualizarReserva($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $query = "UPDATE reserva SET 
                  usuario_id = COALESCE(:usuario_id, usuario_id), 
                  espacio_id = COALESCE(:espacio_id, espacio_id), 
                  bloque_id = COALESCE(:bloque_id, bloque_id), 
                  curso_id = COALESCE(:curso_id, curso_id), 
                  fecha_reserva = COALESCE(:fecha_reserva, fecha_reserva), 
                  descripcion_uso = COALESCE(:descripcion_uso, descripcion_uso), 
                  cantidad_estudiantes = COALESCE(:cantidad_estudiantes, cantidad_estudiantes), 
                  estado = COALESCE(:estado, estado) 
                  WHERE id = :id";
                  
        $stmt = $this->db->prepare($query);
        
        $usuario_id = isset($data['usuarioId']) ? $data['usuarioId'] : null;
        $espacio_id = isset($data['espacioId']) ? $data['espacioId'] : null;
        $bloque_id = isset($data['bloqueId']) ? $data['bloqueId'] : null;
        $curso_id = isset($data['cursoId']) ? $data['cursoId'] : null;
        $fecha_reserva = isset($data['fechaReserva']) ? $data['fechaReserva'] : null;
        $descripcion_uso = isset($data['descripcionUso']) ? $data['descripcionUso'] : null;
        $cantidad_estudiantes = isset($data['cantidadEstudiantes']) ? $data['cantidadEstudiantes'] : null;
        $estado = isset($data['estado']) ? $data['estado'] : null;
        
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':espacio_id', $espacio_id);
        $stmt->bindParam(':bloque_id', $bloque_id);
        $stmt->bindParam(':curso_id', $curso_id);
        $stmt->bindParam(':fecha_reserva', $fecha_reserva);
        $stmt->bindParam(':descripcion_uso', $descripcion_uso);
        $stmt->bindParam(':cantidad_estudiantes', $cantidad_estudiantes);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Reserva actualizada']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Error al actualizar la reserva']);
        }
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
