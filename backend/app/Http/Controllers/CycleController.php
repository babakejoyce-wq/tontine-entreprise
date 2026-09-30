<?php

namespace App\Http\Controllers;

use App\Models\CycleMensuel;
use App\Services\TontineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CycleController extends Controller
{
    // Historique (filtrable : /api/cycles?statut=DISTRIBUE)
    public function index(Request $request)
    {
        return CycleMensuel::with('beneficiaire')
            ->when($request->statut, fn ($q, $s) => $q->where('statut', $s))
            ->orderByDesc('mois')
            ->get();
    }

    // Étape 1 : ouverture du mois
    public function store(Request $request)
    {
        $data = $request->validate([
            'mois' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/', 'unique:cycles_mensuels,mois'],
        ]);

        if (CycleMensuel::where('statut', 'OUVERT')->exists()) {
            return response()->json(['message' => 'Un mois est déjà ouvert. Clôturez-le d\'abord.'], 422);
        }

        return response()->json(CycleMensuel::create($data), 201);
    }

    // Qui a cotisé, qui manque, et est-ce débloqué ?
    public function statut(string $mois, TontineService $service)
    {
    $cycle = CycleMensuel::where('mois', $mois)->firstOrFail();
    $detail = $service->detail($cycle);

    return response()->json([
        'mois' => $cycle->mois,
        'statut' => $cycle->statut,
        'membres' => $detail,
        'peut_designer' => $cycle->statut === 'OUVERT' && $service->peutDesigner($detail),
        'beneficiaire_prevu' => $detail->isEmpty() ? null : (
            $cycle->statut === 'OUVERT'
                ? $service->prochainBeneficiaire()->only(['id', 'nom', 'ordre_tour'])
                : $cycle->beneficiaire?->only(['id', 'nom', 'ordre_tour'])
        ),
    ]);
    }

    // Étapes 4, 5, 6 : blocage/déblocage, désignation, clôture
    public function designer(string $mois, TontineService $service)
    {
        $cycle = CycleMensuel::where('mois', $mois)->firstOrFail();

        if ($cycle->statut === 'DISTRIBUE') {
            return response()->json(['message' => 'Ce mois est déjà distribué.'], 422);
        }

        $detail = $service->detail($cycle);

        if (!$service->peutDesigner($detail)) {
            return response()->json([
                'message' => 'Désignation impossible : des cotisations manquent.',
                'manquants' => $detail->where('en_regle', false)->pluck('nom')->values(),
            ], 422);
        }

        DB::transaction(function () use ($cycle, $service) {
            $cycle->update([
                'beneficiaire_id' => $service->prochainBeneficiaire()->id,
                'montant_total' => $cycle->cotisations()->sum('montant'),
                'statut' => 'DISTRIBUE',
                'date_cloture' => now()->toDateString(),
            ]);
        });

        return response()->json($cycle->load('beneficiaire'));
    }
}