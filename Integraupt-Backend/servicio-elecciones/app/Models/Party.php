<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_id',
        'name',
        'color',
        'logo_url',
        'description',
    ];

    public function election()
    {
        return $this->belongsTo(Election::class);
    }
}
