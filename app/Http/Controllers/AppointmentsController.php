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

class AppointmentsController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeAdministrator($request);

        $appointments = Rdv::query()
            ->with(['utilisateur', 'vehicule'])
            ->orderBy('date_rdv')
            ->get()
            ->map(fn (Rdv $appointment) => [
                'id' => $appointment->id_rdv,
                'clientId' => $appointment->id_utilisateur,
                'clientName' => trim($appointment->utilisateur->prenom_utilisateur.' '.$appointment->utilisateur->nom_utilisateur),
                'email' => $appointment->utilisateur->email_utilisateur,
                'telephone' => $appointment->utilisateur->tel_utilisateur,
                'vehicleId' => $appointment->id_vehicule,
                'vehicle' => trim($appointment->vehicule->marque_vehicule.' '.$appointment->vehicule->modele_vehicule),
                'registration' => $appointment->vehicule->immatriculation_vehicule,
                'appointmentDate' => Carbon::parse($appointment->date_rdv)->format('Y-m-d\TH:i'),
                'dateLabel' => Carbon::parse($appointment->date_rdv)->format('d/m/Y à H:i'),
                'reason' => $appointment->motif_rdv,
            ])->values();

        return Inertia::render('Appointments', [
            'role' => 'administrateur',
            'appointments' => $appointments,
            'appointmentClients' => $this->appointmentClients(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdministrator($request);
        $appointment = $this->appointmentAttributes($this->validatedAppointment($request));

        Rdv::create($appointment);

        return to_route($request->input('returnTo') === 'appointments.index' ? 'appointments.index' : 'dashboard');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAdministrator($request);
        $appointment = Rdv::findOrFail($id);
        $appointment->update($this->appointmentAttributes($this->validatedAppointment($request)));

        return to_route('appointments.index');
    }

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

    /** @return array{clientId: int, vehicleId: int, appointmentDate: string, reason: string} */
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

    /** @param array{clientId: int, vehicleId: int, appointmentDate: string, reason: string} $validated
     *  @return array{date_rdv: string, motif_rdv: string, id_vehicule: int, id_utilisateur: int}
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
                'name' => trim($client->prenom_utilisateur.' '.$client->nom_utilisateur),
                'vehicles' => $client->vehicules->map(fn ($vehicle) => [
                    'id' => $vehicle->id_vehicule,
                    'label' => trim($vehicle->marque_vehicule.' '.$vehicle->modele_vehicule).' · '.$vehicle->immatriculation_vehicule,
                ])->values(),
            ])->values();
    }
}
