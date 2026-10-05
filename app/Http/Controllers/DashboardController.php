<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Models\Rdv;
use App\Models\Utilisateur;
use App\Models\Vehicule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
        * Sélectionne le jeu de données du dashboard selon le rôle stocké sur l'utilisateur connecté.
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        /** @var Utilisateur $user */
        $user = $request->user();
        // Les valeurs de rôle sont normalisées pour accepter les accents sans dupliquer les branches.
        $role = Str::ascii(Str::lower($user->role_utilisateur));

        $data = match ($role) {
            'administrateur' => $this->adminData(),
            'mecanicien' => $this->mechanicData($user),
            'client' => $this->clientData($user),
            default => [],
        };

        return Inertia::render('Dashboard', [
            'role' => $role,
            ...$data,
        ]);
    }

    /** Retourne uniquement les compteurs et interventions des véhicules liés au client. */
    private function clientData(Utilisateur $user): array
    {
        $vehicles = $user->vehicules()->orderBy('marque_vehicule')->get();
        $vehicleIds = $vehicles->modelKeys();

        return [
            // Le périmètre des compteurs reste limité aux véhicules de ce client.
            'stats' => [
                'inProgress' => Intervention::whereIn('id_vehicule', $vehicleIds)
                    ->whereHas('statut', fn ($query) => $query->whereRaw('LOWER(nom_statut) LIKE ?', ['%cours%']))
                    ->count(),
                'completed' => Intervention::whereIn('id_vehicule', $vehicleIds)
                    ->whereHas('statut', fn ($query) => $query->whereRaw('LOWER(nom_statut) LIKE ?', ['%termin%']))
                    ->count(),
            ],
            'interventions' => Intervention::with(['vehicule', 'statut', 'rendezVous.utilisateur'])
                ->whereIn('id_vehicule', $vehicleIds)
                ->orderByDesc('date_depart_intervention')
                ->limit(5)
                ->get()
                ->map(fn (Intervention $intervention) => $this->interventionData($intervention)),
        ];
    }

    /** Retourne les interventions de ce mécanicien et leurs statuts pour les trois compteurs. */
    private function mechanicData(Utilisateur $user): array
    {
        // Le lien hasMany filtre par id_utilisateur; l'eager loading évite une requête par bandeau.
        $interventions = $user->interventions()
            ->with(['vehicule', 'statut', 'rendezVous.utilisateur'])
            ->orderByDesc('date_depart_intervention')
            ->limit(8)
            ->get();

        return [
            'stats' => [
                'toDo' => $user->interventions()
                    ->whereHas('statut', fn ($query) => $query->whereRaw(
                        'LOWER(nom_statut) LIKE ? OR LOWER(nom_statut) LIKE ?',
                        ['%attente%', '%faire%'],
                    ))
                    ->count(),
                'inProgress' => $user->interventions()
                    ->whereHas('statut', fn ($query) => $query->whereRaw('LOWER(nom_statut) LIKE ?', ['%cours%']))
                    ->count(),
                'completed' => $user->interventions()
                    ->whereHas('statut', fn ($query) => $query->whereRaw('LOWER(nom_statut) LIKE ?', ['%termin%']))
                    ->count(),
            ],
            'interventions' => $interventions->map(fn (Intervention $intervention) => $this->interventionData($intervention)),
        ];
    }

    /** Retourne les totaux globaux et les interventions récentes pour l'administrateur. */
    private function adminData(): array
    {
        $toDoInterventions = Intervention::whereHas('statut', fn ($query) => $query->whereRaw(
            'LOWER(nom_statut) LIKE ? OR LOWER(nom_statut) LIKE ?',
            ['%attente%', '%faire%'],
        ));
        $inProgressInterventions = Intervention::whereHas(
            'statut',
            fn ($query) => $query->whereRaw('LOWER(nom_statut) LIKE ?', ['%cours%']),
        );

        return [
            'stats' => [
                'users' => Utilisateur::count(),
                'vehicles' => Vehicule::count(),
                'appointments' => Rdv::count(),
                'interventions' => Intervention::count(),
                'availableMechanics' => Utilisateur::query()
                    ->whereRaw('LOWER(role_utilisateur) = ?', ['mecanicien'])
                    ->whereDoesntHave('interventions', fn ($query) => $query->whereDate(
                        'date_depart_intervention',
                        Carbon::today(),
                    ))
                    ->count(),
                'totalInterventions' => (clone $toDoInterventions)->count() + (clone $inProgressInterventions)->count(),
                'vehiclesInProgress' => (clone $inProgressInterventions)
                    ->distinct('id_vehicule')
                    ->count('id_vehicule'),
                'toDo' => (clone $toDoInterventions)->count(),
                'inProgress' => (clone $inProgressInterventions)->count(),
                'completed' => Intervention::whereHas('statut', fn ($query) => $query->whereRaw('LOWER(nom_statut) LIKE ?', ['%termin%']))->count(),
            ],
            'latestInterventions' => Intervention::with(['vehicule', 'statut', 'rendezVous.utilisateur'])
                ->orderByDesc('date_depart_intervention')
                ->limit(5)
                ->get()
                ->map(fn (Intervention $intervention) => $this->interventionData($intervention)),
        ];
    }

    /** Limite les données véhicule transmises aux seuls champs montrés dans un bandeau. */
    private function vehicleData(Vehicule $vehicle): array
    {
        return [
            'name' => trim($vehicle->marque_vehicule.' '.$vehicle->modele_vehicule),
            'registration' => $vehicle->immatriculation_vehicule,
        ];
    }

    /** Prépare le contrat commun consommé par InterventionBanner.vue. */
    private function interventionData(Intervention $intervention): array
    {
        return [
            'id' => $intervention->id_intervention,
            'description' => $intervention->description_intervention,
            // date_depart_intervention et la date du rendez-vous sont deux champs distincts du schéma.
            'date' => $intervention->date_depart_intervention,
            'appointmentDate' => $intervention->rendezVous?->date_rdv,
            'status' => $intervention->statut?->nom_statut,
            'vehicle' => $intervention->vehicule
                ? $this->vehicleData($intervention->vehicule)
                : null,
            'client' => $intervention->rendezVous?->utilisateur
                ? trim($intervention->rendezVous->utilisateur->nom_utilisateur.' '.$intervention->rendezVous->utilisateur->prenom_utilisateur)
                : null,
        ];
    }
}