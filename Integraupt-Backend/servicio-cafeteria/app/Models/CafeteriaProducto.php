<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CafeteriaProducto extends Model
{
    protected $table = 'cafeteria_producto';

    protected $primaryKey = 'IdProducto';

    public $timestamps = false;

    protected $fillable = [
        'IdCafeteria',
        'Nombre',
        'Descripcion',
        'Precio',
        'Stock',
        'Estado',
    ];

    protected $casts = [
        'Precio' => 'decimal:2',
        'Stock' => 'integer',
        'Estado' => 'boolean',
    ];

    public function cafeteria()
    {
        return $this->belongsTo(Cafeteria::class, 'IdCafeteria', 'IdCafeteria');
    }
}
