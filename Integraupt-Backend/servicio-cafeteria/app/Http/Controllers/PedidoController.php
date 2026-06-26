<?php

namespace App\Http\Controllers;

use App\Http\Requests\PedidoRequest;
use App\Models\CafeteriaPedido;
use App\Services\PedidoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class PedidoController extends Controller
{
    public function __construct(private PedidoService $pedidoService)
    {
    }

    public function misPedidos(Request $request)
    {
        $usuarioId = (int) $request->query('usuarioId');
        $pedidos = $this->pedidoService->listarPorUsuario($usuarioId)->map(fn ($p) => $this->mapear($p))->all();

        return response()->json($pedidos);
    }

    public function porCafeteria(Request $request, $idCafeteria)
    {
        $estado = $request->query('estado');
        $pedidos = $this->pedidoService->listarPorCafeteria((int) $idCafeteria, $estado)
            ->map(fn ($p) => $this->mapear($p, true))
            ->all();

        return response()->json($pedidos);
    }

    public function store(PedidoRequest $request, $idCafeteria)
    {
        $items = collect($request->input('items'))
            ->map(fn ($item) => ['idProducto' => (int) $item['productoId'], 'cantidad' => (int) $item['cantidad']])
            ->all();

        $comprobanteUrl = null;
        if ($request->hasFile('comprobante')) {
            $file = $request->file('comprobante');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $destino = public_path('uploads/comprobantes');
            if (! is_dir($destino)) {
                mkdir($destino, 0755, true);
            }
            $file->move($destino, $filename);
            $comprobanteUrl = rtrim(config('app.url'), '/') . '/uploads/comprobantes/' . $filename;
        }

        try {
            $pedido = $this->pedidoService->crear(
                (int) $idCafeteria,
                (int) $request->input('usuarioId'),
                $items,
                $comprobanteUrl,
                $request->input('codigoOperacion')
            );

            return response()->json($this->mapear($pedido, true), 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cafeteria no encontrada.'], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function aprobar($idPedido)
    {
        try {
            return response()->json($this->mapear($this->pedidoService->aprobar((int) $idPedido)));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function rechazar(Request $request, $idPedido)
    {
        $request->validate(['motivo' => 'required|string|max:255']);

        try {
            $pedido = $this->pedidoService->rechazar((int) $idPedido, $request->input('motivo'));
            return response()->json($this->mapear($pedido));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cancelar(Request $request, $idPedido)
    {
        $request->validate(['usuarioId' => 'required|integer']);

        try {
            $pedido = $this->pedidoService->cancelar((int) $idPedido, (int) $request->input('usuarioId'));
            return response()->json($this->mapear($pedido));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cambiarEstado(Request $request, $idPedido)
    {
        $request->validate(['estado' => 'required|string|in:preparando,listo']);

        try {
            $pedido = $this->pedidoService->cambiarEstado((int) $idPedido, $request->input('estado'));
            return response()->json($this->mapear($pedido));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Pedido no encontrado.'], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function checkin(Request $request)
    {
        $request->validate(['codigoQr' => 'required|string']);

        try {
            $pedido = $this->pedidoService->checkin($request->input('codigoQr'));
            return response()->json($this->mapear($pedido));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function mapear(CafeteriaPedido $pedido, bool $conUsuario = false): array
    {
        $data = [
            'id' => $pedido->IdPedido,
            'cafeteriaId' => $pedido->IdCafeteria,
            'cafeteriaNombre' => $pedido->cafeteria?->Nombre,
            'usuarioId' => $pedido->IdUsuario,
            'total' => (float) $pedido->Total,
            'estado' => $pedido->Estado,
            'codigoQr' => $pedido->CodigoQr,
            'comprobanteUrl' => $pedido->ComprobanteUrl,
            'codigoOperacion' => $pedido->CodigoOperacion,
            'motivoRechazo' => $pedido->MotivoRechazo,
            'fechaPedido' => optional($pedido->FechaPedido)->toIso8601String(),
            'fechaConfirmacionPago' => optional($pedido->FechaConfirmacionPago)->toIso8601String(),
            'fechaEntrega' => optional($pedido->FechaEntrega)->toIso8601String(),
            'items' => $pedido->items->map(fn ($item) => [
                'productoId' => $item->IdProducto,
                'productoNombre' => $item->producto?->Nombre,
                'cantidad' => $item->Cantidad,
                'precioUnitario' => (float) $item->PrecioUnitario,
            ])->all(),
        ];

        if ($conUsuario) {
            $data['usuarioNombre'] = $pedido->usuario?->NombreCompleto;
        }

        return $data;
    }
}
