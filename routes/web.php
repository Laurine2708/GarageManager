<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// À la racine, les visiteurs vont à la connexion et les sessions ouvertes au dashboard.
Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'))->name('home');

// L'action choisit les données à transmettre selon le rôle de cette session authentifiée.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

require __DIR__.'/settings.php';
