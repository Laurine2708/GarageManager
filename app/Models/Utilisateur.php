<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/** Modèle de session correspondant à la table métier `utilisateur`. */
class Utilisateur extends Authenticatable
{
    protected $table = 'utilisateur';

    protected $primaryKey = 'id_utilisateur';

    public $timestamps = false;

    protected $hidden = ['mdp_utilisateur'];

    protected $casts = ['mdp_utilisateur' => 'hashed'];

    protected $fillable = [
        'nom_utilisateur',
        'prenom_utilisateur',
        'adresse_utilisateur',
        'CP_utilisateur',
        'ville_utilisateur',
        'email_utilisateur',
        'login_utilisateur',
        'mdp_utilisateur',
        'tel_utilisateur',
        'role_utilisateur',
    ];

    /** Indique à Laravel le champ de hash utilisé par la table métier. */
    public function getAuthPasswordName(): string
    {
        return 'mdp_utilisateur';
    }

    /** Les véhicules clients sont associés par la table pivot `appartient`. */
    public function vehicules(): BelongsToMany
    {
        return $this->belongsToMany(Vehicule::class, 'appartient', 'id_utilisateur', 'id_vehicule');
    }

    /** Rendez-vous créés au nom de cet utilisateur. */
    public function rendezVous(): HasMany
    {
        return $this->hasMany(Rdv::class, 'id_utilisateur', 'id_utilisateur');
    }

    /** Interventions attribuées à cet utilisateur, notamment lorsqu'il est mécanicien. */
    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class, 'id_utilisateur', 'id_utilisateur');
    }
}