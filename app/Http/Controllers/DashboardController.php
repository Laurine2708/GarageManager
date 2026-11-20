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

/**
 * Prépare les indicateurs et les interventions du tableau de bord selon le rôle.
 */
class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord avec les données adaptées au rôle de l'utilisateur connecté.
     *
     * @param Request $request Requête authentifiée contenant l'utilisateur courant.
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

    /**
     * Prépare les compteurs et les interventions des seuls véhicules du client.
     *
     * @return array<string, mixed> Données attendues par la page du tableau de bord.
     */
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

    /**
     * Prépare les interventions assignées au mécanicien et ses compteurs par statut.
     *
     * @return array<string, mixed> Données attendues par la page du tableau de bord.
     */
    private function mechanicData(Utilisateur $user): array
    {
        // Le lien hasMany filtre par id_utilisateur; l'eager loading évite une requête par bandeau.
        $interventions = $user->interventions()
            ->with(['vehicule', 'statut', 'rendezVous.utilisateur'])
            ->whereHas('statut', fn ($query) => $query->whereIn('nom_statut', ['À faire', 'En cours']))
            ->orderByDesc('date_depart_intervention')
            ->limit(8)
            ->get();

        return [
            'stats' => [
                'toDo' => $user->interventions()
                    ->whereHas('statut', fn ($query) => $query->where('nom_statut', 'À faire'))
                    ->count(),
                'inProgress' => $user->interventions()
                    ->whereHas('statut', fn ($query) => $query->where('nom_statut', 'En cours'))
                    ->count(),
                'completed' => $user->interventions()
                    ->whereHas('statut', fn ($query) => $query->where('nom_statut', 'Terminée'))
                    ->count(),
            ],
            'interventions' => $interventions->map(fn (Intervention $intervention) => $this->interventionData($intervention)),
        ];
    }

    /**
     * Prépare les statistiques globales et les dernières interventions de l'administrateur.
     *
     * @return array<string, mixed> Données attendues par la page du tableau de bord.
     */
    private function adminData(): array
    {
        $toDoInterventions = Intervention::whereHas(
            'statut',
            fn ($query) => $query->where('nom_statut', 'À faire'),
        );
        $inProgressInterventions = Intervention::whereHas(
            'statut',
            fn ($query) => $query->where('nom_statut', 'En cours'),
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
                'completed' => Intervention::whereHas('statut', fn ($query) => $query->where('nom_statut', 'Terminée'))->count(),
            ],
            'latestInterventions' => Intervention::with(['vehicule', 'statut', 'rendezVous.utilisateur'])
                ->orderByDesc('date_depart_intervention')
                ->limit(5)
                ->get()
                ->map(fn (Intervention $intervention) => $this->interventionData($intervention)),
        ];
    }

    /**
     * Réduit un véhicule aux informations affichées dans un bandeau d'intervention.
     *
     * @return array{name: string, registration: string}
     */
    private function vehicleData(Vehicule $vehicle): array
    {
        return [
            'name' => trim($vehicle->marque_vehicule.' '.$vehicle->modele_vehicule),
            'registration' => $vehicle->immatriculation_vehicule,
        ];
    }

    /**
     * Convertit une intervention au format commun utilisé par le bandeau.
     *
     * La date de départ et la date du rendez-vous restent deux informations distinctes.
     *
     * @return array<string, mixed>
     */
    private function interventionData(Intervention $intervention): array
    {
        return [
            'id' => $intervention->id_intervention,
            'description' => $intervention->description_intervention,
            'date' => $intervention->date_depart_intervention,
            'appointmentDate' => $intervention->rendezVous?->date_rdv,
            'status' => $intervention->statut?->nom_statut,
            'vehicle' => $intervention->vehicule
                ? $this->vehicleData($intervention->vehicule)
                : null,
            'client' => $intervention->rendezVous?->utilisateur
                ? trim(Str::upper($intervention->rendezVous->utilisateur->nom_utilisateur).' '.$intervention->rendezVous->utilisateur->prenom_utilisateur)
                : null,
        ];
    }
}