<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\ResultadoService;
use App\Services\InscripcionService;
use App\Models\OlimpiadaEdicion;
use App\Models\OlimpiadaDisciplina;
use App\Models\OlimpiadaEdicionDisciplina;
use App\Models\OlimpiadaInscripcion;
use App\Models\OlimpiadaResultado;
use App\Models\OlimpiadaParticipacionFacultad;
use App\Models\Usuario;
use App\Models\Estudiante;
use App\Models\Facultad;
use App\Models\Escuela;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OlimpiadasScoringTest extends TestCase
{
    use RefreshDatabase;

    protected $resultadoService;
    protected $inscripcionService;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations from servicio-usuarios
        $this->artisan('migrate', [
            '--path' => '../servicio-usuarios/database/migrations',
            '--realpath' => true
        ]);

        $this->resultadoService = new ResultadoService();
        $this->inscripcionService = new InscripcionService();
    }

    public function test_volleyball_and_basketball_scoring(): void
    {
        // 1. Get a basketball discipline
        $disciplina = OlimpiadaDisciplina::where('Nombre', 'Básquetbol')->first();

        $edicion = OlimpiadaEdicion::create([
            'Nombre' => 'Olimpiadas 2026',
            'AnioInicio' => 2026,
            'SemestreInicio' => 1,
            'Estado' => 'inscripcion_abierta'
        ]);

        $vinculo = OlimpiadaEdicionDisciplina::create([
            'Edicion' => $edicion->IdEdicion,
            'Disciplina' => $disciplina->IdDisciplina,
            'Estado' => 'activa'
        ]);

        // Create faculties
        $fac1 = Facultad::create(['Nombre' => 'Ingeniería', 'Abreviatura' => 'FAING', 'Color' => 'Granate']);
        $fac2 = Facultad::create(['Nombre' => 'Ciencias', 'Abreviatura' => 'FACEM', 'Color' => 'Verde']);

        // Register users and subscribe them to generate team participation entries
        $user1 = Usuario::create(['Nombre' => 'Juan', 'Apellido' => 'Perez', 'TipoDoc' => 1, 'Rol' => 2, 'Estado' => 1]);
        $user2 = Usuario::create(['Nombre' => 'Maria', 'Apellido' => 'Gomez', 'TipoDoc' => 1, 'Rol' => 2, 'Estado' => 1]);
        
        $escuela = Escuela::create(['Nombre' => 'Sistemas', 'Abreviatura' => 'ESIS', 'IdFacultad' => $fac1->IdFacultad]);
        $escuela2 = Escuela::create(['Nombre' => 'Comercio', 'Abreviatura' => 'EICO', 'IdFacultad' => $fac2->IdFacultad]);
        
        Estudiante::create(['IdUsuario' => $user1->IdUsuario, 'Codigo' => '2020-119001', 'Escuela' => $escuela->IdEscuela]);
        Estudiante::create(['IdUsuario' => $user2->IdUsuario, 'Codigo' => '2020-119002', 'Escuela' => $escuela2->IdEscuela]);

        OlimpiadaInscripcion::create(['EdicionDisciplina' => $vinculo->IdEdicionDisciplina, 'Usuario' => $user1->IdUsuario, 'Facultad' => $fac1->IdFacultad, 'Estado' => 'inscrito']);
        OlimpiadaInscripcion::create(['EdicionDisciplina' => $vinculo->IdEdicionDisciplina, 'Usuario' => $user2->IdUsuario, 'Facultad' => $fac2->IdFacultad, 'Estado' => 'inscrito']);

        // Case A: Played match. Local (fac1) wins 80-75.
        $res1 = OlimpiadaResultado::create([
            'EdicionDisciplina' => $vinculo->IdEdicionDisciplina,
            'FacultadLocal' => $fac1->IdFacultad,
            'FacultadVisitante' => $fac2->IdFacultad,
            'PuntajeLocal' => 80,
            'PuntajeVisitante' => 75,
            'Estado' => 'finalizado'
        ]);

        $this->resultadoService->recalcularTabla($vinculo->IdEdicionDisciplina);

        $partLocal = OlimpiadaParticipacionFacultad::where('EdicionDisciplina', $vinculo->IdEdicionDisciplina)->where('Facultad', $fac1->IdFacultad)->first();
        $partVisit = OlimpiadaParticipacionFacultad::where('EdicionDisciplina', $vinculo->IdEdicionDisciplina)->where('Facultad', $fac2->IdFacultad)->first();

        $this->assertEquals(2, $partLocal->Puntos); // Win is 2 pts
        $this->assertEquals(1, $partVisit->Puntos); // Loss is 1 pt
        $this->assertEquals(1, $partLocal->PartidosGanados);
        $this->assertEquals(1, $partVisit->PartidosPerdidos);

        // Case B: W.O. match. Guest doesn't show up. Local wins 30-0 (W.O. Basketball).
        $res1->delete(); // Clear previous result
        $res2 = OlimpiadaResultado::create([
            'EdicionDisciplina' => $vinculo->IdEdicionDisciplina,
            'FacultadLocal' => $fac1->IdFacultad,
            'FacultadVisitante' => $fac2->IdFacultad,
            'PuntajeLocal' => 30,
            'PuntajeVisitante' => 0,
            'Estado' => 'finalizado',
            'Observaciones' => 'Ganado por W.O. del equipo visitante'
        ]);

        $this->resultadoService->recalcularTabla($vinculo->IdEdicionDisciplina);

        $partLocal = OlimpiadaParticipacionFacultad::where('EdicionDisciplina', $vinculo->IdEdicionDisciplina)->where('Facultad', $fac1->IdFacultad)->first();
        $partVisit = OlimpiadaParticipacionFacultad::where('EdicionDisciplina', $vinculo->IdEdicionDisciplina)->where('Facultad', $fac2->IdFacultad)->first();

        $this->assertEquals(2, $partLocal->Puntos); // Win is 2 pts
        $this->assertEquals(0, $partVisit->Puntos); // Loss by W.O. is 0 pts
    }

    public function test_football_scoring(): void
    {
        // 1. Get a football discipline
        $disciplina = OlimpiadaDisciplina::where('Nombre', 'Fútbol')->first();

        $edicion = OlimpiadaEdicion::create([
            'Nombre' => 'Olimpiadas 2026',
            'AnioInicio' => 2026,
            'SemestreInicio' => 1,
            'Estado' => 'inscripcion_abierta'
        ]);

        $vinculo = OlimpiadaEdicionDisciplina::create([
            'Edicion' => $edicion->IdEdicion,
            'Disciplina' => $disciplina->IdDisciplina,
            'Estado' => 'activa'
        ]);

        $fac1 = Facultad::create(['Nombre' => 'Ingeniería', 'Abreviatura' => 'FAING', 'Color' => 'Granate']);
        $fac2 = Facultad::create(['Nombre' => 'Ciencias', 'Abreviatura' => 'FACEM', 'Color' => 'Verde']);

        $user1 = Usuario::create(['Nombre' => 'Juan', 'Apellido' => 'Perez', 'TipoDoc' => 1, 'Rol' => 2, 'Estado' => 1]);
        $user2 = Usuario::create(['Nombre' => 'Maria', 'Apellido' => 'Gomez', 'TipoDoc' => 1, 'Rol' => 2, 'Estado' => 1]);
        
        $escuela = Escuela::create(['Nombre' => 'Sistemas', 'Abreviatura' => 'ESIS', 'IdFacultad' => $fac1->IdFacultad]);
        $escuela2 = Escuela::create(['Nombre' => 'Comercio', 'Abreviatura' => 'EICO', 'IdFacultad' => $fac2->IdFacultad]);
        
        Estudiante::create(['IdUsuario' => $user1->IdUsuario, 'Codigo' => '2020-119001', 'Escuela' => $escuela->IdEscuela]);
        Estudiante::create(['IdUsuario' => $user2->IdUsuario, 'Codigo' => '2020-119002', 'Escuela' => $escuela2->IdEscuela]);

        OlimpiadaInscripcion::create(['EdicionDisciplina' => $vinculo->IdEdicionDisciplina, 'Usuario' => $user1->IdUsuario, 'Facultad' => $fac1->IdFacultad, 'Estado' => 'inscrito']);
        OlimpiadaInscripcion::create(['EdicionDisciplina' => $vinculo->IdEdicionDisciplina, 'Usuario' => $user2->IdUsuario, 'Facultad' => $fac2->IdFacultad, 'Estado' => 'inscrito']);

        // Case A: Played match. 1-1 Draw.
        $res1 = OlimpiadaResultado::create([
            'EdicionDisciplina' => $vinculo->IdEdicionDisciplina,
            'FacultadLocal' => $fac1->IdFacultad,
            'FacultadVisitante' => $fac2->IdFacultad,
            'PuntajeLocal' => 1,
            'PuntajeVisitante' => 1,
            'Estado' => 'finalizado'
        ]);

        $this->resultadoService->recalcularTabla($vinculo->IdEdicionDisciplina);

        $partLocal = OlimpiadaParticipacionFacultad::where('EdicionDisciplina', $vinculo->IdEdicionDisciplina)->where('Facultad', $fac1->IdFacultad)->first();
        $partVisit = OlimpiadaParticipacionFacultad::where('EdicionDisciplina', $vinculo->IdEdicionDisciplina)->where('Facultad', $fac2->IdFacultad)->first();

        $this->assertEquals(1, $partLocal->Puntos);
        $this->assertEquals(1, $partVisit->Puntos);

        // Case B: W.O. match. 3-0 in favor of local.
        $res1->delete();
        $res2 = OlimpiadaResultado::create([
            'EdicionDisciplina' => $vinculo->IdEdicionDisciplina,
            'FacultadLocal' => $fac1->IdFacultad,
            'FacultadVisitante' => $fac2->IdFacultad,
            'PuntajeLocal' => 3,
            'PuntajeVisitante' => 0,
            'Estado' => 'finalizado',
            'Observaciones' => 'Ganador local por WO'
        ]);

        $this->resultadoService->recalcularTabla($vinculo->IdEdicionDisciplina);

        $partLocal = OlimpiadaParticipacionFacultad::where('EdicionDisciplina', $vinculo->IdEdicionDisciplina)->where('Facultad', $fac1->IdFacultad)->first();
        $partVisit = OlimpiadaParticipacionFacultad::where('EdicionDisciplina', $vinculo->IdEdicionDisciplina)->where('Facultad', $fac2->IdFacultad)->first();

        $this->assertEquals(3, $partLocal->Puntos);
        $this->assertEquals(-1, $partVisit->Puntos); // -1 point for W.O. loss in Fútbol
    }

    public function test_enrollment_restrictions(): void
    {
        $edicion = OlimpiadaEdicion::create(['Nombre' => 'Olimp 2026', 'AnioInicio' => 2026, 'SemestreInicio' => 1, 'Estado' => 'inscripcion_abierta']);
        
        $disciplinaColectivo1 = OlimpiadaDisciplina::where('Nombre', 'Fútbol')->first();
        $disciplinaColectivo2 = OlimpiadaDisciplina::where('Nombre', 'Básquetbol')->first();
        $disciplinaColectivo3 = OlimpiadaDisciplina::where('Nombre', 'Vóleibol')->first();
        
        $vinculo1 = OlimpiadaEdicionDisciplina::create(['Edicion' => $edicion->IdEdicion, 'Disciplina' => $disciplinaColectivo1->IdDisciplina, 'Estado' => 'activa']);
        $vinculo2 = OlimpiadaEdicionDisciplina::create(['Edicion' => $edicion->IdEdicion, 'Disciplina' => $disciplinaColectivo2->IdDisciplina, 'Estado' => 'activa']);
        $vinculo3 = OlimpiadaEdicionDisciplina::create(['Edicion' => $edicion->IdEdicion, 'Disciplina' => $disciplinaColectivo3->IdDisciplina, 'Estado' => 'activa']);

        $fac = Facultad::create(['Nombre' => 'Ingeniería', 'Abreviatura' => 'FAING', 'Color' => 'Granate']);
        $escuela = Escuela::create(['Nombre' => 'Sistemas', 'Abreviatura' => 'ESIS', 'IdFacultad' => $fac->IdFacultad]);
        
        $user = Usuario::create(['Nombre' => 'Pedro', 'Apellido' => 'Ramirez', 'TipoDoc' => 1, 'Rol' => 2, 'Estado' => 1]);
        Estudiante::create(['IdUsuario' => $user->IdUsuario, 'Codigo' => '2020-119003', 'Escuela' => $escuela->IdEscuela]);

        // Enrolling in first 2 collective sports should succeed
        $this->inscripcionService->inscribir(['edicionDisciplinaId' => $vinculo1->IdEdicionDisciplina, 'usuarioId' => $user->IdUsuario]);
        $this->inscripcionService->inscribir(['edicionDisciplinaId' => $vinculo2->IdEdicionDisciplina, 'usuarioId' => $user->IdUsuario]);

        // Enrolling in the 3rd should fail
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No puedes inscribirte en más de 2 deportes colectivos.');
        $this->inscripcionService->inscribir(['edicionDisciplinaId' => $vinculo3->IdEdicionDisciplina, 'usuarioId' => $user->IdUsuario]);
    }
}
