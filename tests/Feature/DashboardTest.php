<?php

namespace Tests\Feature;

use App\Models\Intervention;
use App\Models\Rdv;
use App\Models\Statut;
use App\Models\Tarif;
use App\Models\Utilisateur;
use App\Models\Vehicule;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/** Vérifie la protection du tableau de bord et ses données selon le rôle connecté. */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = Utilisateur::create([
            'nom_utilisateur' => 'Dupont',
            'prenom_utilisateur' => 'Jean',
            'adresse_utilisateur' => '10 rue de la République',
            'CP_utilisateur' => 87000,
            'ville_utilisateur' => 'Limoges',
            'login_utilisateur' => 'jean.dupont',
            'mdp_utilisateur' => 'password',
            'role_utilisateur' => 'administrateur',
        ]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('role', 'administrateur')
            ->where('stats.users', 1));
    }

    public function test_mechanic_dashboard_counts_only_its_interventions_by_status()
    {
        $this->travelTo(Carbon::parse('2026-10-05 12:00:00'));

        $mechanic = Utilisateur::create([
            'nom_utilisateur' => 'Leroy',
            'prenom_utilisateur' => 'Pierre',
            'adresse_utilisateur' => '15 rue des Écoles',
            'CP_utilisateur' => 87000,
            'ville_utilisateur' => 'Limoges',
            'login_utilisateur' => 'pierre.leroy',
            'mdp_utilisateur' => 'password',
            'role_utilisateur' => 'mecanicien',
        ]);

        $otherMechanic = Utilisateur::create([
            'nom_utilisateur' => 'Martin',
            'prenom_utilisateur' => 'Sophie',
            'adresse_utilisateur' => '25 avenue Jean Jaurès',
            'CP_utilisateur' => 87000,
            'ville_utilisateur' => 'Limoges',
            'login_utilisateur' => 'sophie.martin',
            'mdp_utilisateur' => 'password',
            'role_utilisateur' => 'mecanicien',
        ]);

        $administrator = Utilisateur::create([
            'nom_utilisateur' => 'Administrateur',
            'prenom_utilisateur' => 'Garage',
            'adresse_utilisateur' => '1 rue du Garage',
            'CP_utilisateur' => 87000,
            'ville_utilisateur' => 'Limoges',
            'login_utilisateur' => 'garage.admin',
            'mdp_utilisateur' => 'password',
            'role_utilisateur' => 'administrateur',
        ]);

        $vehicle = Vehicule::create([
            'marque_vehicule' => 'Peugeot',
            'modele_vehicule' => '308',
            'immatriculation_vehicule' => 'AB-123-CD',
            'date_mec_vehicule' => '2020-06-15',
            'motorisation_vehicule' => '1.2 PureTech 130',
            'vin_vehicule' => 'VF3ABCD1234567890',
            'code_moteur_vehicule' => 'EB2ADTS',
        ]);
        $tariff = Tarif::create([
            'libelle_tarif' => 'MO',
            'montant_tarif' => 55,
        ]);
        $appointment = Rdv::create([
            'date_rdv' => '2026-10-05 09:00:00',
            'motif_rdv' => 'Révision',
            'id_vehicule' => $vehicle->id_vehicule,
            'id_utilisateur' => $mechanic->id_utilisateur,
        ]);

        foreach (['À faire', 'En cours', 'Terminée'] as $statusName) {
            $status = Statut::create(['nom_statut' => $statusName]);

            Intervention::create([
                'description_intervention' => 'Contrôle du véhicule',
                'temps_intervention' => 1,
                'date_depart_intervention' => '2026-10-05',
                'id_rdv' => $appointment->id_rdv,
                'id_tarif' => $tariff->id_tarif,
                'id_utilisateur' => $mechanic->id_utilisateur,
                'id_statut' => $status->id_statut,
                'id_vehicule' => $vehicle->id_vehicule,
            ]);
        }

        $legacyStatus = Statut::create(['nom_statut' => 'En attente']);
        Intervention::create([
            'description_intervention' => 'Ancien statut à exclure',
            'temps_intervention' => 1,
            'date_depart_intervention' => '2026-10-05',
            'id_rdv' => $appointment->id_rdv,
            'id_tarif' => $tariff->id_tarif,
            'id_utilisateur' => $mechanic->id_utilisateur,
            'id_statut' => $legacyStatus->id_statut,
            'id_vehicule' => $vehicle->id_vehicule,
        ]);

        $otherStatus = Statut::create(['nom_statut' => 'En cours']);
        Intervention::create([
            'description_intervention' => 'Intervention d’un autre mécanicien',
            'temps_intervention' => 1,
            'date_depart_intervention' => '2026-10-05',
            'id_rdv' => $appointment->id_rdv,
            'id_tarif' => $tariff->id_tarif,
            'id_utilisateur' => $otherMechanic->id_utilisateur,
            'id_statut' => $otherStatus->id_statut,
            'id_vehicule' => $vehicle->id_vehicule,
        ]);

        $this->actingAs($mechanic)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('stats.toDo', 1)
                ->where('stats.inProgress', 1)
                ->where('stats.completed', 1)
                ->has('interventions', 2));

        $this->actingAs($administrator)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('stats.availableMechanics', 0)
                ->where('stats.totalInterventions', 3)
                ->where('stats.vehiclesInProgress', 1)
                ->where('stats.toDo', 1)
                ->where('stats.inProgress', 2)
                ->where('stats.completed', 1));
    }

    public function test_client_status_counts_include_only_interventions_for_owned_vehicles()
    {
        $client = Utilisateur::create([
            'nom_utilisateur' => 'Dupont',
            'prenom_utilisateur' => 'Jean',
            'adresse_utilisateur' => '10 rue de la République',
            'CP_utilisateur' => 87000,
            'ville_utilisateur' => 'Limoges',
            'login_utilisateur' => 'jean.dupont',
            'mdp_utilisateur' => 'password',
            'role_utilisateur' => 'client',
        ]);
        $mechanic = Utilisateur::create([
            'nom_utilisateur' => 'Leroy',
            'prenom_utilisateur' => 'Pierre',
            'adresse_utilisateur' => '15 rue des Écoles',
            'CP_utilisateur' => 87000,
            'ville_utilisateur' => 'Limoges',
            'login_utilisateur' => 'pierre.leroy',
            'mdp_utilisateur' => 'password',
            'role_utilisateur' => 'mecanicien',
        ]);

        $createVehicle = function (string $registration): Vehicule {
            return Vehicule::create([
                'marque_vehicule' => 'Peugeot',
                'modele_vehicule' => '308',
                'immatriculation_vehicule' => $registration,
                'date_mec_vehicule' => '2020-06-15',
                'motorisation_vehicule' => '1.2 PureTech 130',
                'vin_vehicule' => 'VIN-'.$registration,
                'code_moteur_vehicule' => 'EB2ADTS',
            ]);
        };

        $ownedVehicle = $createVehicle('AB-123-CD');
        $unrelatedVehicle = $createVehicle('EF-456-GH');
        $client->vehicules()->attach($ownedVehicle->id_vehicule);

        $status = Statut::create(['nom_statut' => 'Terminée']);
        $tariff = Tarif::create([
            'libelle_tarif' => 'MO',
            'montant_tarif' => 55,
        ]);

        foreach ([$ownedVehicle, $unrelatedVehicle] as $vehicle) {
            $appointment = Rdv::create([
                'date_rdv' => '2026-10-05 09:00:00',
                'motif_rdv' => 'Révision',
                'id_vehicule' => $vehicle->id_vehicule,
                'id_utilisateur' => $client->id_utilisateur,
            ]);

            Intervention::create([
                'description_intervention' => 'Révision du véhicule',
                'temps_intervention' => 1,
                'date_depart_intervention' => '2026-10-05',
                'id_rdv' => $appointment->id_rdv,
                'id_tarif' => $tariff->id_tarif,
                'id_utilisateur' => $mechanic->id_utilisateur,
                'id_statut' => $status->id_statut,
                'id_vehicule' => $vehicle->id_vehicule,
            ]);
        }

        $this->actingAs($client)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('stats.completed', 1));
    }
}
