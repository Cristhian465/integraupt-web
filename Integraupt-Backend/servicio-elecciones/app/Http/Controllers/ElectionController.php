<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\Party;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ElectionController extends Controller
{
    // === ESTUDIANTES ===

    public function activeElection()
    {
        $election = Election::where('is_active', true)->with('parties')->first();
        if (!$election) {
            return response()->json(['message' => 'No hay elecciones activas en este momento'], 404);
        }
        return response()->json($election);
    }

    public function castVote(Request $request, $electionId)
    {
        $request->validate([
            'student_id' => 'required|string',
            'faculty' => 'required|string',
            'asamblea_party_id' => 'nullable|exists:parties,id',
            'consejo_uni_party_id' => 'nullable|exists:parties,id',
            'consejo_fac_party_id' => 'nullable|exists:parties,id',
        ]);

        $election = Election::findOrFail($electionId);
        if (!$election->is_active) {
            return response()->json(['message' => 'Esta elección no está activa'], 400);
        }

        // Verificar si ya votó (comentado para permitir seguir votando en pruebas)
        /*
        $existingVote = Vote::where('election_id', $electionId)
            ->where('student_id', $request->student_id)
            ->first();

        if ($existingVote) {
            return response()->json(['message' => 'Ya has emitido tu voto en esta elección'], 400);
        }
        */

        $vote = Vote::create([
            'election_id' => $electionId,
            'student_id' => $request->student_id . '_' . Str::random(4), // Append random string for testing multiple votes
            'faculty' => $request->faculty,
            'asamblea_party_id' => $request->asamblea_party_id,
            'consejo_uni_party_id' => $request->consejo_uni_party_id,
            'consejo_fac_party_id' => $request->consejo_fac_party_id,
            'verification_code' => strtoupper(Str::random(8)),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Voto registrado exitosamente',
            'verification_code' => $vote->verification_code,
            'vote' => $vote->load('asambleaParty', 'consejoUniParty', 'consejoFacParty')
        ]);
    }

    public function myVote(Request $request, $electionId)
    {
        $request->validate(['student_id' => 'required|string']);
        
        $vote = Vote::where('election_id', $electionId)
            ->where('student_id', $request->student_id)
            ->with(['asambleaParty', 'consejoUniParty', 'consejoFacParty'])
            ->first();

        if (!$vote) {
            return response()->json(['message' => 'No se encontró un voto para este estudiante'], 404);
        }

        return response()->json($vote);
    }

    // === ADMINISTRADORES ===

    public function index()
    {
        $elections = Election::withCount('votes')->get();
        return response()->json($elections);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $election = Election::create($request->all());
        return response()->json($election, 201);
    }

    public function storeParty(Request $request, $electionId)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $party = new Party($request->all());
        $party->election_id = $electionId;
        $party->save();
        
        return response()->json($party, 201);
    }

    public function toggleActive($id)
    {
        $election = Election::findOrFail($id);
        
        if (!$election->is_active) {
            // Desactivar las demás
            Election::where('id', '!=', $id)->update(['is_active' => false]);
        }

        $election->is_active = !$election->is_active;
        $election->save();

        return response()->json($election);
    }

    public function results($id)
    {
        $election = Election::with('parties')->findOrFail($id);
        $totalVotes = Vote::where('election_id', $id)->count();

        $parties = clone $election->parties;
        
        // Add "Voto en Blanco" mock party for calculation
        $blanco = new Party(['id' => null, 'name' => 'Voto en Blanco', 'color' => '#cbd5e1']);
        $parties->push($blanco);

        $results = [
            'asamblea' => [],
            'consejo_uni' => [],
            'consejo_fac' => [] // Grouped by faculty later if needed, here total
        ];

        foreach (['asamblea_party_id' => 'asamblea', 'consejo_uni_party_id' => 'consejo_uni'] as $column => $cargo) {
            $counts = Vote::where('election_id', $id)
                ->select($column, DB::raw('count(*) as total'))
                ->groupBy($column)
                ->pluck('total', $column)->toArray();

            foreach ($parties as $party) {
                $partyId = $party->id;
                // If it's the blank vote, the id in DB is null, which becomes "" in the pluck array keys
                $key = $partyId === null ? "" : $partyId;
                
                $votes = $counts[$key] ?? 0;
                $results[$cargo][] = [
                    'id' => $partyId,
                    'name' => $party->name,
                    'color' => $party->color,
                    'votes' => $votes,
                    'percentage' => $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 2) : 0
                ];
            }
        }

        // Process consejo_fac by faculty and overall
        $faculties = Vote::where('election_id', $id)->whereNotNull('faculty')->distinct()->pluck('faculty');
        
        $results['consejo_fac_totals'] = [];
        $countsTotal = Vote::where('election_id', $id)
            ->select('consejo_fac_party_id', DB::raw('count(*) as total'))
            ->groupBy('consejo_fac_party_id')
            ->pluck('total', 'consejo_fac_party_id')->toArray();
            
        foreach ($parties as $party) {
            $partyId = $party->id;
            $key = $partyId === null ? "" : $partyId;
            $votes = $countsTotal[$key] ?? 0;
            $results['consejo_fac_totals'][] = [
                'id' => $partyId,
                'name' => $party->name,
                'color' => $party->color,
                'votes' => $votes,
                'percentage' => $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 2) : 0
            ];
        }

        $results['consejo_fac'] = [];
        foreach ($faculties as $faculty) {
            $facultyTotalVotes = Vote::where('election_id', $id)->where('faculty', $faculty)->count();
            
            $countsFac = Vote::where('election_id', $id)
                ->where('faculty', $faculty)
                ->select('consejo_fac_party_id', DB::raw('count(*) as total'))
                ->groupBy('consejo_fac_party_id')
                ->pluck('total', 'consejo_fac_party_id')->toArray();
            
            $facultyResults = [];
            foreach ($parties as $party) {
                $partyId = $party->id;
                $key = $partyId === null ? "" : $partyId;
                $votes = $countsFac[$key] ?? 0;
                $facultyResults[] = [
                    'id' => $partyId,
                    'name' => $party->name,
                    'color' => $party->color,
                    'votes' => $votes,
                    'percentage' => $facultyTotalVotes > 0 ? round(($votes / $facultyTotalVotes) * 100, 2) : 0
                ];
            }
            
            $results['consejo_fac'][$faculty] = [
                'total_votes' => $facultyTotalVotes,
                'results' => $facultyResults
            ];
        }

        return response()->json([
            'election' => $election,
            'total_votes' => $totalVotes,
            'results' => $results
        ]);
    }
}
