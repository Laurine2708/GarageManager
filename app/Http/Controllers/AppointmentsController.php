<?php

namespace App\Http\Controllers;

use App\Models\Rdv;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AppointmentsController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $actor = $request->user();
        abort_unless(
            $actor instanceof Utilisateur
                && Str::ascii(Str::lower($actor->role_utilisateur)) === 'administrateur',
            403,
        );

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

        Rdv::create([
            'date_rdv' => Carbon::parse($validated['appointmentDate'])->format('Y-m-d H:i:s'),
            'motif_rdv' => $validated['reason'],
            'id_vehicule' => $validated['vehicleId'],
            'id_utilisateur' => $client->id_utilisateur,
        ]);

        return to_route('dashboard');
    }
}
