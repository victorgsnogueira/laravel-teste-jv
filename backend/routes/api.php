<?php

use App\Http\Controllers\PixController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/pix', [PixController::class, 'store']);
    Route::get('/pix/stats', [PixController::class, 'index']);
});

Route::get('/pix/{token}', [PixController::class, 'show'])->name('pix.show'); 