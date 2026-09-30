<?php
use App\Http\Controllers\CotisationController;
use App\Http\Controllers\CycleController;
use App\Http\Controllers\MembreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/membres', [MembreController::class, 'index']);
Route::post('/membres', [MembreController::class, 'store']);

Route::get('/cycles', [CycleController::class, 'index']);
Route::post('/cycles', [CycleController::class, 'store']);
Route::get('/cycles/{mois}/statut', [CycleController::class, 'statut'])->where('mois', '\d{4}-\d{2}');
Route::post('/cycles/{mois}/designer', [CycleController::class, 'designer'])->where('mois', '\d{4}-\d{2}');

Route::post('/cotisations', [CotisationController::class, 'store']);