<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cafeteria extends Model
{
    protected $table = 'cafeteria';

    protected $primaryKey = 'IdCafeteria';

    public $timestamps = false;

    protected $fillable = [
        'IdFacultad',
        'Nombre',
        'Estado',
        'IdEncargado',
    ];

    protected $casts = [
        'Estado' => 'boolean',
    ];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'IdFacultad', 'IdFacultad');
    }

    public function encargado()
    {
        return $this->belongsTo(Usuario::class, 'IdEncargado', 'IdUsuario');
    }

    public function productos()
    {
        return $this->hasMany(CafeteriaProducto::class, 'IdCafeteria', 'IdCafeteria');
    }
}
