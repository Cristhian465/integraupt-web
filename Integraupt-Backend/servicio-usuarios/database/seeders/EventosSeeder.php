<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventosSeeder extends Seeder
{
    public function run(): void
    {
        DB::unprepared("
            INSERT INTO `evento` (`IdEvento`, `Titulo`, `Descripcion`, `TipoEvento`, `Alcance`, `IdFacultad`, `IdEscuela`, `IdEspacio`, `FechaInicio`, `FechaFin`, `AforoMaximo`, `RequiereInscripcion`, `Estado`, `IdResponsable`) VALUES
                (1, 'Feria Tecnológica 2026', 'Presentación de proyectos innovadores', 'Feria', 'Facultad', 1, 2, 1, '2026-08-15 09:00:00', '2026-08-15 18:00:00', 100, 1, 'publicado', 14),
                (2, 'Seminario de Inteligencia Artificial', 'Tendencias actuales y futuro de la IA', 'Seminario', 'Universidad', 1, NULL, 2, '2026-09-10 10:00:00', '2026-09-10 12:00:00', 50, 1, 'publicado', 16);
        ");

        DB::unprepared("
            INSERT INTO `evento_inscripcion` (`IdInscripcion`, `IdEvento`, `IdUsuario`, `TipoUsuario`, `Estado`, `CodigoQr`) VALUES
                (1, 1, 14, 'estudiante', 'inscrito', 'QR-EV1-US14'),
                (2, 1, 17, 'estudiante', 'inscrito', 'QR-EV1-US17'),
                (3, 2, 18, 'estudiante', 'inscrito', 'QR-EV2-US18');
        ");
    }
}
