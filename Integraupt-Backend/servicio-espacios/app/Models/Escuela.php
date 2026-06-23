<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Escuela extends Model
{
    protected $table = 'escuela';
    protected $primaryKey = 'IdEscuela';
    public $timestamps = false;

    protected $fillable = [
        'IdFacultad',
        'Nombre',
    ];

    protected $casts = [
        'IdFacultad' => 'integer',
    ];

    public function facultad(): BelongsTo
    {
        return $this->belongsTo(Facultad::class, 'IdFacultad', 'IdFacultad');
    }

    public function espacios(): HasMany
    {
        return $this->hasMany(Espacio::class, 'Escuela', 'IdEscuela');
    }
}
