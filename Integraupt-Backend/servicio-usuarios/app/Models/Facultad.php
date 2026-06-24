<?php

namespace App\Models;

class Facultad extends BaseModel
{
    protected $table = 'facultad';
    protected $primaryKey = 'IdFacultad';
    public $timestamps = false;
    protected $guarded = [];
}
