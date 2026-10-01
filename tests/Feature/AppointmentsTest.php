<?php

namespace Tests\Feature;

use App\Models\Rdv;
use App\Models\Utilisateur;
use App\Models\Vehicule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_an_appointment_for_a_clients_vehicle(): void
    {
        $administrator = $this->createUtilisateur('administrateur');
        $client = $this->createUtilisateur('client');
        $vehicle = $this->createVehicle();
        $client->vehicules()->attach($vehicle->id_vehicule);

        $this->actingAs($administrator)
            ->post(route('appointments.store'), [
                'clientId' => $client->id_utilisateur,
                'vehicleId' => $vehicle->id_vehicule,
                'appointmentDate' => '2026-10-05T09:30',
                'reason' => 'Révision annuelle',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('rdv', [
            'date_rdv' => '2026-10-05 09:30:00',
            'motif_rdv' => 'Révision annuelle',
            'id_vehicule' => $vehicle->id_vehicule,
            'id_utilisateur' => $client->id_utilisateur,
        ]);
    }

    public function test_an_appointment_cannot_use_a_vehicle_not_owned_by_the_selected_client(): void
    {
        $administrator = $this->createUtilisateur('administrateur');
        $client = $this->createUtilisateur('client');
        $vehicle = $this->createVehicle();

        $this->actingAs($administrator)
            ->from(route('dashboard'))
            ->post(route('appointments.store'), [
                'clientId' => $client->id_utilisateur,
                'vehicleId' => $vehicle->id_vehicule,
                'appointmentDate' => '2026-10-05T09:30',
                'reason' => 'Révision annuelle',
            ])
            ->assertSessionHasErrors('vehicleId');

        $this->assertSame(0, Rdv::count());
    }

    public function test_non_administrator_cannot_create_an_appointment(): void
    {
        $mechanic = $this->createUtilisateur('mecanicien');

        $this->actingAs($mechanic)
            ->post(route('appointments.store'), [])
            ->assertForbidden();
    }

    private function createUtilisateur(string $role): Utilisateur
    {
        static $sequence = 0;
        $sequence++;

        return Utilisateur::create([
            'nom_utilisateur' => 'Test '.$sequence,
            'prenom_utilisateur' => 'Utilisateur',
            'adresse_utilisateur' => '1 rue du Test',
            'CP_utilisateur' => 75000,
            'ville_utilisateur' => 'Paris',
            'login_utilisateur' => 'test'.$sequence,
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
            'motorisation_vehicule' => '1.2 PureTech',
            'vin_vehicule' => 'VIN-TEST-123',
            'code_moteur_vehicule' => 'EB2ADTS',
        ]);
    }
}
