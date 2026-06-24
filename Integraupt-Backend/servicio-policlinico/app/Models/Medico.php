<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    protected $table = 'medico';
    protected $primaryKey = 'IdMedico';
    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Estado',
    ];

    protected $casts = [
        'Estado' => 'boolean',
    ];

    public function tiposAtencion()
    {
        return $this->belongsToMany(
            TipoAtencion::class,
            'medico_tipo_atencion',
            'Medico',
            'TipoAtencion',
            'IdMedico',
            'IdTipoAtencion'
        );
    }
}
