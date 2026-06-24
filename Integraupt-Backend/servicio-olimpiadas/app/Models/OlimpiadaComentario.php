<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OlimpiadaComentario extends Model
{
    protected $table = 'olimpiada_comentario';

    protected $primaryKey = 'IdComentario';

    public $timestamps = false;

    protected $fillable = [
        'Post',
        'Usuario',
        'Contenido',
        'FechaComentario',
    ];

    public function post()
    {
        return $this->belongsTo(OlimpiadaPost::class, 'Post', 'IdPost');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'Usuario', 'IdUsuario');
    }
}
