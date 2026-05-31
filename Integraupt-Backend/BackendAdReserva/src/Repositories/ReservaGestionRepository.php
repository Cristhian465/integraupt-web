<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\ReservaGestion;
use PDO;

class ReservaGestionRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function save(ReservaGestion $gestion): void {
        if ($gestion->id === null) {
            $sql = "INSERT INTO reserva_gestion (IdReserva, UsuarioGestion, FechaGestion, Accion, Motivo, Comentarios) 
                    VALUES (:reservaId, :usuarioGestionId, :fechaGestion, :accion, :motivo, :comentarios)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'reservaId' => $gestion->reservaId,
                'usuarioGestionId' => $gestion->usuarioGestionId,
                'fechaGestion' => $gestion->fechaGestion,
                'accion' => $gestion->accion,
                'motivo' => $gestion->motivo,
                'comentarios' => $gestion->comentarios
            ]);
            
            $gestion->id = (int)$this->db->lastInsertId();
        } else {
            $sql = "UPDATE reserva_gestion 
                    SET IdReserva = :reservaId, 
                        UsuarioGestion = :usuarioGestionId, 
                        FechaGestion = :fechaGestion, 
                        Accion = :accion, 
                        Motivo = :motivo, 
                        Comentarios = :comentarios 
                    WHERE IdGestion = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $gestion->id,
                'reservaId' => $gestion->reservaId,
                'usuarioGestionId' => $gestion->usuarioGestionId,
                'fechaGestion' => $gestion->fechaGestion,
                'accion' => $gestion->accion,
                'motivo' => $gestion->motivo,
                'comentarios' => $gestion->comentarios
            ]);
        }
    }
}
