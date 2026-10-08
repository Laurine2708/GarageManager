<?php

namespace App\Http\Controllers;

use App\Models\Rdv;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gère la consultation et l'administration des rendez-vous.
 */
class AppointmentsController extends Controller
{
    /**
     * Affiche la liste des rendez-vous.
     */
    public function index(Request $request): Response
    {
        $this->authorizeAdministrator($request);

        $appointments = Rdv::query()
            ->with(['utilisateur', 'vehicule'])
            ->orderBy('date_rdv')
            ->get()
            ->map(fn (Rdv $appointment) => $this->appointmentData($appointment))
            ->values();

        return Inertia::render('Appointments', [
            'role' => 'administrateur',
            'appointments' => $appointments,
        ]);
    }

    /** Affiche le formulaire de création d'un rendez-vous. */
    public function create(Request $request): Response
    {
        $this->authorizeAdministrator($request);

        return Inertia::render('AppointmentForm', [
            'role' => 'administrateur',
            'mode' => 'create',
            'appointment' => null,
            'appointmentClients' => $this->appointmentClients(),
        ]);
    }

    /** Affiche la fiche d'un rendez-vous en lecture seule. */
    public function show(Request $request, int $id): Response
    {
        $this->authorizeAdministrator($request);
        $appointment = Rdv::with(['utilisateur', 'vehicule'])->findOrFail($id);

        return Inertia::render('AppointmentForm', [
            'role' => 'administrateur',
            'mode' => 'view',
            'appointment' => $this->appointmentData($appointment),
            'appointmentClients' => $this->appointmentClients(),
        ]);
    }

    /** Affiche le formulaire de modification d'un rendez-vous. */
    public function edit(Request $request, int $id): Response
    {
        $this->authorizeAdministrator($request);
        $appointment = Rdv::with(['utilisateur', 'vehicule'])->findOrFail($id);

        return Inertia::render('AppointmentForm', [
            'role' => 'administrateur',
            'mode' => 'edit',
            'appointment' => $this->appointmentData($appointment),
            'appointmentClients' => $this->appointmentClients(),
        ]);
    }

    /**
     * Crée un rendez-vous après validation de l'association client-véhicule.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdministrator($request);
        $appointment = $this->appointmentAttributes($this->validatedAppointment($request));

        Rdv::create($appointment);

        return match ($request->input('returnTo')) {
            'appointments.index' => to_route('appointments.index'),
            'appointments.create' => to_route('appointments.create'),
            default => to_route('dashboard'),
        };
    }

    /**
     * Met à jour un rendez-vous existant avec les données validées.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAdministrator($request);
        $appointment = Rdv::findOrFail($id);
        $appointment->update($this->appointmentAttributes($this->validatedAppointment($request)));

        return $request->input('returnTo') === 'appointments.edit'
            ? to_route('appointments.edit', $id)
            : to_route('appointments.index');
    }

    /**
     * Supprime un rendez-vous dépourvu d'intervention associée.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAdministrator($request);
        $appointment = Rdv::findOrFail($id);

        if ($appointment->interventions()->exists()) {
            throw ValidationException::withMessages([
                'appointment' => 'Ce rendez-vous est associé à une intervention et ne peut pas être supprimé.',
            ]);
        }

        $appointment->delete();

        return to_route('appointments.index');
    }

    /**
     * Vérifie le rôle administrateur et retourne l'acteur authentifié.
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
     * Valide les champs du rendez-vous et vérifie que le véhicule appartient au client.
     *
     * @return array{clientId: int, vehicleId: int, appointmentDate: string, reason: string}
     */
    private function validatedAppointment(Request $request): array
    {
        $validated = $request->validate([
            'clientId' => [
                'required',
                'integer',
                Rule::exists('utilisateur', 'id_utilisateur')
                    ->where(fn ($query) => $query->whereRaw('LOWER(role_utilisateur) = ?', ['client'])),
            ],
            'vehicleId' => ['required', 'integer', Rule::exists('vehicule', 'id_vehicule')],
            'appointmentDate' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $client = Utilisateur::findOrFail($validated['clientId']);
        if (! $client->vehicules()->where('vehicule.id_vehicule', $validated['vehicleId'])->exists()) {
            throw ValidationException::withMessages([
                'vehicleId' => 'Le véhicule sélectionné n’appartient pas à ce client.',
            ]);
        }

        return $validated;
    }

    /**
     * Traduit les champs validés de l'interface vers les colonnes du modèle `Rdv`.
     *
     * @param array{clientId: int, vehicleId: int, appointmentDate: string, reason: string} $validated
     * @return array{date_rdv: string, motif_rdv: string, id_vehicule: int, id_utilisateur: int}
     */
    private function appointmentAttributes(array $validated): array
    {
        return [
            'date_rdv' => Carbon::parse($validated['appointmentDate'])->format('Y-m-d H:i:s'),
            'motif_rdv' => $validated['reason'],
            'id_vehicule' => $validated['vehicleId'],
            'id_utilisateur' => $validated['clientId'],
        ];
    }

    /**
     * Construit les choix de clients accompagnés de leurs véhicules pour le formulaire.
     *
     * @return Collection<int, array{id: int, name: string, vehicles: Collection}>
     */
    private function appointmentClients(): Collection
    {
        return Utilisateur::query()
            ->whereRaw('LOWER(role_utilisateur) = ?', ['client'])
            ->with(['vehicules' => fn ($query) => $query->orderBy('marque_vehicule')])
            ->orderBy('nom_utilisateur')
            ->orderBy('prenom_utilisateur')
            ->get(['id_utilisateur', 'nom_utilisateur', 'prenom_utilisateur'])
            ->map(fn (Utilisateur $client) => [
                'id' => $client->id_utilisateur,
                'name' => trim($client->prenom_utilisateur.' '.Str::upper($client->nom_utilisateur)),
                'vehicles' => $client->vehicules->map(fn ($vehicle) => [
                    'id' => $vehicle->id_vehicule,
                    'label' => trim($vehicle->marque_vehicule.' '.$vehicle->modele_vehicule).' · '.$vehicle->immatriculation_vehicule,
                ])->values(),
            ])->values();
    }

    /**
     * Prépare les données d'un rendez-vous pour les pages Inertia.
     *
     * @return array<string, mixed>
     */
    private function appointmentData(Rdv $appointment): array
    {
        return [
            'id' => $appointment->id_rdv,
            'clientId' => $appointment->id_utilisateur,
            'clientName' => trim($appointment->utilisateur->prenom_utilisateur.' '.Str::upper($appointment->utilisateur->nom_utilisateur)),
            'email' => $appointment->utilisateur->email_utilisateur,
            'telephone' => $appointment->utilisateur->tel_utilisateur,
            'vehicleId' => $appointment->id_vehicule,
            'vehicle' => trim($appointment->vehicule->marque_vehicule.' '.$appointment->vehicule->modele_vehicule),
            'registration' => $appointment->vehicule->immatriculation_vehicule,
            'appointmentDate' => Carbon::parse($appointment->date_rdv)->format('Y-m-d\TH:i'),
            'dateLabel' => Carbon::parse($appointment->date_rdv)->format('d/m/Y à H:i'),
            'reason' => $appointment->motif_rdv,
        ];
    }
}
