<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

/**
 * Fournisseurs applicatifs chargés au démarrage de Laravel.
 *
 * AppServiceProvider configure les valeurs communes ; FortifyServiceProvider
 * configure les vues d'authentification et la limitation des connexions.
 *
 * @return array<int, class-string>
 */
return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
];
