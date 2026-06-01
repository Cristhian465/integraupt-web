namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuditoriaService;
use App\Services\AuditoriaExportService;
use Carbon\Carbon;
use Illuminate\Http\Response;

class AuditoriaController extends Controller
{
    private $service;
    private $exportService;

    public function __construct(
        AuditoriaService $service,
        AuditoriaExportService $exportService
    ) {
        $this->service = $service;
        $this->exportService = $exportService;
    }

    public function listar(Request $request)
    {
        $filtro = $this->construirFiltro($request);

        return response()->json(
            $this->service->buscar($filtro)
        );
    }

    public function detalle($id)
    {
        return response()->json(
            $this->service->buscarPorId($id)
        );
    }

    public function porReserva($reservaId)
    {
        return response()->json(
            $this->service->listarPorReserva($reservaId)
        );
    }

    public function exportarPdf(Request $request)
    {
        $filtro = $this->construirFiltro($request);

        $data = $this->exportService->generarPdf($filtro);

        return response($data)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="reporte_auditoria.pdf"');
    }

    public function exportarExcel(Request $request)
    {
        $filtro = $this->construirFiltro($request);

        $data = $this->exportService->generarExcel($filtro);

        return response($data)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="reporte_auditoria.xlsx"');
    }

    private function construirFiltro(Request $request)
    {
        return [
            'reservaId' => $request->reservaId,
            'estado' => $request->estado,
            'usuario' => $request->usuario,
            'fechaInicio' => $this->parseFecha($request->fechaInicio, false),
            'fechaFin' => $this->parseFecha($request->fechaFin, true),
        ];
    }

    private function parseFecha($valor, $endOfDay = false)
    {
        if (!$valor) return null;

        try {
            if (strlen($valor) == 10) {
                $fecha = Carbon::createFromFormat('Y-m-d', $valor);
                return $endOfDay ? $fecha->endOfDay() : $fecha->startOfDay();
            }

            return Carbon::parse($valor);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Formato de fecha inválido. Usa ISO-8601'
            ], 400)->throwResponse();
        }
    }
}