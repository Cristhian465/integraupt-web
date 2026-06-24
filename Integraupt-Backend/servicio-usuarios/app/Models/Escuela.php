<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Escuela extends BaseModel
{
    protected $table = 'escuela';
    protected $primaryKey = 'IdEscuela';
    public $timestamps = false;
    protected $guarded = [];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'IdFacultad', 'IdFacultad');
    }
}
