<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CafeteriaPedidoItem extends Model
{
    protected $table = 'cafeteria_pedido_item';

    protected $primaryKey = 'IdItem';

    public $timestamps = false;

    protected $fillable = [
        'IdPedido',
        'IdProducto',
        'Cantidad',
        'PrecioUnitario',
    ];

    protected $casts = [
        'Cantidad' => 'integer',
        'PrecioUnitario' => 'decimal:2',
    ];

    public function producto()
    {
        return $this->belongsTo(CafeteriaProducto::class, 'IdProducto', 'IdProducto');
    }

    public function pedido()
    {
        return $this->belongsTo(CafeteriaPedido::class, 'IdPedido', 'IdPedido');
    }
}
