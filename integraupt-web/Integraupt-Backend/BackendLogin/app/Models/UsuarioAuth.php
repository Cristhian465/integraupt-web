<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioAuth extends Model
{
    protected $table = 'usuario_auth';
    protected $primaryKey = 'IdAuth';
    public $timestamps = false;
    protected $guarded = [];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }
}
