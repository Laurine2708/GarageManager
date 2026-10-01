<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppointmentsController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

// À la racine, les visiteurs vont à la connexion et les sessions ouvertes au dashboard.
Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'))->name('home');

// L'action choisit les données à transmettre selon le rôle de cette session authentifiée.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::post('rendez-vous', [AppointmentsController::class, 'store'])->name('appointments.store');
    Route::get('utilisateurs', UsersController::class)->name('users.index');
    Route::get('utilisateurs/create', [UsersController::class, 'create'])->name('users.create');
    Route::post('utilisateurs', [UsersController::class, 'store'])->name('users.store');
    // La consultation est distincte de la modification et reste protégée par le contrôleur.
    Route::get('utilisateurs/{id}', [UsersController::class, 'show'])
        ->whereNumber('id')
        ->name('users.show');
    Route::get('utilisateurs/{id}/edit', [UsersController::class, 'edit'])
        ->whereNumber('id')
        ->name('users.edit');
    Route::put('utilisateurs/{id}', [UsersController::class, 'update'])
        ->whereNumber('id')
        ->name('users.update');
    Route::get('mon-profil', [UsersController::class, 'editProfile'])->name('client.profile.edit');
    Route::put('mon-profil', [UsersController::class, 'updateProfile'])->name('client.profile.update');
    Route::delete('utilisateurs/{id}', [UsersController::class, 'destroy'])
        ->whereNumber('id')
        ->name('users.destroy');
});

require __DIR__.'/settings.php';
