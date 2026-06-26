<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventoRequest;
use App\Models\Evento;
use App\Services\EventoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;

class EventoController extends Controller
{
    public function __construct(private EventoService $eventoService)
    {
    }

    public function index(Request $request)
    {
        $filtros = [
            'facultadId' => $request->query('facultadId'),
            'escuelaId' => $request->query('escuelaId'),
            'tipoEvento' => $request->query('tipoEvento'),
            'estado' => $request->query('estado'),
            'desde' => $request->query('desde'),
            'hasta' => $request->query('hasta'),
            'porPagina' => $request->query('porPagina', 15),
        ];

        $eventos = $this->eventoService->listar($filtros);

        return response()->json([
            'data' => collect($eventos->items())->map(fn (Evento $e) => $this->mapear($e))->all(),
            'total' => $eventos->total(),
            'pagina' => $eventos->currentPage(),
            'porPagina' => $eventos->perPage(),
        ]);
    }

    public function show($id)
    {
        try {
            return response()->json($this->mapear($this->eventoService->encontrar((int) $id)));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Evento no encontrado.'], 404);
        }
    }

    public function store(EventoRequest $request)
    {
        try {
            $datos = $this->aPascalCase($request->validated());

            if ($request->hasFile('imagen')) {
                $datos['ImagenUrl'] = $this->guardarImagen($request);
            }

            $evento = $this->eventoService->crear($datos);
            return response()->json($this->mapear($evento), 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(EventoRequest $request, $id)
    {
        try {
            $datos = $this->aPascalCase($request->validated());

            if ($request->hasFile('imagen')) {
                $datos['ImagenUrl'] = $this->guardarImagen($request);
            }

            $evento = $this->eventoService->actualizar((int) $id, $datos);
            return response()->json($this->mapear($evento));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Evento no encontrado.'], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function guardarImagen(Request $request): string
    {
        $file = $request->file('imagen');
        $destino = public_path('uploads/eventos');

        if (! is_dir($destino)) {
            mkdir($destino, 0755, true);
        }

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move($destino, $filename);

        return rtrim(config('app.url'), '/') . '/uploads/eventos/' . $filename;
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate(['estado' => 'required|string|in:borrador,publicado,en_curso,finalizado,cancelado']);

        try {
            $evento = $this->eventoService->cambiarEstado((int) $id, $request->input('estado'));
            return response()->json($this->mapear($evento));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Evento no encontrado.'], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reporte($id)
    {
        try {
            return response()->json($this->eventoService->reporteAsistencia((int) $id));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Evento no encontrado.'], 404);
        }
    }

    private function aPascalCase(array $datos): array
    {
        return [
            'Titulo' => $datos['titulo'],
            'Descripcion' => $datos['descripcion'] ?? null,
            'TipoEvento' => $datos['tipoEvento'],
            'Alcance' => $datos['alcance'],
            'IdFacultad' => $datos['facultadId'],
            'IdEscuela' => $datos['escuelaId'] ?? null,
            'IdEspacio' => $datos['espacioId'] ?? null,
            'FechaInicio' => $datos['fechaInicio'],
            'FechaFin' => $datos['fechaFin'],
            'AforoMaximo' => $datos['aforoMaximo'] ?? null,
            'RequiereInscripcion' => $datos['requiereInscripcion'] ?? true,
            'IdResponsable' => $datos['responsableId'],
        ];
    }

    private function mapear(Evento $evento): array
    {
        return [
            'id' => $evento->IdEvento,
            'titulo' => $evento->Titulo,
            'descripcion' => $evento->Descripcion,
            'tipoEvento' => $evento->TipoEvento,
            'alcance' => $evento->Alcance,
            'facultadId' => $evento->IdFacultad,
            'facultadNombre' => $evento->facultad?->Nombre,
            'escuelaId' => $evento->IdEscuela,
            'escuelaNombre' => $evento->escuela?->Nombre,
            'espacioId' => $evento->IdEspacio,
            'espacioNombre' => $evento->espacio?->Nombre,
            'fechaInicio' => optional($evento->FechaInicio)->toIso8601String(),
            'fechaFin' => optional($evento->FechaFin)->toIso8601String(),
            'aforoMaximo' => $evento->AforoMaximo,
            'requiereInscripcion' => (bool) $evento->RequiereInscripcion,
            'estado' => $evento->Estado,
            'imagenUrl' => $evento->ImagenUrl,
            'inscritos' => $evento->inscritos_count ?? null,
            'cuposDisponibles' => $evento->AforoMaximo !== null
                ? max(0, $evento->AforoMaximo - ($evento->inscritos_count ?? 0))
                : null,
            'responsableId' => $evento->IdResponsable,
            'responsableNombre' => $evento->responsable?->NombreCompleto,
        ];
    }
}
