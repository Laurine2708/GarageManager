<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gère les comptes utilisateurs et l'édition du profil personnel.
 */
class UsersController extends Controller
{
    /**
     * Affiche les comptes utilisateurs avec les seuls champs nécessaires à la liste.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Users', [
            'role' => Str::ascii(Str::lower($request->user()->role_utilisateur)),
            'users' => Utilisateur::query()
                ->orderBy('nom_utilisateur')
                ->orderBy('prenom_utilisateur')
                ->get([
                    'id_utilisateur',
                    'nom_utilisateur',
                    'prenom_utilisateur',
                    'email_utilisateur',
                    'login_utilisateur',
                    'tel_utilisateur',
                    'role_utilisateur',
                ]),
        ]);
    }

    /**
     * Affiche le formulaire de création réservé aux administrateurs.
     */
    public function create(Request $request): Response
    {
        $this->authorizeAdministrator($request);

        return Inertia::render('UserForm', [
            'role' => 'administrateur',
            'mode' => 'create',
            'user' => null,
            'passwordRules' => Password::min(12)->mixedCase()->symbols()->toPasswordRulesString(),
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'un compte existant.
     */
    public function edit(Request $request, int $id): Response
    {
        $this->authorizeAdministrator($request);
        $user = Utilisateur::findOrFail($id);

        return Inertia::render('UserForm', [
            'role' => 'administrateur',
            'mode' => 'edit',
            'user' => $this->userFormData($user),
            'passwordRules' => Password::min(12)->mixedCase()->symbols()->toPasswordRulesString(),
        ]);
    }

    /**
     * Affiche une fiche en lecture seule aux administrateurs et mécaniciens.
     */
    public function show(Request $request, int $id): Response
    {
        // Administrateurs et mécaniciens peuvent lire les fiches sans accéder à leur édition.
        $this->authorizeUsersViewer($request);
        $user = Utilisateur::findOrFail($id);

        return Inertia::render('UserForm', [
            'role' => Str::ascii(Str::lower($request->user()->role_utilisateur)),
            'mode' => 'view',
            'user' => $this->userFormData($user),
            'passwordRules' => Password::min(12)->mixedCase()->symbols()->toPasswordRulesString(),
        ]);
    }

    /**
     * Affiche à l'utilisateur authentifié son propre formulaire de profil.
     */
    public function editProfile(Request $request): Response
    {
        $user = $this->authorizeProfileOwner($request);

        return Inertia::render('UserForm', [
            'role' => Str::ascii(Str::lower($user->role_utilisateur)),
            'mode' => 'profile',
            'user' => $this->userFormData($user),
            'passwordRules' => Password::min(12)->mixedCase()->symbols()->toPasswordRulesString(),
        ]);
    }

    /**
     * Crée un compte et lui attribue un mot de passe initial aléatoire non divulgué.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdministrator($request);
        $validated = $this->validatedUser($request, creating: true);

        Utilisateur::create($this->userAttributes($validated) + [
            // Aucun secret n’est demandé à la création; le mot de passe généré n’est jamais révélé.
            'mdp_utilisateur' => Hash::make(Str::random(64)),
        ]);

        return to_route('users.create');
    }

    /**
     * Met à jour un compte; le mot de passe ne change que s'il est renseigné.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAdministrator($request);
        $user = Utilisateur::findOrFail($id);
        $validated = $this->validatedUser($request, $user);
        $attributes = $this->userAttributes($validated);

        $user->fill($attributes);

        // Le mot de passe n'est changé que si un nouveau mot de passe a été explicitement fourni.
        if (! empty($validated['password'])) {
            $attributes['mdp_utilisateur'] = Hash::make($validated['password']);
            $user->mdp_utilisateur = $attributes['mdp_utilisateur'];
        }

        if ($user->isDirty()) {
            $user->save();
        }

        return to_route('users.edit', $id);
    }

    /**
     * Met à jour le profil de l'utilisateur courant sans permettre de changer son rôle.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $this->authorizeProfileOwner($request);
        $validated = $this->validatedUser($request, $user, allowRole: false);
        $attributes = $this->userAttributes($validated);
        unset($attributes['role_utilisateur']);
        $user->fill($attributes);

        // Le profil ne modifie le mot de passe que si sa nouvelle valeur est fournie.
        if (! empty($validated['password'])) {
            $user->mdp_utilisateur = Hash::make($validated['password']);
        }

        if ($user->isDirty()) {
            $user->save();
        }

        return to_route('client.profile.edit');
    }

    /**
     * Supprime un compte non courant qui n'est lié à aucun élément métier.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $actor = $this->authorizeAdministrator($request);

        $user = Utilisateur::findOrFail($id);

        if ($actor->is($user)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Vous ne pouvez pas supprimer votre propre compte.']);

            return back();
        }

        // Les clés étrangères ne suppriment pas l’historique métier en cascade.
        if ($user->rendezVous()->exists() || $user->interventions()->exists() || $user->vehicules()->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Ce compte est lié à des véhicules, rendez-vous ou interventions et ne peut pas être supprimé.',
            ]);

            return back();
        }

        $user->delete();

        return to_route('users.index');
    }

    /**
     * Valide les champs de compte, en adaptant les contraintes au mode création/édition.
     *
     * @return array<string, mixed> Valeurs validées du formulaire.
     */
    private function validatedUser(
        Request $request,
        ?Utilisateur $user = null,
        bool $creating = false,
        bool $allowRole = true,
    ): array
    {
        $rules = [
            'firstName' => ['required', 'string', 'max:50'],
            'lastName' => ['required', 'string', 'max:50'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => [
                $creating ? 'required' : 'nullable',
                'email',
                'max:255',
                Rule::unique('utilisateur', 'email_utilisateur')->ignore($user?->id_utilisateur, 'id_utilisateur'),
            ],
            'login' => [
                'required',
                'string',
                'max:50',
                Rule::unique('utilisateur', 'login_utilisateur')->ignore($user?->id_utilisateur, 'id_utilisateur'),
            ],
            'houseNumber' => ['required', 'string', 'max:20'],
            'streetName' => ['required', 'string', 'max:50'],
            'postalCode' => ['required', 'integer', 'min:0', 'max:99999'],
            'city' => ['required', 'string', 'max:50'],
        ];

        if (! $creating) {
            $rules['password'] = ['nullable', 'string', Password::min(12)->mixedCase()->symbols(), 'confirmed'];
        }

        if ($allowRole) {
            $rules['userRole'] = ['required', Rule::in(['client', 'mecanicien', 'administrateur'])];
        }

        $validated = $request->validate($rules);

        Validator::make(
            ['streetName' => trim($validated['houseNumber'].' '.$validated['streetName'])],
            ['streetName' => ['required', 'string', 'max:50']],
        )->validate();

        return $validated;
    }

    /**
     * Convertit les valeurs validées du formulaire vers les colonnes de `utilisateur`.
     *
     * @param array<string, mixed> $validated Valeurs issues de la validation.
     * @return array<string, mixed> Attributs compatibles avec le modèle.
     */
    private function userAttributes(array $validated): array
    {
        $attributes = [
            'prenom_utilisateur' => $validated['firstName'],
            'nom_utilisateur' => $validated['lastName'],
            'tel_utilisateur' => $validated['telephone'] ?: null,
            'email_utilisateur' => $validated['email'] ?: null,
            'login_utilisateur' => $validated['login'],
            'adresse_utilisateur' => trim($validated['houseNumber'].' '.$validated['streetName']),
            'CP_utilisateur' => (int) $validated['postalCode'],
            'ville_utilisateur' => $validated['city'],
        ];

        if (isset($validated['userRole'])) {
            $attributes['role_utilisateur'] = $validated['userRole'];
        }

        return $attributes;
    }

    /**
     * Autorise uniquement un administrateur et retourne son modèle utilisateur.
     */
    private function authorizeAdministrator(Request $request): Utilisateur
    {
        $actor = $request->user();
        abort_unless(
            $actor instanceof Utilisateur
                && Str::ascii(Str::lower($actor->role_utilisateur)) === 'administrateur',
            403,
        );

        return $actor;
    }

    /**
     * Autorise la consultation des fiches aux administrateurs et mécaniciens.
     */
    private function authorizeUsersViewer(Request $request): Utilisateur
    {
        // La liste est visible par ces deux rôles; les autres ne peuvent pas consulter de fiche.
        $actor = $request->user();
        abort_unless(
            $actor instanceof Utilisateur
                && in_array(Str::ascii(Str::lower($actor->role_utilisateur)), ['administrateur', 'mecanicien'], true),
            403,
        );

        return $actor;
    }

    /**
     * Vérifie que l'utilisateur courant peut accéder à la gestion de son profil.
     */
    private function authorizeProfileOwner(Request $request): Utilisateur
    {
        $user = $request->user();
        $role = $user instanceof Utilisateur
            ? Str::ascii(Str::lower($user->role_utilisateur))
            : '';

        abort_unless(
            $user instanceof Utilisateur && in_array($role, ['client', 'mecanicien', 'administrateur'], true),
            403,
        );

        return $user;
    }

    /** @return array<string, mixed> */
    private function userFormData(Utilisateur $user): array
    {
        $addressParts = [];
        preg_match('/^(\S+)\s+(.+)$/u', trim($user->adresse_utilisateur), $addressParts);

        return [
            'id' => $user->id_utilisateur,
            'firstName' => $user->prenom_utilisateur,
            'lastName' => $user->nom_utilisateur,
            'telephone' => $user->tel_utilisateur,
            'email' => $user->email_utilisateur,
            'login' => $user->login_utilisateur,
            // L’adresse historique est stockée dans une seule colonne.
            'houseNumber' => $addressParts[1] ?? '',
            'streetName' => $addressParts[2] ?? trim($user->adresse_utilisateur),
            'postalCode' => (string) $user->CP_utilisateur,
            'city' => $user->ville_utilisateur,
            'userRole' => Str::ascii(Str::lower($user->role_utilisateur)),
        ];
    }
}