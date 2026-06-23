<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BloqueHorario extends Model
{
    protected $table = 'bloqueshorarios';
    protected $primaryKey = 'IdBloque';
    public $timestamps = false;

    protected $fillable = ['Orden', 'Nombre', 'HoraInicio', 'HoraFinal'];

    protected $casts = [
        'Orden' => 'integer',
    ];
}
