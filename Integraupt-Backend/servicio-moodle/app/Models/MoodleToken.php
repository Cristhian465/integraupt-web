<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodleToken extends Model
{
    protected $table = 'moodle_token';
    protected $primaryKey = 'IdMoodleToken';
    public $timestamps = false;

    protected $fillable = [
        'Estudiante',
        'MoodleUserId',
        'MoodleUsername',
        'Token',
        'PrivateToken',
        'FechaConexion',
        'FechaActualizacion',
    ];

    protected $casts = [
        'Token' => 'encrypted',
        'PrivateToken' => 'encrypted',
        'FechaConexion' => 'datetime',
        'FechaActualizacion' => 'datetime',
    ];
}
