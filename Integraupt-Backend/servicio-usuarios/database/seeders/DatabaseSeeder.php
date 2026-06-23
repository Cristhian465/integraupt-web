<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::unprepared("
            INSERT INTO `rol` (`IdRol`, `Nombre`) VALUES
                (1, 'Profesor'),
                (2, 'Estudiante'),
                (3, 'Administrador'),
                (4, 'Supervisor');
        ");

        DB::unprepared("
            INSERT INTO `tipodocumento` (`IdTipoDoc`, `Nombre`, `Abreviatura`) VALUES
                (1, 'Documento Nacional de Identidad', 'DNI'),
                (2, 'Carnet de Extranjería', 'CE'),
                (3, 'Pasaporte', 'PAS'),
                (4, 'Permiso Temporal de Permanencia', 'PTP'),
                (5, 'Cédula de Identidad', 'CI'),
                (6, 'Registro Único de Contribuyente', 'RUC'),
                (7, 'Partida de Nacimiento', 'PN'),
                (8, 'Carnet de Refugiado', 'CR'),
                (9, 'Documento de Identidad Extranjero', 'DIE'),
                (10, 'Licencia de Conducir', 'LIC'),
                (11, 'Carnet Universitario', 'CU'),
                (12, 'Otro', 'OTRO');
        ");

        DB::unprepared("
            INSERT INTO `facultad` (`IdFacultad`, `Nombre`, `Abreviatura`) VALUES
                (1, 'Facultad de Ingeniería', 'FAING'),
                (2, 'Facultad de Derecho y Ciencias Políticas', 'FADE'),
                (3, 'Facultad de Ciencias Empresariales', 'FACEM'),
                (4, 'Facultad de Educación, Ciencias de la Comunicación', 'FAEDCOH'),
                (5, 'Facultad de Ciencias De la Salud', 'FACSA'),
                (6, 'Facultad de Arquitectura y Urbanismo', 'FAU');
        ");

        DB::unprepared("
            INSERT INTO `escuela` (`IdEscuela`, `IdFacultad`, `Nombre`) VALUES
                (1, 1, 'Ing. Civil'),
                (2, 1, 'Ing. de Sistemas'),
                (3, 1, 'Ing. Electronica'),
                (4, 1, 'Ing. Agroindustrial'),
                (5, 1, 'Ing. Ambiental'),
                (6, 1, 'Ing. Industrial'),
                (7, 2, 'Derecho'),
                (8, 3, 'Ciencias Contables y Financieras'),
                (9, 3, 'Economia y Microfinanzas'),
                (10, 3, 'Administracion'),
                (11, 3, 'Administracion Turistico-Hotel'),
                (12, 3, 'Administracion de Negocios Internacionales'),
                (13, 4, 'Educacion'),
                (14, 4, 'Ciencias de la Comunicacion'),
                (15, 4, 'Humanidades - Psicologia'),
                (16, 5, 'Medicina Humana'),
                (17, 5, 'Odontologia'),
                (18, 5, 'Tecnologia Medica'),
                (19, 6, 'Arquitectira');
        ");

        DB::unprepared("
            INSERT INTO `bloqueshorarios` (`IdBloque`, `Orden`, `Nombre`, `HoraInicio`, `HoraFinal`) VALUES
                (1, 1, 'B1', '08:00:00', '08:50:00'),
                (2, 2, 'B2', '08:50:00', '09:40:00'),
                (3, 3, 'B3', '09:40:00', '10:30:00'),
                (4, 4, 'B4', '10:30:00', '11:20:00'),
                (5, 5, 'B5', '11:20:00', '12:10:00'),
                (6, 6, 'B6', '12:10:00', '13:00:00'),
                (7, 7, 'B7', '13:00:00', '13:50:00'),
                (8, 8, 'B8', '13:50:00', '14:10:00'),
                (9, 9, 'B9', '14:10:00', '15:00:00'),
                (10, 10, 'B10', '15:00:00', '15:50:00'),
                (11, 11, 'B11', '15:50:00', '16:40:00'),
                (12, 12, 'B12', '16:40:00', '17:30:00'),
                (13, 13, 'B13', '17:30:00', '18:20:00'),
                (14, 14, 'B14', '18:20:00', '19:10:00'),
                (15, 15, 'B15', '19:10:00', '20:00:00'),
                (16, 16, 'B16', '20:00:00', '20:50:00'),
                (17, 17, 'B17', '20:50:00', '21:40:00');
        ");

        DB::unprepared("
            INSERT INTO `cursos` (`IdCurso`, `Nombre`, `Facultad`, `Escuela`, `Ciclo`, `Estado`) VALUES
                (1, 'POGRAMACION', 1, 2, '5', 1),
                (2, 'CIVIL', 1, 1, '2', 1);
        ");

        DB::unprepared("
            INSERT INTO `espacio` (`IdEspacio`, `Codigo`, `Nombre`, `Tipo`, `Capacidad`, `Equipamiento`, `Escuela`, `Estado`) VALUES
                (1, 'P-302', 'LAB 02', 'Laboratorio', 40, '1', 2, 1),
                (2, 'S-630', 'LAB 15', 'Laboratorio', 20, 'A', 1, 1),
                (9, 'P-035', 'G', 'Laboratorio', 20, 'Computadoras', 2, 1);
        ");

        DB::unprepared("
            INSERT INTO `usuario` (`IdUsuario`, `Nombre`, `Apellido`, `TipoDoc`, `NumDoc`, `Rol`, `Celular`, `Genero`, `Estado`, `FechaRegistro`) VALUES
                (14, 'IKER', 'SIERRA', 1, '99887766', 2, '987654321', 1, 1, '2025-11-09 12:11:58'),
                (15, 'pablo', 'Ramirez', 2, '872346812', 3, '762394581', 0, 1, '2025-11-09 12:15:04'),
                (16, 'dayan', 'quispe', 4, '72489213412', 1, '1236498124', 1, 1, '2025-11-09 15:28:53'),
                (17, 'juan', 'perez', 1, '1682346981', 2, '2614894323', 1, 1, '2025-11-09 15:38:59'),
                (18, 'Stevie', 'Marca', 1, '72405382', 2, '979739029', 1, 1, '2025-11-10 20:32:10'),
                (19, 'Nicol', 'Carol', 1, '72405385', 4, '98888', 1, 1, '2025-11-10 20:57:55'),
                (20, 'Stevie', 'Marca', 1, '72405388', 3, '979739029', NULL, 1, '2025-11-26 19:25:03');
        ");

        DB::unprepared("
            INSERT INTO `usuario_auth` (`IdAuth`, `IdUsuario`, `CorreoU`, `Password`, `UltimoLogin`, `SesionToken`, `SesionExpira`, `SesionTipo`) VALUES
                (6, 14, 'ike@upt.pe', 'MTIzNDU2', '2025-11-12 19:11:57', NULL, NULL, NULL),
                (7, 15, 'pa@upt.pe', 'MTIzNDU2', '2025-11-27 11:59:07', NULL, NULL, NULL),
                (8, 16, 'dn@upt.pe', 'MTIzNDU2', '2025-11-26 19:33:58', NULL, NULL, NULL),
                (9, 17, 'ju@upt.pe', 'MTIzNDU2', NULL, NULL, NULL, NULL),
                (10, 18, 'sm@upt.pe', 'MTIzNDU2', '2025-11-27 11:59:57', 'f0feacd6-9e02-4978-87a9-139127faf431', '2025-11-27 12:19:57', 'academic'),
                (11, 19, 'Nc@upt.pe', 'MTIzNDU2', '2025-11-19 16:17:18', NULL, NULL, NULL),
                (12, 20, 'StevieMarca@upt.pe', 'MTIzNDU2', '2025-11-26 19:25:19', NULL, NULL, NULL);
        ");

        DB::unprepared("
            INSERT INTO `estudiante` (`IdEstudiante`, `IdUsuario`, `Codigo`, `Escuela`) VALUES
                (1, 14, '2023088', 2),
                (2, 18, '2023076802', 2),
                (3, 19, '223075555', 1);
        ");

        DB::unprepared("
            INSERT INTO `administrativo` (`IdAdministrativo`, `IdUsuario`, `Escuela`, `Turno`, `Extension`, `FechaIncorporacion`) VALUES
                (1, 15, NULL, 'Completo', '4440', '2025-11-19'),
                (2, 19, 2, 'Tarde', '4455', '2025-11-12'),
                (3, 20, NULL, 'Completo', '44405', '2025-11-26');
        ");

        DB::unprepared("
            INSERT INTO `docente` (`IdDocente`, `IdUsuario`, `CodigoDocente`, `Escuela`, `TipoContrato`, `Especialidad`, `FechaIncorporacion`) VALUES
                (1, 16, '202307', 2, 'Tiempo Completo', 'nose', '2025-11-09');
        ");
    }
}
