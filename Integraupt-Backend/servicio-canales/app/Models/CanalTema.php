<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CanalTema extends Model
{
    protected $table = 'canal_tema';

    protected $primaryKey = 'IdTema';

    public $timestamps = false;

    protected $fillable = [
        'IdCanal',
        'Nombre',
        'Descripcion',
        'Orden',
    ];

    protected $casts = [
        'FechaCreacion' => 'datetime',
    ];

    public function canal(): BelongsTo
    {
        return $this->belongsTo(Canal::class, 'IdCanal', 'IdCanal');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(CanalMensaje::class, 'IdTema', 'IdTema');
    }
}
