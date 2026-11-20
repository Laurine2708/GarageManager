<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rend le choix d'apparence disponible aux vues côté serveur.
 */
class HandleAppearance
{
    /**
     * Partage le thème choisi via cookie avec les vues rendues côté serveur.
     *
     * @param Request $request Requête entrante.
     * @param  Closure(Request): (Response)  $next
     * @return Response Réponse produite par la suite de middlewares.
     */
    public function handle(Request $request, Closure $next): Response
    {
        View::share('appearance', $request->cookie('appearance') ?? 'system');

        return $next($request);
    }
}
