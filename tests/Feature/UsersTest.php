<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/** Vérifie les droits par rôle et les opérations sur les comptes et profils métier. */
class UsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_delete_an_unlinked_user(): void
    {
        $administrator = $this->createUtilisateur('administrateur');
        $target = $this->createUtilisateur('client');

        $this->actingAs($administrator)
            ->delete(route('users.destroy', $target->id_utilisateur))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('utilisateur', ['id_utilisateur' => $target->id_utilisateur]);
    }

    public function test_non_administrator_cannot_delete_a_user(): void
    {
        $mechanic = $this->createUtilisateur('mecanicien');
        $target = $this->createUtilisateur('client');

        $this->actingAs($mechanic)
            ->delete(route('users.destroy', $target->id_utilisateur))
            ->assertForbidden();

        $this->assertDatabaseHas('utilisateur', ['id_utilisateur' => $target->id_utilisateur]);
    }

    public function test_mechanic_can_view_the_user_list_but_not_the_creation_form(): void
    {
        $mechanic = $this->createUtilisateur('mecanicien');

        $this->actingAs($mechanic)->get(route('users.index'))->assertOk();
        $this->get(route('users.create'))->assertForbidden();
    }

    public function test_administrator_and_mechanic_can_view_user_details(): void
    {
        $target = $this->createUtilisateur('client');

        foreach (['administrateur', 'mecanicien'] as $role) {
            $this->actingAs($this->createUtilisateur($role))
                ->get(route('users.show', $target->id_utilisateur))
                ->assertInertia(fn (Assert $page) => $page
                    ->component('UserForm')
                    ->where('mode', 'view')
                    ->where('user.id', $target->id_utilisateur)
                        ->where('user.email', $target->email_utilisateur));
        }
    }

    public function test_client_cannot_view_other_user_details(): void
    {
        $client = $this->createUtilisateur('client');
        $target = $this->createUtilisateur('client');

        $this->actingAs($client)
            ->get(route('users.show', $target->id_utilisateur))
            ->assertForbidden();
    }

    public function test_administrator_can_add_a_user(): void
    {
        $administrator = $this->createUtilisateur('administrateur');
        $form = $this->validForm();
        // La création ne demande plus de secret dans le formulaire.
        unset($form['password'], $form['password_confirmation']);

        $this->actingAs($administrator)
            ->post(route('users.store'), $form)
            ->assertRedirect(route('users.create'));

        $createdUser = Utilisateur::where('login_utilisateur', 'nouveau.login')->firstOrFail();

        $this->assertDatabaseHas('utilisateur', [
            'id_utilisateur' => $createdUser->id_utilisateur,
            'email_utilisateur' => 'nouveau@example.com',
            'login_utilisateur' => 'nouveau.login',
            'tel_utilisateur' => '0612345678',
            'adresse_utilisateur' => '12 rue des Lilas',
            'CP_utilisateur' => 75001,
            'role_utilisateur' => 'client',
        ]);
        $this->assertNotSame('', $createdUser->mdp_utilisateur);
    }

    public function test_administrator_can_update_a_user(): void
    {
        $administrator = $this->createUtilisateur('administrateur');
        $target = $this->createUtilisateur('client');
        $originalPasswordHash = $target->mdp_utilisateur;
        $originalLogin = $target->login_utilisateur;
        $form = $this->validForm([
            'firstName' => 'Nouveau prénom',
            'email' => 'modifie@example.com',
            'login' => $originalLogin,
            'userRole' => 'client',
        ]);
        unset($form['password'], $form['password_confirmation']);

        $this->actingAs($administrator)
            ->put(route('users.update', $target->id_utilisateur), $form)
            ->assertRedirect(route('users.edit', $target->id_utilisateur));

        $this->assertDatabaseHas('utilisateur', [
            'id_utilisateur' => $target->id_utilisateur,
            'prenom_utilisateur' => 'Nouveau prénom',
            'email_utilisateur' => 'modifie@example.com',
            'login_utilisateur' => $originalLogin,
            'role_utilisateur' => 'client',
        ]);

        $this->assertSame($originalPasswordHash, $target->fresh()->mdp_utilisateur);
        $this->assertTrue(Hash::check('password', $originalPasswordHash));
        auth()->logout();

        $this->post(route('login.store'), [
            'login_utilisateur' => $originalLogin,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($target->fresh());
    }

    public function test_client_can_edit_only_their_own_profile_without_changing_role(): void
    {
        $client = $this->createUtilisateur('client');

        $this->actingAs($client)
            ->get(route('client.profile.edit'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('UserForm')
                ->where('mode', 'profile')
                ->where('user.email', $client->email_utilisateur));

        $this->put(route('client.profile.update'), $this->validForm([
            'firstName' => 'Profil modifié',
            'userRole' => 'administrateur',
            'password' => '',
            'password_confirmation' => '',
        ]))->assertRedirect(route('client.profile.edit'));

        $this->assertDatabaseHas('utilisateur', [
            'id_utilisateur' => $client->id_utilisateur,
            'prenom_utilisateur' => 'Profil modifié',
            'role_utilisateur' => 'client',
        ]);
    }

    public function test_administrator_can_edit_their_own_profile_without_changing_role(): void
    {
        $administrator = $this->createUtilisateur('administrateur');

        $this->actingAs($administrator)
            ->get(route('client.profile.edit'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('UserForm')
                ->where('mode', 'profile')
                ->where('role', 'administrateur')
                ->where('user.email', $administrator->email_utilisateur));

        $this->put(route('client.profile.update'), $this->validForm([
            'firstName' => 'Admin modifié',
            'userRole' => 'client',
            'password' => '',
            'password_confirmation' => '',
        ]))->assertRedirect(route('client.profile.edit'));
        $this->assertDatabaseHas('utilisateur', [
            'id_utilisateur' => $administrator->id_utilisateur,
            'prenom_utilisateur' => 'Admin modifié',
            'role_utilisateur' => 'administrateur',
        ]);
    }

    public function test_mechanic_can_edit_their_own_profile_without_changing_role(): void
    {
        $mechanic = $this->createUtilisateur('mecanicien');

        $this->actingAs($mechanic)
            ->get(route('client.profile.edit'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('UserForm')
                ->where('mode', 'profile')
                ->where('role', 'mecanicien')
                ->where('user.email', $mechanic->email_utilisateur));

        $this->put(route('client.profile.update'), $this->validForm([
            'firstName' => 'Mécanicien modifié',
            'userRole' => 'administrateur',
            'password' => '',
            'password_confirmation' => '',
        ]))->assertRedirect(route('client.profile.edit'));

        $this->assertDatabaseHas('utilisateur', [
            'id_utilisateur' => $mechanic->id_utilisateur,
            'prenom_utilisateur' => 'Mécanicien modifié',
            'role_utilisateur' => 'mecanicien',
        ]);
    }

    public function test_profile_password_change_requires_twelve_characters_mixed_case_and_a_symbol(): void
    {
        $client = $this->createUtilisateur('client');
        $form = $this->validForm([
            'password' => 'Moteur!FortXXXX',
            'password_confirmation' => 'Moteur!FortXXXX',
        ]);

        $this->actingAs($client)
            ->put(route('client.profile.update'), $form)
            ->assertRedirect(route('client.profile.edit'));

        $this->assertTrue(Hash::check('Moteur!FortXXXX', $client->fresh()->mdp_utilisateur));

        $form['password'] = 'short-password';
        $form['password_confirmation'] = 'short-password';

        $this->put(route('client.profile.update'), $form)
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('Moteur!FortXXXX', $client->fresh()->mdp_utilisateur));
    }

    /** @return array<string, string> */
    /** Construit les données valides du formulaire, avec surcharges de scénario. */
    private function validForm(array $overrides = []): array
    {
        return array_merge([
            'firstName' => 'Camille',
            'lastName' => 'Durand',
            'telephone' => '0612345678',
            'email' => 'nouveau@example.com',
            'login' => 'nouveau.login',
            'houseNumber' => '12',
            'streetName' => 'rue des Lilas',
            'postalCode' => '75001',
            'city' => 'Paris',
            'userRole' => 'client',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    /** Crée un utilisateur minimal pour les essais de gestion des comptes. */
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
            'email_utilisateur' => 'test'.$sequence.'@example.com',
            'login_utilisateur' => 'test'.$sequence,
            'mdp_utilisateur' => 'password',
            'role_utilisateur' => $role,
        ]);
    }
}