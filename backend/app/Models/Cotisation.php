<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotisation extends Model
{
    protected $fillable = ['membre_id', 'cycle_id', 'periode', 'montant', 'date_versement'];
    public function membre() { return $this->belongsTo(Membre::class); }
    public function cycle() { return $this->belongsTo(CycleMensuel::class, 'cycle_id'); }
}
