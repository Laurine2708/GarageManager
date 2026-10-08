<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use App\Models\Vehicule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/** Vérifie les droits de consultation, création et modification des véhicules. */
class VehiclesTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_view_all_vehicles_and_create_a_vehicle_for_a_client(): void
    {
        $administrator = $this->createUser('administrateur');
        $client = $this->createUser('client');
        $otherClient = $this->createUser('client');
        $firstVehicle = $this->createVehicle('AB-123-CD');
        $secondVehicle = $this->createVehicle('EF-456-GH');
        $client->vehicules()->attach($firstVehicle->id_vehicule);
        $otherClient->vehicules()->attach($secondVehicle->id_vehicule);

        $this->actingAs($administrator)
            ->get(route('vehicles.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicles')
                ->has('vehicles', 2)
                ->where('vehicles.0.owners.0.name', trim($client->prenom_utilisateur.' '.$client->nom_utilisateur))
                ->has('clients', 2));

        $this->post(route('vehicles.store'), $this->vehicleForm([
            'ownerId' => $client->id_utilisateur,
        ]))->assertRedirect(route('vehicles.index'));

        $createdVehicle = Vehicule::where('immatriculation_vehicule', 'GH-789-IJ')->firstOrFail();
        $this->assertTrue($client->vehicules()->whereKey($createdVehicle->id_vehicule)->exists());
    }

    public function test_clients_only_see_their_own_vehicles(): void
    {
        $client = $this->createUser('client');
        $otherClient = $this->createUser('client');
        $ownedVehicle = $this->createVehicle('AB-123-CD');
        $otherVehicle = $this->createVehicle('EF-456-GH');
        $client->vehicules()->attach($ownedVehicle->id_vehicule);
        $otherClient->vehicules()->attach($otherVehicle->id_vehicule);

        $this->actingAs($client)
            ->get(route('vehicles.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicles')
                ->has('vehicles', 1)
                ->where('vehicles.0.registration', 'AB-123-CD')
                ->has('clients', 0));

        $this->post(route('vehicles.store'), $this->vehicleForm([
            'ownerId' => $client->id_utilisateur,
        ]))->assertForbidden();
    }

    public function test_mechanics_only_see_vehicles_associated_with_clients(): void
    {
        $mechanic = $this->createUser('mecanicien');
        $client = $this->createUser('client');
        $clientVehicle = $this->createVehicle('AB-123-CD');
        $unownedVehicle = $this->createVehicle('EF-456-GH');
        $client->vehicules()->attach($clientVehicle->id_vehicule);

        $this->actingAs($mechanic)
            ->get(route('vehicles.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicles')
                ->has('vehicles', 1)
                ->where('vehicles.0.registration', 'AB-123-CD')
                ->has('clients', 0));

        $this->assertDatabaseHas('vehicule', ['id_vehicule' => $unownedVehicle->id_vehicule]);
    }

    public function test_vehicle_detail_is_read_only_and_administrator_can_edit_it_with_success_redirect(): void
    {
        $administrator = $this->createUser('administrateur');
        $client = $this->createUser('client');
        $replacementClient = $this->createUser('client');
        $vehicle = $this->createVehicle('AB-123-CD');
        $client->vehicules()->attach($vehicle->id_vehicule);

        $this->actingAs($client)
            ->get(route('vehicles.show', $vehicle->id_vehicule))
            ->assertInertia(fn (Assert $page) => $page
                ->component('VehicleForm')
                ->where('mode', 'view')
                ->where('vehicle.registration', 'AB-123-CD')
                ->where('vehicle.ownerName', trim($client->prenom_utilisateur.' '.$client->nom_utilisateur)));

        $this->actingAs($administrator)
            ->get(route('vehicles.edit', $vehicle->id_vehicule))
            ->assertInertia(fn (Assert $page) => $page
                ->component('VehicleForm')
                ->where('mode', 'edit')
                ->where('vehicle.id', $vehicle->id_vehicule)
                ->has('clients', 2));

        $this->put(route('vehicles.update', $vehicle->id_vehicule), $this->vehicleForm([
            'brand' => 'Renault',
            'ownerId' => $replacementClient->id_utilisateur,
        ]))->assertRedirect(route('vehicles.edit', $vehicle->id_vehicule));

        $this->assertDatabaseHas('vehicule', [
            'id_vehicule' => $vehicle->id_vehicule,
            'marque_vehicule' => 'Renault',
        ]);
        $this->assertTrue($replacementClient->vehicules()->whereKey($vehicle->id_vehicule)->exists());
        $this->assertFalse($client->vehicules()->whereKey($vehicle->id_vehicule)->exists());
    }

    /** @return array<string, int|string> */
    /** Prépare des données valides de véhicule et applique les valeurs propres au cas. */
    private function vehicleForm(array $overrides = []): array
    {
        return array_merge([
            'brand' => 'Renault',
            'model' => 'Clio',
            'registration' => 'GH-789-IJ',
            'firstRegistration' => '2022-03-15',
            'engine' => '1.0 TCe',
            'vin' => 'VIN-GH-789-IJ',
            'engineCode' => 'H4D',
            'ownerId' => 1,
        ], $overrides);
    }

    /** Crée un compte du rôle indiqué pour établir les droits de propriété. */
    private function createUser(string $role): Utilisateur
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

    /** Crée un véhicule de test associé à une immatriculation distincte. */
    private function createVehicle(string $registration): Vehicule
    {
        return Vehicule::create([
            'marque_vehicule' => 'Peugeot',
            'modele_vehicule' => '308',
            'immatriculation_vehicule' => $registration,
            'date_mec_vehicule' => '2020-06-15',
            'motorisation_vehicule' => '1.2 PureTech',
            'vin_vehicule' => 'VIN-'.$registration,
            'code_moteur_vehicule' => 'EB2ADTS',
        ]);
    }
}
