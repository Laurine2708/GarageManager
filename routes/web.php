<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppointmentsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VehiclesController;
use Illuminate\Support\Facades\Route;

// À la racine, les visiteurs vont à la connexion et les sessions ouvertes au dashboard.
Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'))->name('home');

// L'action choisit les données à transmettre selon le rôle de cette session authentifiée.
// Les routes métier exigent une session active et une adresse vérifiée.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('rendez-vous', [AppointmentsController::class, 'index'])->name('appointments.index');
    Route::get('rendez-vous/create', [AppointmentsController::class, 'create'])->name('appointments.create');
    Route::post('rendez-vous', [AppointmentsController::class, 'store'])->name('appointments.store');
    Route::get('rendez-vous/{id}', [AppointmentsController::class, 'show'])
        ->whereNumber('id')
        ->name('appointments.show');
    Route::get('rendez-vous/{id}/edit', [AppointmentsController::class, 'edit'])
        ->whereNumber('id')
        ->name('appointments.edit');
    Route::put('rendez-vous/{id}', [AppointmentsController::class, 'update'])
        ->whereNumber('id')
        ->name('appointments.update');
    Route::delete('rendez-vous/{id}', [AppointmentsController::class, 'destroy'])
        ->whereNumber('id')
        ->name('appointments.destroy');
    Route::get('utilisateurs', UsersController::class)->name('users.index');
    Route::get('vehicules', [VehiclesController::class, 'index'])->name('vehicles.index');
    Route::post('vehicules', [VehiclesController::class, 'store'])->name('vehicles.store');
    Route::get('vehicules/{id}', [VehiclesController::class, 'show'])
        ->whereNumber('id')
        ->name('vehicles.show');
    Route::get('vehicules/{id}/edit', [VehiclesController::class, 'edit'])
        ->whereNumber('id')
        ->name('vehicles.edit');
    Route::put('vehicules/{id}', [VehiclesController::class, 'update'])
        ->whereNumber('id')
        ->name('vehicles.update');
    Route::delete('vehicules/{id}', [VehiclesController::class, 'destroy'])
        ->whereNumber('id')
        ->name('vehicles.destroy');
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
