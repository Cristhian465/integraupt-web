<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    use HasFactory;

    protected $table = 'docente';

    protected $primaryKey = 'IdDocente';

    public $timestamps = false;

    protected $fillable = [
        'IdUsuario',
        'CodigoDocente',
        'Escuela'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }
}
