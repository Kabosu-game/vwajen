<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'VWAJEN',
        'status' => 'ok',
        'admin' => url('/admin'),
        'api' => url('/api/v1/health'),
    ]);
});
