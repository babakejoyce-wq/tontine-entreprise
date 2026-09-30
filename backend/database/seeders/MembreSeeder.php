<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MembreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    \App\Models\Membre::insert([
        ['nom' => 'Kofi',  'telephone' => '90000001', 'ordre_tour' => 1, 'frequence' => 'MENSUELLE',    'created_at' => now(), 'updated_at' => now()],
        ['nom' => 'Ama',   'telephone' => '90000002', 'ordre_tour' => 2, 'frequence' => 'HEBDOMADAIRE', 'created_at' => now(), 'updated_at' => now()],
        ['nom' => 'Yao',   'telephone' => '90000003', 'ordre_tour' => 3, 'frequence' => 'JOURNALIERE',  'created_at' => now(), 'updated_at' => now()],
    ]);
}
}
