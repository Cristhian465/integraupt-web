<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Escuela extends Model
{
    protected $table = 'escuela';

    protected $primaryKey = 'IdEscuela';

    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'IdFacultad',
    ];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'IdFacultad', 'IdFacultad');
    }
}
