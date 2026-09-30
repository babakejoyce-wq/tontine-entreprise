<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membre extends Model
{
    protected $fillable = ['nom', 'telephone', 'ordre_tour', 'frequence'];
    public function cotisations() { return $this->hasMany(Cotisation::class); }
}
