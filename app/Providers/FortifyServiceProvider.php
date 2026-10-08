<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

/**
 * Configure Fortify pour utiliser les actions, vues et limites de l'application.
 */
class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les services d'authentification propres à l'application.
     */
    public function register(): void
    {
        //
    }

    /**
     * Configure les actions, pages et limitations de débit de Fortify.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Relie les flux d'inscription et de réinitialisation aux actions applicatives.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Associe les pages d'authentification Fortify aux composants Inertia.
     */
    private function configureViews(): void
    {
        // Fortify conserve l'authentification de session et délègue le rendu du formulaire à Inertia.
        Fortify::loginView(fn (Request $request) => Inertia::render('auth/Login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'status' => $request->session()->get('status'),
        ]));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::registerView(fn () => Inertia::render('auth/Register', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/ConfirmPassword'));
    }

    /**
     * Limite les tentatives de connexion par identifiant normalisé.
     */
    private function configureRateLimiting(): void
    {

        RateLimiter::for('login', function (Request $request) {
            $login = $request->input(Fortify::username());
            $throttleKey = Str::transliterate(Str::lower(is_string($login) ? $login : ''));

            return Limit::perMinute(5)->by($throttleKey);
        });

    }
}
