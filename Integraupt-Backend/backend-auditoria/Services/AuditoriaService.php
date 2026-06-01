namespace App\Services;

use App\Models\AuditoriaReserva;
use Carbon\Carbon;

class AuditoriaService
{
    public function buscar($filtro)
    {
        $query = AuditoriaReserva::query();

        if (!empty($filtro['reservaId'])) {
            $query->where('registro_id', $filtro['reservaId']);
        }

        if (!empty($filtro['estado'])) {
            $query->where('estado', strtoupper($filtro['estado']));
        }

        if (!empty($filtro['usuario'])) {
            $query->where('usuario_nombre', 'like', '%' . $filtro['usuario'] . '%');
        }

        if (!empty($filtro['fechaInicio']) && !empty($filtro['fechaFin'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($filtro['fechaInicio']),
                Carbon::parse($filtro['fechaFin'])
            ]);
        }

        return $query->get()->map([$this, 'mapear']);
    }

    public function listarPorReserva($reservaId)
    {
        return AuditoriaReserva::where('registro_id', $reservaId)
            ->get()
            ->map([$this, 'mapear']);
    }

    public function buscarPorId($id)
    {
        $registro = AuditoriaReserva::where('id', $id)->first();

        if (!$registro) {
            abort(404, 'Auditoria no encontrada');
        }

        return $this->mapear($registro);
    }

    private function mapear($registro)
    {
        return [
            'idAudit' => $registro->id,
            'idReserva' => $registro->registro_id,
            'estadoAnterior' => $registro->estado_anterior ?? null,
            'estadoNuevo' => $registro->estado_nuevo ?? null,
            'fechaCambio' => $registro->fecha_accion ?? $registro->created_at,
            'usuarioCambioId' => $registro->usuario_id,
            'usuarioCambioNombre' => $this->normalizarNombre($registro->usuario_nombre),
            'usuarioCambioDocumento' => $registro->usuario_documento ?? null,
            'estadoReservaActual' => $registro->estado ?? null,
            'descripcionUso' => $registro->descripcion ?? null,
            'fechaReserva' => $registro->fecha_reserva ?? null,
            'codigoEspacio' => $registro->codigo_espacio ?? null,
            'nombreEspacio' => $registro->nombre_espacio ?? null,
        ];
    }

    private function normalizarNombre($nombre)
    {
        if (!$nombre) return null;

        return preg_replace('/\s+/', ' ', trim($nombre));
    }
}