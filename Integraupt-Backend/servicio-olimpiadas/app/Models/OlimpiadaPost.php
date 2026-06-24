<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OlimpiadaPost extends Model
{
    protected $table = 'olimpiada_post';

    protected $primaryKey = 'IdPost';

    public $timestamps = false;

    protected $fillable = [
        'Edicion',
        'Titulo',
        'Contenido',
        'ImagenUrl',
        'Autor',
        'FechaPublicacion',
    ];

    public function edicion()
    {
        return $this->belongsTo(OlimpiadaEdicion::class, 'Edicion', 'IdEdicion');
    }

    public function comentarios()
    {
        return $this->hasMany(OlimpiadaComentario::class, 'Post', 'IdPost');
    }
}
