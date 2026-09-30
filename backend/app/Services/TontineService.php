<?php

namespace App\Services;

use App\Models\CycleMensuel;
use App\Models\Membre;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class TontineService
{
    // Dates d'échéance valides pour un membre sur un mois donné (ex. "2026-10")
    public function echeances(Membre $membre, string $mois): array
    {
        $debut = Carbon::createFromFormat('Y-m-d', $mois . '-01')->startOfDay();
        $fin = $debut->copy()->endOfMonth();

        if ($membre->frequence === 'MENSUELLE') {
            return [$debut->toDateString()];
        }

        $dates = [];
        foreach (CarbonPeriod::create($debut, $fin) as $jour) {
            if ($membre->frequence === 'JOURNALIERE' && $jour->isWeekday()) {
                $dates[] = $jour->toDateString();
            }
            if ($membre->frequence === 'HEBDOMADAIRE' && $jour->isMonday()) {
                $dates[] = $jour->toDateString();
            }
        }
        return $dates;
    }

    // Montant d'une échéance (arrondi au supérieur pour que la somme atteigne bien le montant mensuel)
    public function montantEcheance(Membre $membre, string $mois): int
    {
        $nb = count($this->echeances($membre, $mois));
        return (int) ceil(config('tontine.montant_mensuel') / $nb);
    }

    // État de chaque membre pour le cycle : payé, dû, en règle ou non
    public function detail(CycleMensuel $cycle): Collection
{
    $du = config('tontine.montant_mensuel');
    $cotisations = $cycle->cotisations()->get()->groupBy('membre_id');

    return Membre::orderBy('ordre_tour')->get()->map(function ($m) use ($cotisations, $cycle, $du) {
        $lignes = $cotisations->get($m->id, collect());
        $paye = (float) $lignes->sum('montant');
        $datesPayees = $lignes->map(fn ($c) => Carbon::parse($c->periode)->toDateString())->all();

        return [
            'id' => $m->id,
            'nom' => $m->nom,
            'frequence' => $m->frequence,
            'paye' => $paye,
            'du' => $du,
            'en_regle' => $paye >= $du,
            'montant_echeance' => $this->montantEcheance($m, $cycle->mois),
            'echeances' => collect($this->echeances($m, $cycle->mois))
                ->map(fn ($d) => ['date' => $d, 'payee' => in_array($d, $datesPayees)])
                ->all(),
        ];
    });
}
    // LA règle de blocage/déblocage : tous les membres doivent être en règle
    public function peutDesigner(Collection $detail): bool
    {
        return $detail->isNotEmpty() && $detail->every(fn ($m) => $m['en_regle']);
    }

    // Bénéficiaire selon l'ordre de passage (recommence au début après un tour complet)
    public function prochainBeneficiaire(): Membre
    {
        $membres = Membre::orderBy('ordre_tour')->get()->values();
        $nbDistribues = CycleMensuel::where('statut', 'DISTRIBUE')->count();
        return $membres->get($nbDistribues % $membres->count());
    }
}