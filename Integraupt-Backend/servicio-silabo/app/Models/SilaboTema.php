<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SilaboTema extends Model
{
    protected $table = 'silabo_tema';
    protected $primaryKey = 'IdTema';

    protected $fillable = [
        'UnidadId',
        'Semana',
        'ContenidoConceptual',
        'ContenidoProcedimental',
        'Orden',
    ];

    public function unidad()
    {
        return $this->belongsTo(SilaboUnidad::class, 'UnidadId', 'IdUnidad');
    }

    public function avances()
    {
        return $this->hasMany(AvanceTema::class, 'SilaboTemaId', 'IdTema');
    }
}
