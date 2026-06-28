<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SilaboUnidad extends Model
{
    protected $table = 'silabo_unidad';
    protected $primaryKey = 'IdUnidad';

    protected $fillable = [
        'SilaboId',
        'Numero',
        'Nombre',
        'HorasTotal',
    ];

    public function silabo()
    {
        return $this->belongsTo(Silabo::class, 'SilaboId', 'IdSilabo');
    }

    public function temas()
    {
        return $this->hasMany(SilaboTema::class, 'UnidadId', 'IdUnidad')
                    ->orderBy('Semana')->orderBy('Orden');
    }
}
