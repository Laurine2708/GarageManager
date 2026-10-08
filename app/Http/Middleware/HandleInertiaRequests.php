<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Configure la vue racine et les propriétés partagées des réponses Inertia.
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * Vue racine chargée lors de la première visite Inertia.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Retourne la version des ressources calculée par le middleware parent.
     *
     * @see https://inertiajs.com/asset-versioning
     *
     * @param Request $request Requête dont la version doit être déterminée.
     * @return string|null Version ou valeur nulle si elle n'est pas définie.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Définit les propriétés partagées avec toutes les pages Inertia.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @param Request $request Requête courante.
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
