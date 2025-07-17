<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PixController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rotas públicas de autenticação
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/pix', [PixController::class, 'store']);
    Route::get('/pix', [PixController::class, 'index']); // Rota para listar PIX
    Route::get('/pix/stats', [PixController::class, 'stats']);
});

// Rota pública do PIX
Route::get('/pix/{token}', [PixController::class, 'show'])->name('pix.show'); 