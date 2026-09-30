<?php

namespace App\Http\Controllers;

use App\Models\Cotisation;
use App\Models\CycleMensuel;
use App\Models\Membre;
use App\Services\TontineService;
use Illuminate\Http\Request;

class CotisationController extends Controller
{
    public function store(Request $request, TontineService $service)
    {
        $data = $request->validate([
            'membre_id' => 'required|exists:membres,id',
            'mois' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/', 'exists:cycles_mensuels,mois'],
            'periode' => 'required|date_format:Y-m-d',
        ]);

        $cycle = CycleMensuel::where('mois', $data['mois'])->firstOrFail();
        if ($cycle->statut !== 'OUVERT') {
            return response()->json(['message' => 'Ce mois est déjà clôturé.'], 422);
        }

        $membre = Membre::findOrFail($data['membre_id']);

        if (!in_array($data['periode'], $service->echeances($membre, $cycle->mois))) {
            return response()->json([
                'message' => "Cette date n'est pas une échéance valide pour la fréquence du membre.",
            ], 422);
        }

        $existe = Cotisation::where('membre_id', $membre->id)
            ->where('cycle_id', $cycle->id)
            ->where('periode', $data['periode'])
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'Ce membre a déjà cotisé pour cette échéance.'], 409);
        }

        $cotisation = Cotisation::create([
            'membre_id' => $membre->id,
            'cycle_id' => $cycle->id,
            'periode' => $data['periode'],
            'montant' => $service->montantEcheance($membre, $cycle->mois),
            'date_versement' => now(),
        ]);

        return response()->json($cotisation, 201);
    }
}