<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioAuth extends Model
{
    protected $table = 'usuario_auth';
    protected $primaryKey = 'IdAuth';
    public $timestamps = false;

    protected $fillable = [
        'IdUsuario', 'CorreoU', 'Password',
        'UltimoLogin', 'SesionToken', 'SesionExpira', 'SesionTipo',
    ];

    protected $hidden = ['Password', 'SesionToken'];

    protected $casts = [
        'UltimoLogin'  => 'datetime',
        'SesionExpira' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }
}
