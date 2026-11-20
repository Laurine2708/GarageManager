<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Models\Tarif;
use App\Models\Utilisateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/** Affiche et met à jour les tarifs horaires de main-d'œuvre. */
class TarifMOController extends Controller
{
    /** Affiche les tarifs et les coûts des interventions non terminées. */
    public function index(Request $request): Response
    {
        $this->authorizeAdministrator($request);

        $interventions = Intervention::query()
            ->with(['tarif', 'statut', 'rendezVous.utilisateur'])
            ->whereHas('statut', fn ($query) => $query->whereIn('nom_statut', ['À faire', 'En cours']))
            ->orderByDesc('date_depart_intervention')
            ->get()
            ->map(function (Intervention $intervention): array {
                $rate = (float) ($intervention->tarif?->montant_tarif ?? 0);
                $minutes = (int) $intervention->temps_intervention;

                return [
                    'id' => $intervention->id_intervention,
                    'description' => $intervention->description_intervention,
                    'status' => $intervention->statut?->nom_statut,
                    'client' => $intervention->rendezVous?->utilisateur
                        ? trim(Str::upper($intervention->rendezVous->utilisateur->nom_utilisateur).' '.$intervention->rendezVous->utilisateur->prenom_utilisateur)
                        : null,
                    'date' => $intervention->rendezVous?->date_rdv
                        ? date('d/m/Y', strtotime($intervention->rendezVous->date_rdv))
                        : null,
                    'departureDate' => $intervention->date_depart_intervention
                        ? date('d/m/Y', strtotime($intervention->date_depart_intervention))
                        : null,
                    'minutes' => $minutes,
                    'tarif' => $intervention->tarif?->libelle_tarif,
                    'hourlyRate' => $rate,
                    'estimatedCost' => round($minutes * $rate / 60, 2),
                ];
            })
            ->values();

        return Inertia::render('LaborRates', [
            'role' => 'administrateur',
            'tariffs' => Tarif::query()->orderBy('id_tarif')->get()->map(fn (Tarif $tarif): array => [
                'id' => $tarif->id_tarif,
                'label' => $tarif->libelle_tarif,
                'amount' => (float) $tarif->montant_tarif,
            ])->values(),
            'interventions' => $interventions,
        ]);
    }

    /** Met à jour le prix horaire du tarif sélectionné. */
    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdministrator($request);

        $validated = $request->validate([
            'tarifId' => ['required', 'integer', Rule::exists('tarif', 'id_tarif')],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
        ]);

        Tarif::query()
            ->whereKey($validated['tarifId'])
            ->update(['montant_tarif' => $validated['amount']]);

        return to_route('labor-rates.index');
    }

    /** Réserve la gestion des tarifs aux administrateurs. */
    private function authorizeAdministrator(Request $request): Utilisateur
    {
        $user = $request->user();
        $role = $user instanceof Utilisateur ? Str::ascii(Str::lower($user->role_utilisateur)) : '';

        abort_unless($user instanceof Utilisateur && $role === 'administrateur', 403);

        return $user;
    }
}
