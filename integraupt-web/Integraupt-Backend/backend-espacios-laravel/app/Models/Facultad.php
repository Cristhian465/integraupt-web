<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facultad extends Model
{
    protected $table = 'facultad';
    protected $primaryKey = 'IdFacultad';
    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Abreviatura',
    ];

    public function escuelas(): HasMany
    {
        return $this->hasMany(Escuela::class, 'IdFacultad', 'IdFacultad');
    }
}
