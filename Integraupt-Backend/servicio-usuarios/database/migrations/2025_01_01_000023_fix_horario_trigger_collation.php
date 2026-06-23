<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS crear_reserva_automatica;
            CREATE PROCEDURE crear_reserva_automatica(
                IN p_usuario INT,
                IN p_espacio INT,
                IN p_bloque INT,
                IN p_curso INT,
                IN p_fechaReserva DATE,
                IN p_descripcion TEXT,
                IN p_cantidadEstudiantes INT
            )
            BEGIN
                DECLARE v_dia_semana VARCHAR(10) COLLATE utf8mb4_unicode_ci;
                DECLARE v_horario_ocupado INT DEFAULT 0;
                DECLARE v_reserva_existente INT DEFAULT 0;

                SET v_dia_semana = CASE DAYOFWEEK(p_fechaReserva)
                    WHEN 2 THEN 'Lunes'
                    WHEN 3 THEN 'Martes'
                    WHEN 4 THEN 'Miercoles'
                    WHEN 5 THEN 'Jueves'
                    WHEN 6 THEN 'Viernes'
                    WHEN 7 THEN 'Sabado'
                    ELSE 'Lunes'
                END;

                SELECT COUNT(*) INTO v_horario_ocupado
                FROM horarios
                WHERE espacio = p_espacio
                AND bloque = p_bloque
                AND diaSemana = v_dia_semana
                AND ocupado = 1;

                SELECT COUNT(*) INTO v_reserva_existente
                FROM reserva
                WHERE espacio = p_espacio
                AND bloque = p_bloque
                AND fechaReserva = p_fechaReserva
                AND Estado = 'Aprobada';

                IF v_horario_ocupado = 0 AND v_reserva_existente = 0 THEN
                    INSERT INTO reserva (usuario, espacio, bloque, curso, fechaReserva, DescripcionUso, CantidadEstudiantes, Estado)
                    VALUES (p_usuario, p_espacio, p_bloque, p_curso, p_fechaReserva, p_descripcion, p_cantidadEstudiantes, 'Pendiente');

                    SELECT LAST_INSERT_ID() AS IdReserva, 'Reserva creada exitosamente' AS Mensaje;
                ELSE
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El horario seleccionado no está disponible';
                END IF;
            END;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_actualizar_horario_update;
            CREATE TRIGGER trg_actualizar_horario_update AFTER UPDATE ON reserva FOR EACH ROW
            BEGIN
                DECLARE dia ENUM('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado') COLLATE utf8mb4_unicode_ci;

                SET dia = CASE DAYOFWEEK(NEW.fechaReserva)
                    WHEN 2 THEN 'Lunes'
                    WHEN 3 THEN 'Martes'
                    WHEN 4 THEN 'Miercoles'
                    WHEN 5 THEN 'Jueves'
                    WHEN 6 THEN 'Viernes'
                    WHEN 7 THEN 'Sabado'
                    ELSE 'Lunes'
                END;

                IF NEW.Estado = 'Aprobada' THEN
                    UPDATE horarios h
                    SET h.ocupado = 1
                    WHERE h.espacio = NEW.espacio
                      AND h.bloque = NEW.bloque
                      AND h.diaSemana = dia;
                ELSEIF NEW.Estado IN ('Rechazada','Cancelada') AND OLD.Estado = 'Aprobada' THEN
                    UPDATE horarios h
                    SET h.ocupado = 0
                    WHERE h.espacio = NEW.espacio
                      AND h.bloque = NEW.bloque
                      AND h.diaSemana = dia;
                END IF;
            END;
        ");
    }

    public function down(): void
    {
        // Forward-only fix: original (broken-collation) definitions live in
        // 2025_01_01_000021_create_triggers_procedures_events.php's down() teardown.
    }
};
