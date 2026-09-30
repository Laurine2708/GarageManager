<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicule extends Model
{
    protected $table = 'vehicule';

    protected $primaryKey = 'id_vehicule';

    public $timestamps = false;

    protected $fillable = [
        'marque_vehicule',
        'modele_vehicule',
        'immatriculation_vehicule',
        'date_mec_vehicule',
        'motorisation_vehicule',
        'vin_vehicule',
        'code_moteur_vehicule',
    ];

    /** Utilisateurs propriétaires ou associés via `appartient`. */
    public function utilisateurs(): BelongsToMany
    {
        return $this->belongsToMany(Utilisateur::class, 'appartient', 'id_vehicule', 'id_utilisateur');
    }

    /** Rendez-vous enregistrés pour ce véhicule. */
    public function rendezVous(): HasMany
    {
        return $this->hasMany(Rdv::class, 'id_vehicule', 'id_vehicule');
    }

    /** Historique des interventions associées à ce véhicule. */
    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class, 'id_vehicule', 'id_vehicule');
    }
}