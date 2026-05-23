<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'rol';
    protected $primaryKey = 'IdRol';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['IdRol', 'Nombre'];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'Rol', 'IdRol');
    }
}
