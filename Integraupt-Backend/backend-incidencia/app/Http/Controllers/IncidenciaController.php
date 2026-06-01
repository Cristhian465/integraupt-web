namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\IncidenciaService;
use Illuminate\Http\Response;

class IncidenciaController extends Controller
{
    private $service;

    public function __construct(IncidenciaService $service)
    {
        $this->service = $service;
    }

    public function registrarIncidencia(Request $request)
    {
        try {
            $incidencia = $this->service->registrarIncidencia($request->all());

            return response()->json($incidencia, 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json($e->getMessage(), 404);

        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function listarParaGestion(Request $request)
    {
        return response()->json(
            $this->service->listarIncidenciasParaGestion(
                $request->rol,
                $request->facultadId,
                $request->escuelaId,
                $request->escuelaContextoId,
                $request->espacioId,
                $request->search
            )
        );
    }

    public function listarPorReserva($reservaId)
    {
        return response()->json(
            $this->service->listarPorReserva($reservaId)
        );
    }

    public function verificarDisponibilidad($reservaId)
    {
        return response()->json(
            $this->service->verificarDisponibilidad($reservaId)
        );
    }

    public function listarReservasPorUsuario($usuarioId)
    {
        return response()->json(
            $this->service->listarReservasParaUsuario($usuarioId)
        );
    }
}