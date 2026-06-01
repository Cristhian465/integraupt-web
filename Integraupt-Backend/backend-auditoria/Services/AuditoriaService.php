namespace App\Services;

use App\Models\AuditoriaReserva;

class AuditoriaService
{
    public function buscar($filtro)
    {
        $query = AuditoriaReserva::query();

        if ($filtro['reservaId']) {
            $query->where('registro_id', $filtro['reservaId']);
        }

        if ($filtro['estado']) {
            $query->where('estado', $filtro['estado']);
        }

        if ($filtro['usuario']) {
            $query->where('usuario_nombre', 'like', "%{$filtro['usuario']}%");
        }

        if ($filtro['fechaInicio'] && $filtro['fechaFin']) {
            $query->whereBetween('created_at', [
                $filtro['fechaInicio'],
                $filtro['fechaFin']
            ]);
        }

        return $query->get();
    }

    public function buscarPorId($id)
    {
        return AuditoriaReserva::findOrFail($id);
    }

    public function listarPorReserva($reservaId)
    {
        return AuditoriaReserva::where('registro_id', $reservaId)->get();
    }
}