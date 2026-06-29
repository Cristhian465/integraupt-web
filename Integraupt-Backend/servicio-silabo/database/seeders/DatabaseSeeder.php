<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Silabo;
use App\Models\SilaboUnidad;
use App\Models\SilaboTema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $silabo = Silabo::create([
            'CodigoCurso' => 'SI-101',
            'NombreCurso' => 'Ingeniería de Software I',
            'CicloNumero' => 'VII',
            'Semestre' => '2026-I',
            'Horas' => 4,
            'Creditos' => 4,
            'Docente' => 'JUAN ALBERTO RODRIGUEZ PAZ',
            'HorarioCursoId' => 1,
            'DiasXSemana' => 2,
            'Estado' => 'aprobado'
        ]);

        $unidad = SilaboUnidad::create([
            'SilaboId' => $silabo->IdSilabo,
            'Numero' => 1,
            'Titulo' => 'Introducción a la Ingeniería de Software',
            'Semanas' => 4
        ]);

        SilaboTema::create([
            'UnidadId' => $unidad->IdUnidad,
            'Semana' => 1,
            'ContenidoConceptual' => 'Conceptos básicos de la Ingeniería de Software.',
            'ContenidoProcedimental' => 'Identificación de problemas y levantamiento de requerimientos.',
            'ContenidoActitudinal' => 'Participación activa.'
        ]);

        SilaboTema::create([
            'UnidadId' => $unidad->IdUnidad,
            'Semana' => 2,
            'ContenidoConceptual' => 'Modelos de ciclo de vida del software.',
            'ContenidoProcedimental' => 'Comparación entre modelos tradicionales y ágiles.',
            'ContenidoActitudinal' => 'Análisis crítico.'
        ]);
    }
}
