<?php

namespace App\Http\Controllers;

use App\Http\Requests\DisciplinaRequest;
use App\Models\OlimpiadaDisciplina;
use App\Services\DisciplinaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class DisciplinaController extends Controller
{
    public function __construct(private DisciplinaService $disciplinaService)
    {
    }

    public function index(Request $request)
    {
        $disciplinas = $this->disciplinaService->listar($request->query('estado'))
            ->map(fn (OlimpiadaDisciplina $d) => $this->mapear($d))
            ->all();

        return response()->json($disciplinas);
    }

    public function store(DisciplinaRequest $request)
    {
        $disciplina = $this->disciplinaService->crear($request->validated());
        return response()->json($this->mapear($disciplina), 201);
    }

    public function update(DisciplinaRequest $request, $id)
    {
        try {
            $disciplina = $this->disciplinaService->actualizar((int) $id, $request->validated());
            return response()->json($this->mapear($disciplina));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate(['estado' => 'required|in:activa,inactiva']);

        try {
            $disciplina = $this->disciplinaService->cambiarEstado((int) $id, $request->input('estado'));
            return response()->json($this->mapear($disciplina));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    private function mapear(OlimpiadaDisciplina $disciplina): array
    {
        return [
            'id' => $disciplina->IdDisciplina,
            'nombre' => $disciplina->Nombre,
            'descripcion' => $disciplina->Descripcion,
            'tipoParticipacion' => $disciplina->TipoParticipacion,
            'reglas' => $disciplina->Reglas,
            'cupoMaximoDefault' => $disciplina->CupoMaximoDefault,
            'estado' => $disciplina->Estado,
        ];
    }
}
