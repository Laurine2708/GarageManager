<?php

namespace Tests\Feature\Auth;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Couvre l’affichage, la connexion, la limitation des essais et la déconnexion. */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = $this->createUtilisateur();

        $response = $this->post(route('login.store'), [
            'login_utilisateur' => $user->login_utilisateur,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = $this->createUtilisateur();

        $this->post(route('login.store'), [
            'login_utilisateur' => $user->login_utilisateur,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_account_is_throttled_after_five_failed_attempts_for_one_minute(): void
    {
        $user = $this->createUtilisateur();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'login_utilisateur' => $user->login_utilisateur,
                'password' => 'wrong-password',
            ]);
        }

        $this->post(route('login.store'), [
            'login_utilisateur' => $user->login_utilisateur,
            'password' => 'password',
        ])->assertSessionHasErrors();
        $this->assertGuest();

        $this->travel(61)->seconds();

        $this->post(route('login.store'), [
            'login_utilisateur' => $user->login_utilisateur,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_logout()
    {
        $user = $this->createUtilisateur();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));

        $this->assertGuest();
    }

    /** Prépare un compte métier utilisable par les essais d’authentification. */
    private function createUtilisateur(): Utilisateur
    {
        return Utilisateur::create([
            'nom_utilisateur' => 'Dupont',
            'prenom_utilisateur' => 'Jean',
            'adresse_utilisateur' => '10 rue de la République',
            'CP_utilisateur' => 87000,
            'ville_utilisateur' => 'Limoges',
            'login_utilisateur' => 'jean.dupont',
            'mdp_utilisateur' => 'password',
            'role_utilisateur' => 'client',
        ]);
    }
}
