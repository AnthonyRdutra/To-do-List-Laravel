<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;
use App\Http\Middleware\TokenAuthMiddleware;

Route::post('/singup', [AuthController::class, 'singup']);
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware(TokenAuthMiddleware::class)->group(function () {
    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::put('/notes/{id}', [NoteController::class, 'update']);
    Route::delete('/notes/{id}', [NoteController::class, 'destroy']);
});

