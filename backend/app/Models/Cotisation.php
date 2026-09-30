<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotisation extends Model
{
    protected $table = 'cycles_mensuels';
    protected $fillable = ['mois', 'statut', 'montant_total', 'date_cloture', 'beneficiaire_id'];
    public function cotisations() { return $this->hasMany(Cotisation::class, 'cycle_id'); }
    public function beneficiaire() { return $this->belongsTo(Membre::class, 'beneficiaire_id'); }
}
