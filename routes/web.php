<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Identity\AuthController;

// Pagina de inicio (Login)
Route::get('/', function () { return view('auth.login'); })->name('login');

// Proceso de autenticacion
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

// Dashboard (Protegido por middleware de autenticacion)
Route::get('/dashboard', function () {
    return view('dashboard.index');
})->middleware('auth')->name('dashboard');
