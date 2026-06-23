<?php

namespace App\Services;

use App\Models\BloqueHorario;
use App\Models\Curso;
use App\Models\Espacio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EspacioService
{
    public function listarActivos(): Collection
    {
        return Espacio::where('Estado', 1)->orderBy('Nombre')->get();
    }

    public function listarTodos(): Collection
    {
        return Espacio::orderBy('Nombre')->get();
    }

    public function listarActivosPorEscuela(?int $escuelaId): Collection
    {
        if ($escuelaId === null) {
            return $this->listarActivos();
        }

        return Espacio::where('Estado', 1)
            ->where('Escuela', $escuelaId)
            ->orderBy('Nombre')
            ->get();
    }

    public function listarTodosPorEscuela(?int $escuelaId): Collection
    {
        if ($escuelaId === null) {
            return $this->listarTodos();
        }

        return Espacio::where('Escuela', $escuelaId)->orderBy('Nombre')->get();
    }

    public function buscarPorId(int $id): ?Espacio
    {
        return Espacio::find($id);
    }

    public function listarCursosActivosPorEspacio(?int $espacioId): Collection
    {
        if ($espacioId === null) {
            return new Collection();
        }

        $espacio = Espacio::find($espacioId);
        if ($espacio === null) {
            return new Collection();
        }

        return Curso::where('Escuela', $espacio->Escuela)
            ->where('Estado', 1)
            ->orderBy('Nombre')
            ->get();
    }

    public function listarBloquesPorEspacio(?int $espacioId): Collection
    {
        if ($espacioId === null || !Espacio::where('IdEspacio', $espacioId)->exists()) {
            return new Collection();
        }

        $rows = DB::select(
            'SELECT DISTINCT b.* FROM bloqueshorarios b '
            . 'INNER JOIN horarios h ON h.bloque = b.IdBloque '
            . 'WHERE h.espacio = ? '
            . 'ORDER BY b.Orden ASC, b.Nombre ASC',
            [$espacioId]
        );

        return BloqueHorario::hydrate(array_map(fn (object $row) => (array) $row, $rows));
    }
}
