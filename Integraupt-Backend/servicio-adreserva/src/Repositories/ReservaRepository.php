<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Reserva;
use App\DTO\AdminReservaCardDto;
use PDO;

class ReservaRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?Reserva {
        $sql = "SELECT IdReserva, usuario, espacio, bloque, curso, fechaReserva, fechaSolicitud, DescripcionUso, CantidadEstudiantes, Estado 
                FROM reserva WHERE IdReserva = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return new Reserva(
            (int)$row['IdReserva'],
            (int)$row['usuario'],
            (int)$row['espacio'],
            (int)$row['bloque'],
            (int)$row['curso'],
            $row['fechaReserva'],
            $row['fechaSolicitud'],
            $row['DescripcionUso'],
            (int)$row['CantidadEstudiantes'],
            $row['Estado']
        );
    }

    public function save(Reserva $reserva): void {
        if ($reserva->id === null) {
            $sql = "INSERT INTO reserva (usuario, espacio, bloque, curso, fechaReserva, fechaSolicitud, DescripcionUso, CantidadEstudiantes, Estado) 
                    VALUES (:usuarioId, :espacioId, :bloqueId, :cursoId, :fechaReserva, :fechaSolicitud, :descripcionUso, :cantidadEstudiantes, :estado)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'usuarioId' => $reserva->usuarioId,
                'espacioId' => $reserva->espacioId,
                'bloqueId' => $reserva->bloqueId,
                'cursoId' => $reserva->cursoId,
                'fechaReserva' => $reserva->fechaReserva,
                'fechaSolicitud' => $reserva->fechaSolicitud,
                'descripcionUso' => $reserva->descripcionUso,
                'cantidadEstudiantes' => $reserva->cantidadEstudiantes,
                'estado' => $reserva->estado
            ]);
            
            $reserva->id = (int)$this->db->lastInsertId();
        } else {
            $sql = "UPDATE reserva 
                    SET usuario = :usuarioId, 
                        espacio = :espacioId, 
                        bloque = :bloqueId, 
                        curso = :cursoId, 
                        fechaReserva = :fechaReserva, 
                        fechaSolicitud = :fechaSolicitud, 
                        DescripcionUso = :descripcionUso, 
                        CantidadEstudiantes = :cantidadEstudiantes, 
                        Estado = :estado 
                    WHERE IdReserva = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $reserva->id,
                'usuarioId' => $reserva->usuarioId,
                'espacioId' => $reserva->espacioId,
                'bloqueId' => $reserva->bloqueId,
                'cursoId' => $reserva->cursoId,
                'fechaReserva' => $reserva->fechaReserva,
                'fechaSolicitud' => $reserva->fechaSolicitud,
                'descripcionUso' => $reserva->descripcionUso,
                'cantidadEstudiantes' => $reserva->cantidadEstudiantes,
                'estado' => $reserva->estado
            ]);
        }
    }

    /**
     * @return AdminReservaCardDto[]
     */
    public function buscarReservasParaAdmin(
        ?string $estado,
        ?string $tipoEspacio,
        ?int $facultadId,
        ?int $escuelaId,
        ?string $fecha,
        ?string $search
    ): array {
        $sql = "
            SELECT
                r.IdReserva AS reserva_id,
                r.Estado AS estado,
                r.fechaReserva AS fecha_reserva,
                r.fechaSolicitud AS fecha_solicitud,
                r.DescripcionUso AS descripcion_uso,
                r.CantidadEstudiantes AS cantidad_estudiantes,
                e.Codigo AS espacio_codigo,
                e.Nombre AS espacio_nombre,
                e.Tipo AS espacio_tipo,
                esc.IdEscuela AS escuela_id,
                esc.Nombre AS escuela_nombre,
                fac.IdFacultad AS facultad_id,
                fac.Nombre AS facultad_nombre,
                bh.Nombre AS bloque_nombre,
                bh.HoraInicio AS hora_inicio,
                bh.HoraFinal AS hora_fin,
                c.Nombre AS curso_nombre,
                u.IdUsuario AS solicitante_id,
                u.Nombre AS solicitante_nombre,
                u.Apellido AS solicitante_apellido,
                ua.CorreoU AS solicitante_correo,
                rg.Accion AS ultima_accion,
                rg.Motivo AS motivo,
                rg.Comentarios AS comentarios,
                rg.FechaGestion AS fecha_gestion,
                ug.IdUsuario AS gestor_id,
                ug.Nombre AS gestor_nombre,
                ug.Apellido AS gestor_apellido
            FROM reserva r
            INNER JOIN usuario u ON u.IdUsuario = r.usuario
            LEFT JOIN usuario_auth ua ON ua.IdUsuario = u.IdUsuario
            INNER JOIN espacio e ON e.IdEspacio = r.espacio
            INNER JOIN escuela esc ON esc.IdEscuela = e.Escuela
            INNER JOIN facultad fac ON fac.IdFacultad = esc.IdFacultad
            INNER JOIN bloqueshorarios bh ON bh.IdBloque = r.bloque
            INNER JOIN cursos c ON c.IdCurso = r.curso
            LEFT JOIN (
                SELECT g1.*
                FROM reserva_gestion g1
                INNER JOIN (
                    SELECT IdReserva, MAX(FechaGestion) AS FechaGestion
                    FROM reserva_gestion
                    GROUP BY IdReserva
                ) g2 ON g1.IdReserva = g2.IdReserva AND g1.FechaGestion = g2.FechaGestion
            ) rg ON rg.IdReserva = r.IdReserva
            LEFT JOIN usuario ug ON ug.IdUsuario = rg.UsuarioGestion
        ";

        $conditions = [];
        $params = [];

        if ($estado !== null) {
            $conditions[] = "r.Estado = :estado";
            $params['estado'] = $estado;
        }
        if ($tipoEspacio !== null) {
            $conditions[] = "e.Tipo = :tipoEspacio";
            $params['tipoEspacio'] = $tipoEspacio;
        }
        if ($facultadId !== null) {
            $conditions[] = "fac.IdFacultad = :facultadId";
            $params['facultadId'] = $facultadId;
        }
        if ($escuelaId !== null) {
            $conditions[] = "esc.IdEscuela = :escuelaId";
            $params['escuelaId'] = $escuelaId;
        }
        if ($fecha !== null) {
            $conditions[] = "r.fechaReserva = :fechaReserva";
            $params['fechaReserva'] = $fecha;
        }
        if ($search !== null) {
            $conditions[] = "(LOWER(e.Nombre) LIKE :search OR LOWER(e.Codigo) LIKE :search OR LOWER(CONCAT(u.Nombre, ' ', u.Apellido)) LIKE :search)";
            $params['search'] = '%' . strtolower($search) . '%';
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY r.fechaSolicitud DESC, r.IdReserva DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $reservas = [];
        while ($row = $stmt->fetch()) {
            $reservas[] = $this->mapRowToCardDto($row);
        }

        return $reservas;
    }

    public function buscarReservaPorId(int $id): ?AdminReservaCardDto {
        $sql = "
            SELECT
                r.IdReserva AS reserva_id,
                r.Estado AS estado,
                r.fechaReserva AS fecha_reserva,
                r.fechaSolicitud AS fecha_solicitud,
                r.DescripcionUso AS descripcion_uso,
                r.CantidadEstudiantes AS cantidad_estudiantes,
                e.Codigo AS espacio_codigo,
                e.Nombre AS espacio_nombre,
                e.Tipo AS espacio_tipo,
                esc.IdEscuela AS escuela_id,
                esc.Nombre AS escuela_nombre,
                fac.IdFacultad AS facultad_id,
                fac.Nombre AS facultad_nombre,
                bh.Nombre AS bloque_nombre,
                bh.HoraInicio AS hora_inicio,
                bh.HoraFinal AS hora_fin,
                c.Nombre AS curso_nombre,
                u.IdUsuario AS solicitante_id,
                u.Nombre AS solicitante_nombre,
                u.Apellido AS solicitante_apellido,
                ua.CorreoU AS solicitante_correo,
                rg.Accion AS ultima_accion,
                rg.Motivo AS motivo,
                rg.Comentarios AS comentarios,
                rg.FechaGestion AS fecha_gestion,
                ug.IdUsuario AS gestor_id,
                ug.Nombre AS gestor_nombre,
                ug.Apellido AS gestor_apellido
            FROM reserva r
            INNER JOIN usuario u ON u.IdUsuario = r.usuario
            LEFT JOIN usuario_auth ua ON ua.IdUsuario = u.IdUsuario
            INNER JOIN espacio e ON e.IdEspacio = r.espacio
            INNER JOIN escuela esc ON esc.IdEscuela = e.Escuela
            INNER JOIN facultad fac ON fac.IdFacultad = esc.IdFacultad
            INNER JOIN bloqueshorarios bh ON bh.IdBloque = r.bloque
            INNER JOIN cursos c ON c.IdCurso = r.curso
            LEFT JOIN (
                SELECT g1.*
                FROM reserva_gestion g1
                INNER JOIN (
                    SELECT IdReserva, MAX(FechaGestion) AS FechaGestion
                    FROM reserva_gestion
                    GROUP BY IdReserva
                ) g2 ON g1.IdReserva = g2.IdReserva AND g1.FechaGestion = g2.FechaGestion
            ) rg ON rg.IdReserva = r.IdReserva
            LEFT JOIN usuario ug ON ug.IdUsuario = rg.UsuarioGestion
            WHERE r.IdReserva = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return $this->mapRowToCardDto($row);
    }

    /**
     * @return array Array asociativo con 'estado' => 'total'
     */
    public function obtenerResumenEstados(
        ?string $tipoEspacio,
        ?int $facultadId,
        ?int $escuelaId,
        ?string $fecha,
        ?string $search
    ): array {
        $sql = "
            SELECT
                r.Estado AS estado,
                COUNT(*) AS total
            FROM reserva r
            INNER JOIN espacio e ON e.IdEspacio = r.espacio
            INNER JOIN escuela esc ON esc.IdEscuela = e.Escuela
            INNER JOIN facultad fac ON fac.IdFacultad = esc.IdFacultad
        ";

        $conditions = [];
        $params = [];

        if ($tipoEspacio !== null) {
            $conditions[] = "e.Tipo = :tipoEspacio";
            $params['tipoEspacio'] = $tipoEspacio;
        }
        if ($facultadId !== null) {
            $conditions[] = "fac.IdFacultad = :facultadId";
            $params['facultadId'] = $facultadId;
        }
        if ($escuelaId !== null) {
            $conditions[] = "esc.IdEscuela = :escuelaId";
            $params['escuelaId'] = $escuelaId;
        }
        if ($fecha !== null) {
            $conditions[] = "r.fechaReserva = :fechaReserva";
            $params['fechaReserva'] = $fecha;
        }
        if ($search !== null) {
            $conditions[] = "(LOWER(e.Nombre) LIKE :search OR LOWER(e.Codigo) LIKE :search OR LOWER((SELECT CONCAT(u.Nombre, ' ', u.Apellido) FROM usuario u WHERE u.IdUsuario = r.usuario)) LIKE :search)";
            $params['search'] = '%' . strtolower($search) . '%';
        }

        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " GROUP BY r.Estado";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $resumen = [];
        while ($row = $stmt->fetch()) {
            $resumen[$row['estado']] = (int)$row['total'];
        }

        return $resumen;
    }

    private function mapRowToCardDto(array $row): AdminReservaCardDto {
        return new AdminReservaCardDto(
            (int)$row['reserva_id'],
            $row['estado'],
            $row['fecha_reserva'],
            $row['fecha_solicitud'],
            $row['descripcion_uso'],
            $row['cantidad_estudiantes'] !== null ? (int)$row['cantidad_estudiantes'] : null,
            $row['espacio_codigo'],
            $row['espacio_nombre'],
            $row['espacio_tipo'],
            $row['escuela_id'] !== null ? (int)$row['escuela_id'] : null,
            $row['escuela_nombre'],
            $row['facultad_id'] !== null ? (int)$row['facultad_id'] : null,
            $row['facultad_nombre'],
            $row['bloque_nombre'],
            $row['hora_inicio'],
            $row['hora_fin'],
            $row['curso_nombre'],
            $row['solicitante_id'] !== null ? (int)$row['solicitante_id'] : null,
            $row['solicitante_nombre'],
            $row['solicitante_apellido'],
            $row['solicitante_correo'],
            $row['ultima_accion'],
            $row['motivo'],
            $row['comentarios'],
            $row['fecha_gestion'],
            $row['gestor_id'] !== null ? (int)$row['gestor_id'] : null,
            $row['gestor_nombre'],
            $row['gestor_apellido']
        );
    }
}
