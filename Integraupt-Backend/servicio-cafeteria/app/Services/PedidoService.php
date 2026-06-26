<?php

namespace App\Services;

use App\Models\Cafeteria;
use App\Models\CafeteriaPedido;
use App\Models\CafeteriaPedidoItem;
use App\Models\CafeteriaProducto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class PedidoService
{
    private const TRANSICIONES_VALIDAS = [
        CafeteriaPedido::ESTADO_PAGADO => [CafeteriaPedido::ESTADO_PREPARANDO],
        CafeteriaPedido::ESTADO_PREPARANDO => [CafeteriaPedido::ESTADO_LISTO],
    ];

    public function listarPorUsuario(int $idUsuario): Collection
    {
        return CafeteriaPedido::with(['items.producto', 'cafeteria'])
            ->where('IdUsuario', $idUsuario)
            ->orderByDesc('FechaPedido')
            ->get();
    }

    public function listarPorCafeteria(int $idCafeteria, ?string $estado = null): Collection
    {
        $query = CafeteriaPedido::with(['items.producto', 'usuario'])
            ->where('IdCafeteria', $idCafeteria);

        if ($estado) {
            $query->where('Estado', $estado);
        }

        return $query->orderBy('FechaPedido')->get();
    }

    /**
     * @param array<int, array{idProducto:int, cantidad:int}> $items
     */
    public function crear(int $idCafeteria, int $idUsuario, array $items, ?string $comprobanteUrl, ?string $codigoOperacion): CafeteriaPedido
    {
        if (empty($items)) {
            throw new InvalidArgumentException('El pedido debe tener al menos un producto.');
        }

        $cafeteria = Cafeteria::findOrFail($idCafeteria);
        if (! $cafeteria->Estado) {
            throw new InvalidArgumentException('Esta cafeteria se encuentra cerrada por el momento.');
        }

        return DB::transaction(function () use ($idCafeteria, $idUsuario, $items, $comprobanteUrl, $codigoOperacion) {
            $total = 0;
            $detalle = [];

            foreach ($items as $item) {
                $producto = CafeteriaProducto::where('IdProducto', $item['idProducto'])
                    ->where('IdCafeteria', $idCafeteria)
                    ->lockForUpdate()
                    ->first();

                if (! $producto || ! $producto->Estado) {
                    throw new InvalidArgumentException('Uno de los productos seleccionados no esta disponible.');
                }

                $cantidad = (int) $item['cantidad'];
                if ($cantidad <= 0) {
                    throw new InvalidArgumentException('La cantidad debe ser mayor a cero.');
                }

                if ($producto->Stock < $cantidad) {
                    throw new InvalidArgumentException("No hay stock suficiente de \"{$producto->Nombre}\".");
                }

                $producto->decrement('Stock', $cantidad);

                $precioUnitario = (float) $producto->Precio;
                $total += $precioUnitario * $cantidad;

                $detalle[] = [
                    'IdProducto' => $producto->IdProducto,
                    'Cantidad' => $cantidad,
                    'PrecioUnitario' => $precioUnitario,
                ];
            }

            $pedido = CafeteriaPedido::create([
                'IdCafeteria' => $idCafeteria,
                'IdUsuario' => $idUsuario,
                'Total' => $total,
                'Estado' => CafeteriaPedido::ESTADO_PENDIENTE_REVISION,
                'ComprobanteUrl' => $comprobanteUrl,
                'CodigoOperacion' => $codigoOperacion,
            ]);

            foreach ($detalle as $linea) {
                CafeteriaPedidoItem::create([...$linea, 'IdPedido' => $pedido->IdPedido]);
            }

            return $pedido->refresh()->load('items.producto');
        });
    }

    public function aprobar(int $idPedido): CafeteriaPedido
    {
        $pedido = CafeteriaPedido::findOrFail($idPedido);

        if ($pedido->Estado !== CafeteriaPedido::ESTADO_PENDIENTE_REVISION) {
            throw new InvalidArgumentException('Solo se pueden aprobar pedidos pendientes de revision.');
        }

        $pedido->update([
            'Estado' => CafeteriaPedido::ESTADO_PAGADO,
            'CodigoQr' => (string) Str::uuid(),
            'FechaConfirmacionPago' => now(),
        ]);

        return $pedido->refresh();
    }

    public function rechazar(int $idPedido, string $motivo): CafeteriaPedido
    {
        $pedido = CafeteriaPedido::with('items')->findOrFail($idPedido);

        if ($pedido->Estado !== CafeteriaPedido::ESTADO_PENDIENTE_REVISION) {
            throw new InvalidArgumentException('Solo se pueden rechazar pedidos pendientes de revision.');
        }

        return DB::transaction(function () use ($pedido, $motivo) {
            $this->restockItems($pedido);
            $pedido->update(['Estado' => CafeteriaPedido::ESTADO_RECHAZADO, 'MotivoRechazo' => $motivo]);

            return $pedido->refresh();
        });
    }

    public function cancelar(int $idPedido, int $idUsuario): CafeteriaPedido
    {
        $pedido = CafeteriaPedido::with('items')->findOrFail($idPedido);

        if ($pedido->IdUsuario !== $idUsuario) {
            throw new InvalidArgumentException('No puedes cancelar un pedido que no es tuyo.');
        }

        if ($pedido->Estado !== CafeteriaPedido::ESTADO_PENDIENTE_REVISION) {
            throw new InvalidArgumentException('Solo puedes cancelar un pedido mientras esta pendiente de revision.');
        }

        return DB::transaction(function () use ($pedido) {
            $this->restockItems($pedido);
            $pedido->update(['Estado' => CafeteriaPedido::ESTADO_CANCELADO]);

            return $pedido->refresh();
        });
    }

    public function cambiarEstado(int $idPedido, string $nuevoEstado): CafeteriaPedido
    {
        $pedido = CafeteriaPedido::findOrFail($idPedido);
        $permitidos = self::TRANSICIONES_VALIDAS[$pedido->Estado] ?? [];

        if (! in_array($nuevoEstado, $permitidos, true)) {
            throw new InvalidArgumentException("No se puede pasar de \"{$pedido->Estado}\" a \"{$nuevoEstado}\".");
        }

        $pedido->update(['Estado' => $nuevoEstado]);

        return $pedido->refresh();
    }

    public function checkin(string $codigoQr): CafeteriaPedido
    {
        $pedido = CafeteriaPedido::where('CodigoQr', $codigoQr)->first();

        if (! $pedido) {
            throw new RuntimeException('Codigo QR no reconocido.');
        }

        if ($pedido->Estado === CafeteriaPedido::ESTADO_ENTREGADO) {
            throw new InvalidArgumentException('Este pedido ya fue entregado.');
        }

        if ($pedido->Estado !== CafeteriaPedido::ESTADO_LISTO) {
            throw new InvalidArgumentException('El pedido todavia no esta listo para ser entregado.');
        }

        $pedido->update(['Estado' => CafeteriaPedido::ESTADO_ENTREGADO, 'FechaEntrega' => now()]);

        return $pedido->refresh();
    }

    private function restockItems(CafeteriaPedido $pedido): void
    {
        foreach ($pedido->items as $item) {
            CafeteriaProducto::where('IdProducto', $item->IdProducto)->increment('Stock', $item->Cantidad);
        }
    }
}
