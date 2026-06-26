<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Election;
use App\Models\Party;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $election = Election::create([
            'title' => 'Elecciones Generales del Estamento Estudiantil 2026-2027',
            'description' => 'Elecciones para Asamblea Universitaria, Consejo Universitario y Consejo de Facultad.',
            'start_date' => now(),
            'end_date' => now()->addDays(2),
            'is_active' => true,
        ]);

        Party::create([
            'election_id' => $election->id,
            'name' => 'Fuerza UPT',
            'color' => '#22c55e', // green-500
            'logo_url' => 'https://ui-avatars.com/api/?name=Fuerza+UPT&background=22c55e&color=fff&size=200',
            'description' => 'Somos una organización juvenil de la Universidad Privada de Tacna. Nuestro propósito: ser la voz, la acción y el impulso de la juventud universitaria. Lema: "LA EVOLUCIÓN".',
        ]);

        Party::create([
            'election_id' => $election->id,
            'name' => 'REU UPT',
            'color' => '#3b82f6', // blue-500 (and yellow but we'll use blue as primary)
            'logo_url' => 'https://ui-avatars.com/api/?name=REU+UPT&background=3b82f6&color=eab308&size=200',
            'description' => 'RENOVACIÓN UNIVERSITARIA – La mejor organización estudiantil de la UPT te está esperando. Haz nuevos amigos, aprende en grande, vive experiencias únicas, desarrolla tu liderazgo, crece personal y profesionalmente.',
        ]);

        Party::create([
            'election_id' => $election->id,
            'name' => 'FEUT UPT',
            'color' => '#a855f7', // purple-500
            'logo_url' => 'https://ui-avatars.com/api/?name=FEUT+UPT&background=a855f7&color=fff&size=200',
            'description' => 'Nueva lista candidata para el periodo 2026-2027. Promoviendo la participación estudiantil activa en la Universidad Privada de Tacna.',
        ]);
    }
}
