<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vote;
use Illuminate\Support\Str;

class MockVotesSeeder extends Seeder
{
    public function run()
    {
        $faculties = ['FAING', 'FAEDCOH', 'FACS', 'FAEM', 'FIAG', 'FADE'];
        $parties = [
            1 => 20, // Fuerza UPT (20% chance)
            2 => 50, // REU UPT (50% chance, winning)
            3 => 25, // FEUT UPT (25% chance)
            0 => 5 // Voto en Blanco (5% chance)
        ];
        
        $votes = [];
        
        for ($i = 0; $i < 5000; $i++) {
            $votes[] = [
                'election_id' => 1,
                'student_id' => '2024' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT) . '_' . Str::random(4),
                'faculty' => $faculties[array_rand($faculties)],
                'asamblea_party_id' => $this->getRandomParty($parties),
                'consejo_uni_party_id' => $this->getRandomParty($parties),
                'consejo_fac_party_id' => $this->getRandomParty($parties),
                'verification_code' => strtoupper(Str::random(8)),
                'ip_address' => '127.0.0.' . mt_rand(1, 255),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Insert in chunks of 200 to avoid memory issues
            if (count($votes) == 200) {
                Vote::insert($votes);
                $votes = [];
            }
        }
        
        // Insert remaining
        if (count($votes) > 0) {
            Vote::insert($votes);
        }
    }
    
    private function getRandomParty($parties) {
        $rand = mt_rand(1, 100);
        $cumulative = 0;
        foreach ($parties as $partyId => $chance) {
            $cumulative += $chance;
            if ($rand <= $cumulative) {
                return $partyId === 0 ? null : $partyId;
            }
        }
        return null;
    }
}
