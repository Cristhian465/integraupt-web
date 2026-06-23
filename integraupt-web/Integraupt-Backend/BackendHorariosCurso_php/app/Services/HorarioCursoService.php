<?php

namespace App\Services;

use App\Models\HorarioCurso;
use App\Repositories\CatalogoRepository;
use App\Repositories\HorarioCursoRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Carbon\Carbon;

class HorarioCursoService
{
    public function __construct(
        protected HorarioCursoRepository $horarioCursoRepo,
        protected CatalogoRepository $catalogoRepo
    ) {}

    public function listar(): array
    {
        return array_map([$this, 'mapearRespuesta'], $this->horarioCursoRepo->listarDetalle());
    }

    public function buscarPorId(int $id): array
    {
        $detalle = $this->horarioCursoRepo->buscarDetallePorId($id);
        if (!$detalle) {
            throw new HttpException(404, "No se encontro el horario con id " . $id);
        }
        return $this->mapearRespuesta($detalle);
    }

    public function crear(array $data): array
    {
        $this->validarReferencias($data);
        $this->validarRangoFechas($data['fechaInicio'] ?? null, $data['fechaFin'] ?? null);

        $horarioCurso = new HorarioCurso();
        $this->aplicarDatos($horarioCurso, $data);
        $horarioCurso->save();

        return $this->buscarPorId($horarioCurso->IdHorarioCurso);
    }

    public function actualizar(int $id, array $data): array
    {
        $horarioCurso = HorarioCurso::find($id);
        if (!$horarioCurso) {
            throw new HttpException(404, "No se encontro el horario con id " . $id);
        }

        $this->validarReferencias($data);
        $this->validarRangoFechas($data['fechaInicio'] ?? null, $data['fechaFin'] ?? null);

        $this->aplicarDatos($horarioCurso, $data);
        $horarioCurso->save();

        return $this->buscarPorId($id);
    }

    public function eliminar(int $id): void
    {
        $horarioCurso = HorarioCurso::find($id);
        if (!$horarioCurso) {
            throw new HttpException(404, "No se encontro el horario con id " . $id);
        }
        $horarioCurso->delete();
    }

    private function aplicarDatos(HorarioCurso $destino, array $data): void
    {
        $destino->Curso = $data['curso'];
        $destino->Docente = $data['docente'];
        $destino->Espacio = $data['espacio'];
        $destino->Bloque = $data['bloque'];
        $destino->DiaSemana = $data['diaSemana'];
        $destino->FechaInicio = $data['fechaInicio'];
        $destino->FechaFin = $data['fechaFin'];
        $destino->Estado = filter_var($data['estado'], FILTER_VALIDATE_BOOLEAN);
    }

    private function validarReferencias(array $data): void
    {
        if (!$this->catalogoRepo->existeCurso($data['curso'])) {
            throw new HttpException(400, "El curso seleccionado no es valido.");
        }
        if (!$this->catalogoRepo->existeDocente($data['docente'])) {
            throw new HttpException(400, "El docente seleccionado no es valido.");
        }
        if (!$this->catalogoRepo->existeEspacio($data['espacio'])) {
            throw new HttpException(400, "El espacio seleccionado no es valido.");
        }
        if (!$this->catalogoRepo->existeBloque($data['bloque'])) {
            throw new HttpException(400, "El bloque seleccionado no es valido.");
        }
    }

    private function validarRangoFechas(?string $inicio, ?string $fin): void
    {
        if (!$inicio || !$fin) {
            return;
        }
        $dateInicio = Carbon::parse($inicio);
        $dateFin = Carbon::parse($fin);

        if ($dateFin->lt($dateInicio)) {
            throw new HttpException(400, "La fecha fin no puede ser anterior a la fecha inicio.");
        }
    }

    private function mapearRespuesta(object $row): array
    {
        return [
            'idHorarioCurso' => (int) $row->idHorarioCurso,
            'curso' => (int) $row->curso,
            'docente' => (int) $row->docente,
            'espacio' => (int) $row->espacio,
            'bloque' => (int) $row->bloque,
            'diaSemana' => $row->diaSemana,
            'fechaInicio' => Carbon::parse($row->fechaInicio)->format('Y-m-d'),
            'fechaFin' => Carbon::parse($row->fechaFin)->format('Y-m-d'),
            'estado' => (bool) $row->estado,
            'nombreCurso' => $row->nombreCurso,
            'nombreDocente' => $row->nombreDocente,
            'nombreEspacio' => $row->nombreEspacio,
            'codigoEspacio' => $row->codigoEspacio,
            'nombreBloque' => $row->nombreBloque,
            'horaInicioBloque' => $row->horaInicio,
            'horaFinBloque' => $row->horaFinal
        ];
    }
}
