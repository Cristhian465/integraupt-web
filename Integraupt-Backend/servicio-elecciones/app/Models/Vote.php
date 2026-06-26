<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_id',
        'student_id',
        'faculty',
        'asamblea_party_id',
        'consejo_uni_party_id',
        'consejo_fac_party_id',
        'verification_code',
        'ip_address',
    ];

    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    public function asambleaParty()
    {
        return $this->belongsTo(Party::class, 'asamblea_party_id');
    }

    public function consejoUniParty()
    {
        return $this->belongsTo(Party::class, 'consejo_uni_party_id');
    }

    public function consejoFacParty()
    {
        return $this->belongsTo(Party::class, 'consejo_fac_party_id');
    }
}
