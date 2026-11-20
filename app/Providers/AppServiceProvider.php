<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

/**
 * Enregistre les services et valeurs par défaut partagés par l'application.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les services propres à l'application dans le conteneur.
     */
    public function register(): void
    {
        //
    }

    /**
     * Initialise les comportements globaux après l'enregistrement des services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure les comportements globaux de dates, de base de données et de mots de passe.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
