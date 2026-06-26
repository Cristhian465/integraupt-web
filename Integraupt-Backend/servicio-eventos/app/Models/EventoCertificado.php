<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventoCertificado extends Model
{
    protected $table = 'evento_certificado';

    protected $primaryKey = 'IdCertificado';

    public $timestamps = false;

    protected $fillable = [
        'IdInscripcion',
        'UrlArchivo',
    ];

    protected $casts = [
        'FechaEmision' => 'datetime',
    ];

    public function inscripcion()
    {
        return $this->belongsTo(EventoInscripcion::class, 'IdInscripcion', 'IdInscripcion');
    }
}
