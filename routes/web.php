<?php

use Illuminate\Support\Facades\Route;

// Anciens favoris lorsque le panneau était sous /admin
Route::permanentRedirect('/admin/login', '/login');
Route::permanentRedirect('/admin', '/');

// Même information qu’autrefois sur « / », désormais ici pour ne pas masquer Filament.
Route::get('/info', function () {
    return response()->json([
        'app' => 'VWAJEN',
        'status' => 'ok',
        'panel' => url('/'),
        'login' => url('/login'),
        'api' => url('/api/v1/health'),
        'hint' => 'Admin Filament à la racine (/). Ancien chemin /admin redirige ici.',
    ]);
})->name('vwajen.info');
