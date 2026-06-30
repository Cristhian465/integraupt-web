<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Silabo extends Model
{
    protected $table      = 'silabo';
    protected $primaryKey = 'IdSilabo';

    protected $fillable = [
        'CodigoCurso',
        'NombreCurso',
        'CicloNumero',
        'Semestre',
        'Horas',
        'Creditos',
        'Docente',
        'CorreoDocente',
        'HorarioCursoId',
        'DiasXSemana',
        'ArchivoPdf',
        'FechaCarga',
        'Estado',
    ];

    public function unidades()
    {
        return $this->hasMany(SilaboUnidad::class, 'SilaboId', 'IdSilabo')
                    ->orderBy('Numero');
    }
}
