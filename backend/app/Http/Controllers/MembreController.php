<?php

namespace App\Http\Controllers;

use App\Models\Membre;
use Illuminate\Http\Request;

class MembreController extends Controller
{
    public function index()
    {
        return Membre::orderBy('ordre_tour')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:100',
            'telephone' => 'required|string|max:20',
            'ordre_tour' => 'required|integer|min:1|unique:membres,ordre_tour',
            'frequence' => 'required|in:JOURNALIERE,HEBDOMADAIRE,MENSUELLE',
        ]);

        return response()->json(Membre::create($data), 201);
    }
}