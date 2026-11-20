<?php

namespace Tests\Feature;

use App\Models\Intervention;
use App\Models\Rdv;
use App\Models\Statut;
use App\Models\Tarif;
use App\Models\Utilisateur;
use App\Models\Vehicule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/** Vérifie la configuration des tarifs horaires et leur affichage sur les interventions. */
class LaborRatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_sees_tariffs_and_estimated_costs_for_unfinished_interventions(): void
    {
        $administrator = $this->createUser('administrateur', 'admin');
        $client = $this->createUser('client', 'client');
        $vehicle = $this->createVehicle();
        $tariff = Tarif::create(['libelle_tarif' => 'T1', 'montant_tarif' => 50]);
        $appointment = Rdv::create([
            'date_rdv' => '2026-10-05 09:30:00',
            'motif_rdv' => 'Révision annuelle',
            'id_vehicule' => $vehicle->id_vehicule,
            'id_utilisateur' => $client->id_utilisateur,
        ]);
        $unfinishedStatus = Statut::create(['nom_statut' => 'À faire']);
        $completedStatus = Statut::create(['nom_statut' => 'Terminée']);

        $unfinished = $this->createIntervention($appointment, $vehicle, $tariff, $unfinishedStatus, 90);
        $this->createIntervention($appointment, $vehicle, $tariff, $completedStatus, 60);

        $this->actingAs($administrator)
            ->get(route('labor-rates.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('LaborRates')
                ->where('tariffs.0.label', 'T1')
                ->where('tariffs.0.amount', 50)
                ->has('interventions', 1)
                ->where('interventions.0.id', $unfinished->id_intervention)
                ->where('interventions.0.client', 'CLIENT Client')
                ->where('interventions.0.departureDate', '05/10/2026')
                ->where('interventions.0.minutes', 90)
                ->where('interventions.0.hourlyRate', 50)
                ->where('interventions.0.estimatedCost', 75));
    }

    public function test_administrator_can_update_a_tariff_amount(): void
    {
        $administrator = $this->createUser('administrateur', 'admin');
        $tariff = Tarif::create(['libelle_tarif' => 'T2', 'montant_tarif' => 75]);

        $this->actingAs($administrator)
            ->put(route('labor-rates.update'), [
                'tarifId' => $tariff->id_tarif,
                'amount' => '82.50',
            ])
            ->assertRedirect(route('labor-rates.index'));

        $this->assertDatabaseHas('tarif', [
            'id_tarif' => $tariff->id_tarif,
            'montant_tarif' => 82.50,
        ]);
    }

    public function test_non_administrator_cannot_manage_labor_rates(): void
    {
        $mechanic = $this->createUser('mecanicien', 'mechanic');
        $tariff = Tarif::create(['libelle_tarif' => 'T1', 'montant_tarif' => 50]);

        $this->actingAs($mechanic)
            ->get(route('labor-rates.index'))
            ->assertForbidden();

        $this->put(route('labor-rates.update'), [
            'tarifId' => $tariff->id_tarif,
            'amount' => 80,
        ])->assertForbidden();
    }

    private function createUser(string $role, string $login): Utilisateur
    {
        return Utilisateur::create([
            'nom_utilisateur' => strtoupper($login),
            'prenom_utilisateur' => ucfirst($login),
            'adresse_utilisateur' => '10 rue du Garage',
            'CP_utilisateur' => 87000,
            'ville_utilisateur' => 'Limoges',
            'login_utilisateur' => $login,
            'mdp_utilisateur' => 'password',
            'role_utilisateur' => $role,
        ]);
    }

    private function createVehicle(): Vehicule
    {
        return Vehicule::create([
            'marque_vehicule' => 'Peugeot',
            'modele_vehicule' => '308',
            'immatriculation_vehicule' => 'AB-123-CD',
            'date_mec_vehicule' => '2020-06-15',
            'motorisation_vehicule' => '1.2 PureTech 130',
            'vin_vehicule' => 'VF3ABCD1234567890',
            'code_moteur_vehicule' => 'EB2ADTS',
        ]);
    }

    private function createIntervention(
        Rdv $appointment,
        Vehicule $vehicle,
        Tarif $tariff,
        Statut $status,
        int $minutes,
    ): Intervention {
        return Intervention::create([
            'description_intervention' => 'Révision du véhicule',
            'temps_intervention' => $minutes,
            'date_depart_intervention' => '2026-10-05',
            'id_rdv' => $appointment->id_rdv,
            'id_tarif' => $tariff->id_tarif,
            'id_utilisateur' => $appointment->id_utilisateur,
            'id_statut' => $status->id_statut,
            'id_vehicule' => $vehicle->id_vehicule,
        ]);
    }
}
