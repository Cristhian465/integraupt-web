<?php

namespace App\Http\Controllers;

use App\Models\Canal;
use App\Services\CanalService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CanalController extends Controller
{
    public function __construct(private CanalService $canalService)
    {
    }

    public function index(Request $request)
    {
        $request->validate([
            'usuarioId' => 'required|integer',
        ]);

        $canales = $this->canalService->listarParaUsuario(
            (int) $request->query('usuarioId'),
            $request->boolean('esAdmin')
        );

        return response()->json([
            'data' => $canales->map(fn (Canal $c) => $this->mapear($c))->all(),
        ]);
    }

    public function show($id)
    {
        try {
            return response()->json($this->mapear($this->canalService->encontrar((int) $id)));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Canal no encontrado.'], 404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'creadorId' => 'required|integer|exists:usuario,IdUsuario',
            'miembros' => 'nullable|array',
            'miembros.*' => 'integer|exists:usuario,IdUsuario',
            'color' => 'nullable|string|max:7',
            'fotoUrl' => 'nullable|string',
        ]);

        try {
            $canal = $this->canalService->crear([
                'Nombre' => $request->input('nombre'),
                'Descripcion' => $request->input('descripcion'),
                'IdCreador' => $request->input('creadorId'),
                'Miembros' => $request->input('miembros', []),
                'Color' => $request->input('color'),
                'FotoUrl' => $request->input('fotoUrl'),
            ]);

            return response()->json($this->mapear($canal), 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'nullable|string|max:150',
            'descripcion' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'fotoUrl' => 'nullable|string',
        ]);

        try {
            $canal = $this->canalService->actualizar((int) $id, [
                'Nombre' => $request->input('nombre'),
                'Descripcion' => $request->input('descripcion'),
                'Color' => $request->input('color'),
                'FotoUrl' => $request->input('fotoUrl'),
            ]);

            return response()->json($this->mapear($canal));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Canal no encontrado.'], 404);
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate(['estado' => 'required|string|in:activo,archivado']);

        try {
            $canal = $this->canalService->cambiarEstado((int) $id, $request->input('estado'));
            return response()->json($this->mapear($canal));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Canal no encontrado.'], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function agregarMiembros(Request $request, $id)
    {
        $request->validate([
            'miembros' => 'required|array|min:1',
            'miembros.*' => 'integer|exists:usuario,IdUsuario',
        ]);

        try {
            $canal = $this->canalService->agregarMiembros((int) $id, $request->input('miembros'));
            return response()->json($this->mapear($canal));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function quitarMiembro($id, $idUsuario)
    {
        try {
            $this->canalService->quitarMiembro((int) $id, (int) $idUsuario);
            return response()->json(['message' => 'Miembro eliminado.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function mapear(Canal $canal): array
    {
        return [
            'id' => $canal->IdCanal,
            'nombre' => $canal->Nombre,
            'descripcion' => $canal->Descripcion,
            'creadorId' => $canal->IdCreador,
            'creadorNombre' => $canal->creador?->NombreCompleto,
            'tipoCreador' => $canal->TipoCreador,
            'estado' => $canal->Estado,
            'color' => $canal->Color,
            'fotoUrl' => $canal->FotoUrl,
            'fechaCreacion' => optional($canal->FechaCreacion)->toIso8601String(),
            'miembros' => $canal->miembros->map(fn ($m) => [
                'idUsuario' => $m->IdUsuario,
                'nombre' => $m->usuario?->NombreCompleto,
                'rol' => $m->Rol,
            ])->all(),
        ];
    }
}
