<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\Vehicule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gère la consultation et l'administration des véhicules du garage.
 */
class VehiclesController extends Controller
{
    /**
     * Affiche les véhicules accessibles au rôle courant et les clients pour l'administration.
     */
    public function index(Request $request): Response
    {
        $user = $this->authorizeViewer($request);
        $role = Str::ascii(Str::lower($user->role_utilisateur));

        $vehicles = Vehicule::query()
            ->when($role === 'client', fn ($query) => $query->whereHas(
                'utilisateurs',
                fn ($owners) => $owners->where('utilisateur.id_utilisateur', $user->id_utilisateur),
            ))
            ->when($role === 'mecanicien', fn ($query) => $query->whereHas(
                'utilisateurs',
                fn ($owners) => $owners->whereRaw('LOWER(utilisateur.role_utilisateur) = ?', ['client']),
            ))
            ->with(['utilisateurs' => fn ($query) => $query
                ->whereRaw('LOWER(utilisateur.role_utilisateur) = ?', ['client'])
                ->orderBy('nom_utilisateur')
                ->orderBy('prenom_utilisateur')])
            ->orderBy('marque_vehicule')
            ->orderBy('modele_vehicule')
            ->orderBy('immatriculation_vehicule')
            ->get()
            ->map(fn (Vehicule $vehicle) => $this->vehicleData($vehicle))
            ->values();

        return Inertia::render('Vehicles', [
            'role' => $role,
            'vehicles' => $vehicles,
            'clients' => $role === 'administrateur'
                ? Utilisateur::query()
                    ->whereRaw('LOWER(role_utilisateur) = ?', ['client'])
                    ->orderBy('nom_utilisateur')
                    ->orderBy('prenom_utilisateur')
                    ->get(['id_utilisateur', 'nom_utilisateur', 'prenom_utilisateur'])
                    ->map(fn (Utilisateur $client) => [
                        'id' => $client->id_utilisateur,
                        'name' => trim($client->prenom_utilisateur.' '.Str::upper($client->nom_utilisateur)),
                    ])
                    ->values()
                : [],
        ]);
    }

    /**
     * Crée un véhicule et associe son propriétaire client.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdministrator($request);
        $validated = $this->validatedVehicle($request);
        $client = Utilisateur::findOrFail($validated['ownerId']);

        $vehicle = Vehicule::create($this->vehicleAttributes($validated));
        $vehicle->utilisateurs()->attach($client->id_utilisateur);

        return to_route('vehicles.index');
    }

    /**
     * Affiche une fiche véhicule en lecture seule après vérification de sa visibilité.
     */
    public function show(Request $request, int $id): Response
    {
        $user = $this->authorizeViewer($request);
        $vehicle = $this->visibleVehicle($user, $id);

        return Inertia::render('VehicleForm', [
            'role' => Str::ascii(Str::lower($user->role_utilisateur)),
            'mode' => 'view',
            'vehicle' => $this->vehicleData($vehicle),
            'clients' => [],
        ]);
    }

    /**
     * Affiche le formulaire d'édition et la liste des clients possibles.
     */
    public function edit(Request $request, int $id): Response
    {
        $this->authorizeAdministrator($request);
        $vehicle = Vehicule::with(['utilisateurs' => fn ($query) => $query
            ->whereRaw('LOWER(utilisateur.role_utilisateur) = ?', ['client'])])
            ->findOrFail($id);

        return Inertia::render('VehicleForm', [
            'role' => 'administrateur',
            'mode' => 'edit',
            'vehicle' => $this->vehicleData($vehicle),
            'clients' => Utilisateur::query()
                ->whereRaw('LOWER(role_utilisateur) = ?', ['client'])
                ->orderBy('nom_utilisateur')
                ->orderBy('prenom_utilisateur')
                ->get(['id_utilisateur', 'nom_utilisateur', 'prenom_utilisateur'])
                ->map(fn (Utilisateur $client) => [
                    'id' => $client->id_utilisateur,
                    'name' => trim($client->prenom_utilisateur.' '.Str::upper($client->nom_utilisateur)),
                ])
                ->values(),
        ]);
    }

    /**
     * Met à jour un véhicule et remplace son association de propriétaire.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAdministrator($request);
        $vehicle = Vehicule::findOrFail($id);
        $validated = $this->validatedVehicle($request, $vehicle);
        $vehicle->update($this->vehicleAttributes($validated));
        $vehicle->utilisateurs()->sync([$validated['ownerId']]);

        return to_route('vehicles.edit', $vehicle->id_vehicule);
    }

    /**
     * Supprime un véhicule uniquement s'il n'a pas d'historique métier associé.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAdministrator($request);
        $vehicle = Vehicule::findOrFail($id);

        if ($vehicle->rendezVous()->exists() || $vehicle->interventions()->exists()) {
            throw ValidationException::withMessages([
                'vehicle' => 'Ce véhicule est associé à un rendez-vous ou à une intervention et ne peut pas être supprimé.',
            ]);
        }

        $vehicle->utilisateurs()->detach();
        $vehicle->delete();

        return to_route('vehicles.index');
    }

    /**
     * Autorise les rôles pouvant consulter des véhicules.
     */
    private function authorizeViewer(Request $request): Utilisateur
    {
        $user = $request->user();
        $role = $user instanceof Utilisateur ? Str::ascii(Str::lower($user->role_utilisateur)) : '';

        abort_unless(
            $user instanceof Utilisateur && in_array($role, ['administrateur', 'mecanicien', 'client'], true),
            403,
        );

        return $user;
    }

    /**
     * Vérifie le rôle administrateur et retourne l'utilisateur authentifié.
     */
    private function authorizeAdministrator(Request $request): Utilisateur
    {
        $user = $this->authorizeViewer($request);

        abort_unless(Str::ascii(Str::lower($user->role_utilisateur)) === 'administrateur', 403);

        return $user;
    }

    /**
     * Charge un véhicule seulement s'il est visible par l'utilisateur courant.
     *
     * Les mécaniciens voient les véhicules rattachés à au moins un client.
     */
    private function visibleVehicle(Utilisateur $user, int $id): Vehicule
    {
        $role = Str::ascii(Str::lower($user->role_utilisateur));
        $vehicle = Vehicule::with(['utilisateurs' => fn ($query) => $query
            ->whereRaw('LOWER(utilisateur.role_utilisateur) = ?', ['client'])])
            ->findOrFail($id);

        abort_unless(
            $role === 'administrateur'
                || ($role === 'client' && $vehicle->utilisateurs->contains('id_utilisateur', $user->id_utilisateur))
                || ($role === 'mecanicien' && $vehicle->utilisateurs->isNotEmpty()),
            404,
        );

        return $vehicle;
    }

    /**
     * Valide les attributs du véhicule et l'identifiant du client propriétaire.
     *
     * @return array<string, mixed> Valeurs validées du formulaire.
     */
    private function validatedVehicle(Request $request, ?Vehicule $vehicle = null): array
    {
        $rules = [
            'brand' => ['required', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:50'],
            'registration' => [
                'required',
                'string',
                'max:50',
                Rule::unique('vehicule', 'immatriculation_vehicule')->ignore($vehicle?->id_vehicule, 'id_vehicule'),
            ],
            'firstRegistration' => ['required', 'date_format:Y-m-d'],
            'engine' => ['required', 'string', 'max:50'],
            'vin' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicule', 'vin_vehicule')->ignore($vehicle?->id_vehicule, 'id_vehicule'),
            ],
            'engineCode' => ['required', 'string', 'max:50'],
        ];

        $rules['ownerId'] = [
            'required',
            'integer',
            Rule::exists('utilisateur', 'id_utilisateur')
                ->where(fn ($query) => $query->whereRaw('LOWER(role_utilisateur) = ?', ['client'])),
        ];

        return $request->validate($rules);
    }

    /**
     * Convertit les valeurs validées vers les colonnes persistées du véhicule.
     *
     * @param array<string, mixed> $validated Valeurs issues de la validation.
     * @return array<string, mixed> Attributs compatibles avec le modèle.
     */
    private function vehicleAttributes(array $validated): array
    {
        return [
            'marque_vehicule' => $validated['brand'],
            'modele_vehicule' => $validated['model'],
            'immatriculation_vehicule' => $validated['registration'],
            'date_mec_vehicule' => $validated['firstRegistration'],
            'motorisation_vehicule' => $validated['engine'],
            'vin_vehicule' => $validated['vin'],
            'code_moteur_vehicule' => $validated['engineCode'],
        ];
    }

    /**
     * Prépare les données d'un véhicule pour les pages Inertia.
     *
     * @return array<string, mixed>
     */
    private function vehicleData(Vehicule $vehicle): array
    {
        return [
            'id' => $vehicle->id_vehicule,
            'brand' => $vehicle->marque_vehicule,
            'model' => $vehicle->modele_vehicule,
            'registration' => $vehicle->immatriculation_vehicule,
            'year' => substr((string) $vehicle->date_mec_vehicule, 0, 4),
            'firstRegistration' => $vehicle->date_mec_vehicule,
            'engine' => $vehicle->motorisation_vehicule,
            'vin' => $vehicle->vin_vehicule,
            'engineCode' => $vehicle->code_moteur_vehicule,
            'ownerId' => $vehicle->utilisateurs->first()?->id_utilisateur,
            'ownerName' => $vehicle->utilisateurs->map(fn (Utilisateur $owner) =>
                trim($owner->prenom_utilisateur.' '.Str::upper($owner->nom_utilisateur)))->join(', '),
            'owners' => $vehicle->utilisateurs->map(fn (Utilisateur $owner) => [
                'id' => $owner->id_utilisateur,
                'name' => trim($owner->prenom_utilisateur.' '.Str::upper($owner->nom_utilisateur)),
            ])->values(),
        ];
    }
}
