<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    protected $table = 'facultad';

    protected $primaryKey = 'IdFacultad';

    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Abreviatura',
    ];
}
