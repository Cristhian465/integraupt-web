<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CafeteriaPedido extends Model
{
    protected $table = 'cafeteria_pedido';

    protected $primaryKey = 'IdPedido';

    public $timestamps = false;

    public const ESTADO_PENDIENTE_REVISION = 'pendiente_revision';
    public const ESTADO_PAGADO = 'pagado';
    public const ESTADO_PREPARANDO = 'preparando';
    public const ESTADO_LISTO = 'listo';
    public const ESTADO_ENTREGADO = 'entregado';
    public const ESTADO_RECHAZADO = 'rechazado';
    public const ESTADO_CANCELADO = 'cancelado';

    protected $fillable = [
        'IdCafeteria',
        'IdUsuario',
        'Total',
        'Estado',
        'CodigoQr',
        'ComprobanteUrl',
        'CodigoOperacion',
        'MotivoRechazo',
        'FechaConfirmacionPago',
        'FechaEntrega',
    ];

    protected $casts = [
        'Total' => 'decimal:2',
        'FechaPedido' => 'datetime',
        'FechaConfirmacionPago' => 'datetime',
        'FechaEntrega' => 'datetime',
    ];

    public function cafeteria()
    {
        return $this->belongsTo(Cafeteria::class, 'IdCafeteria', 'IdCafeteria');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }

    public function items()
    {
        return $this->hasMany(CafeteriaPedidoItem::class, 'IdPedido', 'IdPedido');
    }
}
