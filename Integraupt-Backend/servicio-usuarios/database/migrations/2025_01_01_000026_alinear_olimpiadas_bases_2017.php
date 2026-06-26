<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('olimpiada_disciplina', 'TipoPuntuacion')) {
            Schema::table('olimpiada_disciplina', function (Blueprint $table) {
                $table->enum('TipoPuntuacion', ['partido', 'posiciones'])->default('partido')->after('TipoParticipacion');
            });
        }

        if (!Schema::hasColumn('olimpiada_edicion_disciplina', 'Lugar')) {
            Schema::table('olimpiada_edicion_disciplina', function (Blueprint $table) {
                $table->string('Lugar', 150)->nullable()->after('ReglasEspecificas');
            });
        }

        if (!Schema::hasColumn('olimpiada_resultado', 'Lugar')) {
            Schema::table('olimpiada_resultado', function (Blueprint $table) {
                $table->string('Lugar', 150)->nullable()->after('FechaPartido');
            });
        }

        if (!Schema::hasTable('olimpiada_resultado_posicion')) Schema::create('olimpiada_resultado_posicion', function (Blueprint $table) {
            $table->bigIncrements('IdResultadoPosicion');
            $table->unsignedInteger('EdicionDisciplina');
            $table->unsignedInteger('Facultad');
            $table->unsignedInteger('Posicion');
            $table->unsignedInteger('Puntos')->default(0);
            $table->string('Prueba', 100)->nullable();
            $table->date('Fecha')->nullable();
            $table->string('Lugar', 150)->nullable();
            $table->text('Observaciones')->nullable();
            $table->enum('Estado', ['registrado', 'cancelado'])->default('registrado');
            $table->dateTime('FechaRegistro')->nullable()->useCurrent();

            $table->foreign('EdicionDisciplina')->references('IdEdicionDisciplina')->on('olimpiada_edicion_disciplina')->onDelete('cascade');
            $table->foreign('Facultad')->references('IdFacultad')->on('facultad');
            $table->unique(['EdicionDisciplina', 'Facultad', 'Prueba'], 'olimp_resultado_posicion_unique');
        });

        // ===== Corrección de las 6 disciplinas ya sembradas, con datos reales de las
        // Bases Deportivas de los XVII Juegos Deportivos Inter Facultades UPT 2017 =====

        DB::table('olimpiada_disciplina')->where('Nombre', 'Fútbol')->update([
            'TipoParticipacion' => 'equipo',
            'TipoPuntuacion' => 'partido',
            'CupoMaximoDefault' => 18,
            'Descripcion' => 'Fútbol damas y varones. Partidos dirigidos por árbitros oficiales, reglas FIFA con modificaciones propias del torneo.',
            'Reglas' => "Mínimo 07 jugadores inscritos para iniciar el partido; máximo 4 cambios por equipo.\n"
                . "Duración: 35' x 35' (40' x 40' en semifinal y final), 5' de descanso entre tiempos.\n"
                . "Tolerancia: 10' para el primer encuentro, 5' para los siguientes.\n"
                . "Empate en semifinal/final se define por penales (5 por equipo, alternados), sin tiempo suplementario.\n"
                . "Puntaje: Ganador 3 pts, Empate 1 pt, Perdedor 0 pts, W.O. -1 pt (rival gana 3-0).\n"
                . "Sanciones: suplantación de jugador = W.O.; tarjeta roja no directa = 1 fecha; 2 tarjetas amarillas = 1 fecha de suspensión; agresión física o verbal = expulsión del campeonato.",
        ]);

        DB::table('olimpiada_disciplina')->where('Nombre', 'Vóleibol')->update([
            'TipoParticipacion' => 'equipo',
            'TipoPuntuacion' => 'partido',
            'CupoMaximoDefault' => 12,
            'Descripcion' => 'Vóley femenino y masculino. Se juega con las reglas vigentes de la FIVB, sistema Rally Point.',
            'Reglas' => "Planilla de juego: máximo 12 jugadores, mínimo 6 por partido.\n"
                . "Fase eliminatoria: 2 sets ganados de 20 puntos (tercer set a 15 en caso de empate 1-1).\n"
                . "Semifinal y final: 2 sets ganados de 25 puntos (tercer set a 15).\n"
                . "Puntaje: Partido ganado 2 pts, perdido 1 pt, W.O. 0 pts (rival gana 2-0 con parciales 25-0).\n"
                . "Jugador 'Líbero' opcional, con camiseta y número distintos.",
        ]);

        DB::table('olimpiada_disciplina')->where('Nombre', 'Básquetbol')->update([
            'TipoParticipacion' => 'equipo',
            'TipoPuntuacion' => 'partido',
            'CupoMaximoDefault' => 12,
            'Descripcion' => 'Baloncesto damas y varones. Arbitraje FIBA designado por la Comisión Técnica.',
            'Reglas' => "Puntaje: Partido ganado 2 pts, perdido 1 pt, W.O. 0 pts (rival gana con score 30-0).\n"
                . "Tolerancia: 10' en el primer encuentro, 5' entre encuentros.\n"
                . "Fase eliminatoria: 2 tiempos de 15' (12' corridos + 3' finales de tiempo efectivo), 1 tiempo técnico por equipo y periodo.\n"
                . "Semifinal y final: tiempo efectivo reglamentario.\n"
                . "Expulsión del campo de juego: sanción automática de 1 fecha (o más según gravedad).",
        ]);

        DB::table('olimpiada_disciplina')->where('Nombre', 'Ajedrez')->update([
            'TipoParticipacion' => 'equipo',
            'TipoPuntuacion' => 'partido',
            'CupoMaximoDefault' => 4,
            'Descripcion' => 'Ajedrez por equipos de facultad (4 tableros). Se rige por las normas de la FIDE.',
            'Reglas' => "Cada equipo: 4 jugadores inscritos en orden fijo, ocupando los tableros 1 a 4.\n"
                . "Puntaje por partida: ganada 1 pt, empatada 0.5 pts, perdida 0 pts. El puntaje del match es la suma de las 4 partidas.\n"
                . "Tolerancia de 10' para iniciar el match; mínimo 2 jugadores presentes para no perder por W.O.; 2 W.O. = eliminación del equipo.\n"
                . "Desempate en etapa eliminatoria: Sonnenborn-Berger, resultado entre empatados, partidas ganadas, partidas ganadas con negras, sorteo.\n"
                . "Desempate en semifinal/final: nuevo match a 15' por jugador (rápido); si persiste, 10' (blitz); si persiste, partida única a muerte súbita (blancas 6' vs negras 5'; tablas favorece a negras).",
        ]);

        DB::table('olimpiada_disciplina')->where('Nombre', 'Atletismo')->update([
            'TipoParticipacion' => 'individual',
            'TipoPuntuacion' => 'posiciones',
            'CupoMaximoDefault' => null,
            'Descripcion' => 'Atletismo damas y varones (pistas, postas y campo). Se rige por el Reglamento Internacional de Atletismo (IAAF).',
            'Reglas' => "Pruebas: 100m, 200m, 400m, 800m, 1500m (damas) / 3000m (varones), postas 4x100m y 4x400m, salto alto, salto largo, triple, lanzamiento de bala/disco/jabalina.\n"
                . "Máximo 2 participantes por facultad en pruebas individuales; 1 equipo por facultad en cada posta.\n"
                . "Un atleta puede correr hasta 4 pruebas individuales más una colectiva (posta).\n"
                . "Puntaje individual (8 mejores marcas): 10, 8, 6, 5, 4, 3, 2, 1. En postas, el doble: 20, 16, 12, 10, 8, 6, 4, 2.\n"
                . "El campeón general se determina por la suma de puntos obtenidos en todas las pruebas.",
        ]);

        DB::table('olimpiada_disciplina')->where('Nombre', 'Tenis de Mesa')->update([
            'TipoParticipacion' => 'individual',
            'TipoPuntuacion' => 'posiciones',
            'CupoMaximoDefault' => 6,
            'Descripcion' => 'Tenis de mesa: dobles mixtos, individual damas e individual varones. Se rige por el Reglamento ITTF.',
            'Reglas' => "Cada facultad inscribe hasta 3 damas y 3 varones (máximo 6 deportistas).\n"
                . "Dobles mixtos: hasta 2 parejas por facultad, formadas por jugadores ya inscritos en individuales; sistema de eliminación simple.\n"
                . "Individuales: sistema de series + llave de eliminación simple.\n"
                . "Todas las modalidades se juegan al mejor de 3 sets.\n"
                . "Puntaje por puesto (igual en las 3 modalidades): 1° 20, 2° 15, 3° 12, 4° 10, 5° 9, 6° 8 puntos. El puntaje final de la facultad es la suma de lo obtenido en las 3 modalidades.\n"
                . "Tolerancia máxima de 5' sobre el horario; vencido el plazo se resuelve por W.O.",
        ]);

        // ===== Disciplinas adicionales de las Bases 2017 que faltaban en el catálogo =====

        DB::table('olimpiada_disciplina')->insertOrIgnore([
            [
                'Nombre' => 'Futsal',
                'Descripcion' => 'Futsal damas y varones.',
                'TipoParticipacion' => 'equipo',
                'TipoPuntuacion' => 'partido',
                'CupoMaximoDefault' => 10,
                'Estado' => 'activa',
                'Reglas' => "Planilla de juego: hasta 10 jugadores; el equipo titular son 5 (uno arquero); cambios ilimitados por la zona de sustituciones.\n"
                    . "Mínimo 5 jugadores para iniciar, de lo contrario W.O.\n"
                    . "Tarjeta roja directa: sale del partido. Tarjeta amarilla: sale 3 minutos.\n"
                    . "Fase eliminatoria: tiempo corrido 18' x 18'. Semifinal y final: tiempo efectivo 25' x 25', ambos con 5' de descanso.\n"
                    . "Empate en semifinal/final: penales alternados (3 iniciales, luego se continúa alternando hasta desempatar).\n"
                    . "Puntaje: Ganador 3 pts, Empate 1 pt, Perdedor 0 pts, W.O. -1 pt (rival gana 3-0).\n"
                    . "Sanciones: suplantación de jugador = eliminación de la disciplina; tarjeta roja directa = 2 fechas; agresión física o verbal = expulsión del campeonato.",
            ],
            [
                'Nombre' => 'Drill Gimnástico / Gimnasia Aerobia',
                'Descripcion' => 'Gimnasia aerobia / drill gimnástico, calificada por jurado.',
                'TipoParticipacion' => 'equipo',
                'TipoPuntuacion' => 'posiciones',
                'CupoMaximoDefault' => 20,
                'Estado' => 'activa',
                'Reglas' => "Equipo de 15 a 20 participantes. Rutina musicalizada de 6' a 8' (tolerancia +/- 15-20 segundos).\n"
                    . "Ingreso al escenario dentro de los 30 segundos de ser llamado (penalidad 0.5 pts si demora más; W.O. si supera 60 segundos).\n"
                    . "Calificación de un jurado de 3 jueces sobre 100 puntos: Presentación 20, Desplazamiento 20, Coordinación y ritmo 20, Pirámides 20, Coreografía 20.\n"
                    . "Se registra la posición y el puntaje obtenido por cada facultad participante.",
            ],
            [
                'Nombre' => 'Juegos de Competencia',
                'Descripcion' => 'Circuito de 5 juegos recreativo-competitivos definidos por la comisión organizadora el día del evento.',
                'TipoParticipacion' => 'equipo',
                'TipoPuntuacion' => 'posiciones',
                'CupoMaximoDefault' => 10,
                'Estado' => 'activa',
                'Reglas' => "Cada facultad inscribe 10 estudiantes (5 damas, 5 varones); admite hasta 4 inscritos adicionales como 'inscripción de emergencia' si faltan titulares.\n"
                    . "Se desarrollan 5 juegos elegidos de una lista (fuerza en la soga, carrera de chasquis, carretilla humana, carrera de caballos, carrera de orientación, el gusano humano, revienta globos, asignación de tareas), anunciados 10 minutos antes de cada competencia.\n"
                    . "Puntaje por puesto: 1° 5 pts, 2° 4 pts, 3° 3 pts, 4° 2 pts, 5° 1 pt, 6° 0 pts. El puntaje final de la facultad es la suma de los 5 juegos.",
            ],
            [
                'Nombre' => 'Tenis de Campo',
                'Descripcion' => 'Tenis de campo (singles) damas y varones.',
                'TipoParticipacion' => 'individual',
                'TipoPuntuacion' => 'posiciones',
                'CupoMaximoDefault' => 4,
                'Estado' => 'activa',
                'Reglas' => "Máximo 2 deportistas por facultad en damas y 2 en varones (singles únicamente).\n"
                    . "Eliminación simple; si hay menos de 4 participantes, se juega round robin.\n"
                    . "Se juega al mejor de 2 sets; en caso de 1-1 se juega un match break a 10 puntos con diferencia de 2.\n"
                    . "Puntaje: Ganador 2 pts, Perdedor 1 pt, No presentado 0 pts. El cuadro final de facultades se obtiene del puntaje general de sus 2 competidores en cada categoría.\n"
                    . "Se rige por el reglamento de la Federación Deportiva Peruana de Tenis.",
            ],
            [
                'Nombre' => 'Natación',
                'Descripcion' => 'Natación damas y varones, series finales por tiempo. Se rige por el reglamento de la FINA.',
                'TipoParticipacion' => 'individual',
                'TipoPuntuacion' => 'posiciones',
                'CupoMaximoDefault' => null,
                'Estado' => 'activa',
                'Reglas' => "Pruebas: 50m libre, espalda, pecho y mariposa; posta 4x50m libre; posta 4x50m combinado; 200m combinado (damas y varones).\n"
                    . "Cada nadador participa en máximo 4 pruebas individuales más una posta. Máximo 2 nadadores por facultad por prueba; 1 posta por facultad en cada posta.\n"
                    . "Cada facultad inscribe un delegado titular responsable de la ficha de inscripción y cambios.\n"
                    . "Puntaje (8 mejores tiempos): 10, 8, 6, 5, 4, 3, 2, 1; en postas el doble.\n"
                    . "Campeón general: mayor puntaje acumulado; en caso de empate se decide por mayor cantidad de primeros lugares, luego segundos, y así sucesivamente.",
            ],
            [
                'Nombre' => 'Triatlón',
                'Descripcion' => 'Triatlón damas y varones: natación + ciclismo + carrera, de forma continua.',
                'TipoParticipacion' => 'individual',
                'TipoPuntuacion' => 'posiciones',
                'CupoMaximoDefault' => 20,
                'Estado' => 'activa',
                'Reglas' => "Máximo 10 deportistas por facultad en damas y 10 en varones.\n"
                    . "Recorrido continuo: natación 50m + ciclismo 9km + carrera 4km. Cada deportista trae su propia bicicleta.\n"
                    . "Sanción a quien no cumpla el volumen mínimo de cada tramo por omisión o fraude.\n"
                    . "Puntaje por equipo (15 mejores puestos): 1° 20, 2° 16, 3° 14, 4° 12, 5° 11, 6° 10, 7° 9, 8° 8, 9° 7, 10° 6, 11° 5, 12° 4, 13° 3, 14° 2, 15° 1 punto.\n"
                    . "Se premian los 3 primeros lugares de cada categoría (damas y varones).",
            ],
            [
                'Nombre' => 'Billar Federado',
                'Descripcion' => 'Billar federado, modalidad pool/billar americano.',
                'TipoParticipacion' => 'individual',
                'TipoPuntuacion' => 'posiciones',
                'CupoMaximoDefault' => 4,
                'Estado' => 'activa',
                'Reglas' => "Cada facultad inscribe hasta 4 estudiantes (damas y/o varones).\n"
                    . "Sistema de eliminación simple, competencia en un solo día hábil.\n"
                    . "Puntaje por puesto (10 mejores): 1° 10, 2° 8, 3° 7, 4° 6, 5° 5, 6° 4, 7° 3, 8° 2, 9° 1, 10° 0.5 puntos.\n"
                    . "El puntaje final de la facultad es la suma de los puntos de todos sus competidores.",
            ],
            [
                'Nombre' => 'Maratón',
                'Descripcion' => 'Maratón damas y varones por equipos de facultad.',
                'TipoParticipacion' => 'individual',
                'TipoPuntuacion' => 'posiciones',
                'CupoMaximoDefault' => 24,
                'Estado' => 'activa',
                'Reglas' => "Cada facultad inscribe hasta 12 participantes en damas y 12 en varones.\n"
                    . "Puntaje por equipo según el orden de llegada de los primeros 15 corredores de cada facultad (puntaje por equipos, no individual): 1° 20, 2° 16, 3° 14, 4° 12, 5° 11, 6° 10, 7° 9, 8° 8, 9° 7, 10° 6, 11° 5, 12° 4, 13° 3, 14° 2, 15° 1 punto.",
            ],
            [
                'Nombre' => 'Ciclismo',
                'Descripcion' => 'Ciclismo de ruta y/o circuito, damas y varones.',
                'TipoParticipacion' => 'individual',
                'TipoPuntuacion' => 'posiciones',
                'CupoMaximoDefault' => 16,
                'Estado' => 'activa',
                'Reglas' => "Cada facultad inscribe hasta 8 damas y 8 varones.\n"
                    . "Modalidad ruta y/o circuito; sistema todos contra todos. Equipo obligatorio: casco, guantes, lentes, protectores de rodilla y bicicleta propia.\n"
                    . "Descalificación por incumplir la ruta establecida, suplantación de persona o atajar la ruta.\n"
                    . "Puntaje por puesto (10 mejores): 1° 10, 2° 8, 3° 7, 4° 6, 5° 5, 6° 4, 7° 3, 8° 2, 9° 1, 10° 0.5 puntos.",
            ],
            [
                'Nombre' => 'Inauguración / Desfile',
                'Descripcion' => 'Calificación del desfile inaugural de cada delegación. Otorga puntos adicionales al cuadro general.',
                'TipoParticipacion' => 'equipo',
                'TipoPuntuacion' => 'posiciones',
                'CupoMaximoDefault' => null,
                'Estado' => 'activa',
                'Reglas' => "Las delegaciones deben presentarse en el punto de encuentro a la hora indicada para iniciar el desfile; una vez cerrado el ingreso no se admiten más delegaciones.\n"
                    . "Calificación del 1 al 10 según: puntualidad, presentación, orden y originalidad (pancartas, mascotas, disfraces, etc.).\n"
                    . "Conteo de delegación: alumnos matriculados de la facultad, acompañados de su madrina y docentes; formación en filas de 3 o 4.\n"
                    . "Prohibido el uso de artefactos pirotécnicos, salvo bengalas.\n"
                    . "Los puntos obtenidos se suman como puntaje adicional al cuadro general de la facultad.",
            ],
            [
                'Nombre' => 'Caminata',
                'Descripcion' => 'Caminata de motivación de cada delegación. Otorga puntos adicionales al cuadro general.',
                'TipoParticipacion' => 'equipo',
                'TipoPuntuacion' => 'posiciones',
                'CupoMaximoDefault' => null,
                'Estado' => 'activa',
                'Reglas' => "Las delegaciones deben presentarse en el punto de partida a la hora indicada; iniciada la caminata no se modifica el orden de las delegaciones.\n"
                    . "Calificación del 1 al 10 según: puntualidad, presentación, orden y originalidad (pancartas, mascotas, disfraces, etc.).\n"
                    . "Formación en filas de 3 o 4 según el número de integrantes de cada facultad.\n"
                    . "Prohibido el uso de artefactos pirotécnicos, salvo bengalas.\n"
                    . "Los puntos obtenidos se suman como puntaje adicional al cuadro general de la facultad.",
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('olimpiada_resultado_posicion');

        Schema::table('olimpiada_resultado', function (Blueprint $table) {
            $table->dropColumn('Lugar');
        });

        Schema::table('olimpiada_edicion_disciplina', function (Blueprint $table) {
            $table->dropColumn('Lugar');
        });

        Schema::table('olimpiada_disciplina', function (Blueprint $table) {
            $table->dropColumn('TipoPuntuacion');
        });
    }
};
