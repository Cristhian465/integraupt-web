<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Procedimientos Almacenados
        DB::unprepared("
            DROP PROCEDURE IF EXISTS aprobar_reserva;
            CREATE PROCEDURE aprobar_reserva(IN p_IdReserva INT)
            BEGIN
                DECLARE v_usuario INT;
                DECLARE v_espacio INT;
                DECLARE v_bloque INT;
                DECLARE v_fecha DATE;
                DECLARE v_rol INT;
                DECLARE v_motivo_rechazo VARCHAR(255);
                DECLARE v_existen_otros_estudiantes INT DEFAULT 0;

                SELECT r.usuario, r.espacio, r.bloque, r.fechaReserva, u.Rol 
                INTO v_usuario, v_espacio, v_bloque, v_fecha, v_rol
                FROM reserva r
                JOIN usuario u ON r.usuario = u.IdUsuario
                WHERE r.IdReserva = p_IdReserva;

                INSERT INTO reserva_gestion (IdReserva, UsuarioGestion, Accion, Motivo)
                VALUES (p_IdReserva, v_usuario, 'Aprobar', 'Reserva aprobada mediante procedimiento');

                IF v_rol = 1 THEN
                    SET v_motivo_rechazo = 'un docente reservo prioritario';
                    
                    INSERT INTO reserva_gestion (IdReserva, UsuarioGestion, Accion, Motivo)
                    SELECT r.IdReserva, v_usuario, 'Rechazar', v_motivo_rechazo
                    FROM reserva r
                    WHERE r.espacio = v_espacio
                      AND r.fechaReserva = v_fecha
                      AND r.bloque = v_bloque
                      AND r.Estado = 'Pendiente'
                      AND r.IdReserva != p_IdReserva;

                ELSEIF v_rol = 3 THEN
                    SET v_motivo_rechazo = 'prioridad auditoria';
                    
                    INSERT INTO reserva_gestion (IdReserva, UsuarioGestion, Accion, Motivo)
                    SELECT r.IdReserva, v_usuario, 'Rechazar', v_motivo_rechazo
                    FROM reserva r
                    WHERE r.espacio = v_espacio
                      AND r.fechaReserva = v_fecha
                      AND r.bloque = v_bloque
                      AND r.Estado = 'Pendiente'
                      AND r.IdReserva != p_IdReserva;

                ELSEIF v_rol = 2 THEN
                    SELECT COUNT(*) INTO v_existen_otros_estudiantes
                    FROM reserva r
                    JOIN usuario u ON r.usuario = u.IdUsuario
                    WHERE r.espacio = v_espacio
                      AND r.fechaReserva = v_fecha
                      AND r.bloque = v_bloque
                      AND r.Estado = 'Pendiente'
                      AND u.Rol = 2
                      AND r.IdReserva != p_IdReserva;

                    IF v_existen_otros_estudiantes > 0 THEN
                        SET v_motivo_rechazo = 'estudiante reservo primero';
                        
                        INSERT INTO reserva_gestion (IdReserva, UsuarioGestion, Accion, Motivo)
                        SELECT r.IdReserva, v_usuario, 'Rechazar', v_motivo_rechazo
                        FROM reserva r
                        JOIN usuario u ON r.usuario = u.IdUsuario
                        WHERE r.espacio = v_espacio
                          AND r.fechaReserva = v_fecha
                          AND r.bloque = v_bloque
                          AND r.Estado = 'Pendiente'
                          AND u.Rol = 2
                          AND r.IdReserva != p_IdReserva;
                    END IF;
                END IF;
            END;
        ");

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

        // Eventos
        DB::unprepared("
            DROP EVENT IF EXISTS actualizar_sanciones_cumplidas;
            CREATE EVENT actualizar_sanciones_cumplidas 
            ON SCHEDULE EVERY 1 DAY STARTS '2025-11-01 00:00:00' 
            ON COMPLETION PRESERVE ENABLE DO 
            BEGIN
                UPDATE sancion 
                SET Estado = 'Cumplida' 
                WHERE Estado = 'Activa' 
                AND FechaFin < CURDATE();
            END;
        ");

        DB::unprepared("
            DROP EVENT IF EXISTS aplicar_horarios_fijos;
            CREATE EVENT aplicar_horarios_fijos 
            ON SCHEDULE EVERY 1 DAY STARTS '2025-11-01 00:00:00' 
            ON COMPLETION PRESERVE ENABLE DO 
            BEGIN
                UPDATE horarios h
                JOIN horario_curso hc ON h.espacio = hc.Espacio
                                       AND h.bloque = hc.Bloque
                                       AND h.diaSemana = hc.DiaSemana
                SET h.ocupado = 1
                WHERE CURDATE() BETWEEN hc.FechaInicio AND hc.FechaFin
                  AND hc.Estado = 1;
            END;
        ");

        DB::unprepared("
            DROP EVENT IF EXISTS liberar_horarios_fijos;
            CREATE EVENT liberar_horarios_fijos 
            ON SCHEDULE EVERY 1 DAY STARTS '2025-11-01 00:00:00' 
            ON COMPLETION PRESERVE ENABLE DO 
            BEGIN
                UPDATE horario_curso
                SET Estado = 0
                WHERE FechaFin < CURDATE();
                
                UPDATE horarios h
                JOIN horario_curso hc ON h.espacio = hc.Espacio
                                       AND h.bloque = hc.Bloque
                                       AND h.diaSemana = hc.DiaSemana
                SET h.ocupado = 0
                WHERE hc.FechaFin < CURDATE()
                  AND h.ocupado = 1;
            END;
        ");

        DB::unprepared("
            DROP EVENT IF EXISTS resetear_bloqueos;
            CREATE EVENT resetear_bloqueos 
            ON SCHEDULE EVERY 1 HOUR STARTS '2025-11-01 00:00:00' 
            ON COMPLETION PRESERVE ENABLE DO 
            BEGIN
                UPDATE usuario_auth 
                SET IntentosFallidos = 0, 
                    BloqueadoHasta = NULL 
                WHERE BloqueadoHasta IS NOT NULL 
                AND BloqueadoHasta < NOW();
                
                UPDATE usuario_auth 
                SET TokenRecuperacion = NULL, 
                    TokenExpiracion = NULL 
                WHERE TokenExpiracion IS NOT NULL 
                AND TokenExpiracion < NOW();
            END;
        ");

        DB::unprepared("
            DROP EVENT IF EXISTS reset_horarios_domingo;
            CREATE EVENT reset_horarios_domingo 
            ON SCHEDULE EVERY 1 WEEK STARTS '2025-11-02 00:00:00' 
            ON COMPLETION PRESERVE ENABLE DO 
            BEGIN
                UPDATE horarios h
                JOIN reserva r ON h.espacio = r.espacio AND h.bloque = r.bloque
                SET h.ocupado = 0
                WHERE r.fechaReserva < CURDATE();
            END;
        ");

        // Triggers
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_actualizar_estado_sancion;
            CREATE TRIGGER trg_actualizar_estado_sancion BEFORE UPDATE ON sancion FOR EACH ROW 
            BEGIN
                IF CURDATE() > NEW.FechaFin AND NEW.Estado = 'Activa' THEN
                    SET NEW.Estado = 'Cumplida';
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

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_auditoria_reserva;
            CREATE TRIGGER trg_auditoria_reserva AFTER UPDATE ON reserva FOR EACH ROW 
            BEGIN
                DECLARE usuario_gestion INT;
                
                IF OLD.Estado <> NEW.Estado THEN
                    SELECT rg.UsuarioGestion INTO usuario_gestion
                    FROM reserva_gestion rg
                    WHERE rg.IdReserva = NEW.IdReserva
                    ORDER BY rg.FechaGestion DESC
                    LIMIT 1;
                    
                    SET usuario_gestion = COALESCE(usuario_gestion, NEW.usuario);
                    
                    INSERT INTO auditoriareserva (IdReserva, EstadoAnterior, EstadoNuevo, UsuarioCambio)
                    VALUES (NEW.IdReserva, OLD.Estado, NEW.Estado, usuario_gestion);
                END IF;
            END;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_bloquear_horario_curso_insert;
            CREATE TRIGGER trg_bloquear_horario_curso_insert AFTER INSERT ON horario_curso FOR EACH ROW 
            BEGIN
                IF CURDATE() BETWEEN NEW.FechaInicio AND NEW.FechaFin AND NEW.Estado = 1 THEN
                    UPDATE horarios 
                    SET ocupado = 1 
                    WHERE espacio = NEW.Espacio 
                      AND bloque = NEW.Bloque 
                      AND diaSemana = NEW.DiaSemana;
                END IF;
            END;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_bloquear_horario_curso_update;
            CREATE TRIGGER trg_bloquear_horario_curso_update AFTER UPDATE ON horario_curso FOR EACH ROW 
            BEGIN
                IF (OLD.Estado = 1 AND NEW.Estado = 0) OR 
                   (OLD.Estado = 1 AND (NEW.Espacio != OLD.Espacio OR NEW.Bloque != OLD.Bloque OR NEW.DiaSemana != OLD.DiaSemana)) OR
                   (OLD.Estado = 1 AND CURDATE() NOT BETWEEN NEW.FechaInicio AND NEW.FechaFin) THEN
                    
                    UPDATE horarios 
                    SET ocupado = 0 
                    WHERE espacio = OLD.Espacio 
                      AND bloque = OLD.Bloque 
                      AND diaSemana = OLD.DiaSemana;
                END IF;
                
                IF NEW.Estado = 1 AND CURDATE() BETWEEN NEW.FechaInicio AND NEW.FechaFin THEN
                    UPDATE horarios 
                    SET ocupado = 1 
                    WHERE espacio = NEW.Espacio 
                      AND bloque = NEW.Bloque 
                      AND diaSemana = NEW.DiaSemana;
                END IF;
            END;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_crear_horarios;
            CREATE TRIGGER trg_crear_horarios AFTER INSERT ON bloqueshorarios FOR EACH ROW 
            BEGIN
                INSERT INTO horarios (espacio, bloque, diaSemana, ocupado)
                SELECT e.IdEspacio, NEW.IdBloque, d.dia, 0
                FROM espacio e
                CROSS JOIN (
                    SELECT 'Lunes' AS dia
                    UNION ALL SELECT 'Martes'
                    UNION ALL SELECT 'Miercoles'
                    UNION ALL SELECT 'Jueves'
                    UNION ALL SELECT 'Viernes'
                    UNION ALL SELECT 'Sabado'
                ) d;
            END;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_crear_horarios_espacios;
            CREATE TRIGGER trg_crear_horarios_espacios AFTER INSERT ON espacio FOR EACH ROW 
            BEGIN
                INSERT INTO horarios (espacio, bloque, diaSemana, ocupado)
                SELECT NEW.IdEspacio, b.IdBloque, d.dia, 0
                FROM bloqueshorarios b
                CROSS JOIN (
                    SELECT 'Lunes' AS dia UNION SELECT 'Martes' UNION SELECT 'Miercoles'
                    UNION SELECT 'Jueves' UNION SELECT 'Viernes' UNION SELECT 'Sabado'
                ) d;
            END;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_eliminar_horarios_bloque;
            CREATE TRIGGER trg_eliminar_horarios_bloque AFTER DELETE ON bloqueshorarios FOR EACH ROW 
            BEGIN
                DELETE FROM horarios 
                WHERE bloque = OLD.IdBloque;
            END;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_eliminar_horarios_espacio;
            CREATE TRIGGER trg_eliminar_horarios_espacio AFTER DELETE ON espacio FOR EACH ROW 
            BEGIN
                DELETE FROM horarios 
                WHERE espacio = OLD.IdEspacio;
            END;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_liberar_horario_curso_delete;
            CREATE TRIGGER trg_liberar_horario_curso_delete AFTER DELETE ON horario_curso FOR EACH ROW 
            BEGIN
                UPDATE horarios 
                SET ocupado = 0 
                WHERE espacio = OLD.Espacio 
                  AND bloque = OLD.Bloque 
                  AND diaSemana = OLD.DiaSemana;
            END;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_sincronizar_estado_gestion;
            CREATE TRIGGER trg_sincronizar_estado_gestion AFTER INSERT ON reserva_gestion FOR EACH ROW 
            BEGIN
                DECLARE nuevo_estado VARCHAR(20);
                
                CASE NEW.Accion
                    WHEN 'Aprobar' THEN SET nuevo_estado = 'Aprobada';
                    WHEN 'Rechazar' THEN SET nuevo_estado = 'Rechazada';
                    ELSE SET nuevo_estado = 'Pendiente';
                END CASE;
                
                UPDATE reserva 
                SET Estado = nuevo_estado 
                WHERE IdReserva = NEW.IdReserva;
            END;
        ");

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_verificar_sanciones_activas;
            CREATE TRIGGER trg_verificar_sanciones_activas BEFORE INSERT ON reserva FOR EACH ROW 
            BEGIN
                DECLARE sancion_activa INT DEFAULT 0;
                
                SELECT COUNT(*) INTO sancion_activa
                FROM sancion 
                WHERE Usuario = NEW.usuario 
                AND Estado = 'Activa'
                AND CURDATE() BETWEEN FechaInicio AND FechaFin;
                
                IF sancion_activa > 0 THEN
                    SIGNAL SQLSTATE '45000' 
                    SET MESSAGE_TEXT = 'El usuario tiene una sanción activa y no puede realizar reservas';
                END IF;
            END;
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS aprobar_reserva;");
        DB::unprepared("DROP PROCEDURE IF EXISTS crear_reserva_automatica;");
        
        DB::unprepared("DROP EVENT IF EXISTS actualizar_sanciones_cumplidas;");
        DB::unprepared("DROP EVENT IF EXISTS aplicar_horarios_fijos;");
        DB::unprepared("DROP EVENT IF EXISTS liberar_horarios_fijos;");
        DB::unprepared("DROP EVENT IF EXISTS resetear_bloqueos;");
        DB::unprepared("DROP EVENT IF EXISTS reset_horarios_domingo;");
        
        DB::unprepared("DROP TRIGGER IF EXISTS trg_actualizar_estado_sancion;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_actualizar_horario_update;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_auditoria_reserva;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_bloquear_horario_curso_insert;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_bloquear_horario_curso_update;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_crear_horarios;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_crear_horarios_espacios;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_eliminar_horarios_bloque;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_eliminar_horarios_espacio;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_liberar_horario_curso_delete;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_sincronizar_estado_gestion;");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_verificar_sanciones_activas;");
    }
};
