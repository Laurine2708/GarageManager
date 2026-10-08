<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Modèle d'authentification correspondant à la table métier `utilisateur`.
 *
 * @property int $id_utilisateur Identifiant du compte.
 * @property string $nom_utilisateur Nom de famille.
 * @property string $prenom_utilisateur Prénom.
 * @property string $adresse_utilisateur Adresse postale.
 * @property int $CP_utilisateur Code postal.
 * @property string $ville_utilisateur Ville.
 * @property string|null $email_utilisateur Adresse e-mail.
 * @property string $login_utilisateur Identifiant de connexion.
 * @property string $mdp_utilisateur Mot de passe haché.
 * @property string|null $tel_utilisateur Numéro de téléphone.
 * @property string $role_utilisateur Rôle métier du compte.
 */
class Utilisateur extends Authenticatable
{
    /** Table métier contenant les comptes. */
    protected $table = 'utilisateur';

    /** Clé primaire non conventionnelle du modèle. */
    protected $primaryKey = 'id_utilisateur';

    /** Le schéma métier ne comporte pas de colonnes de suivi Laravel. */
    public $timestamps = false;

    /** Le secret ne doit jamais être sérialisé dans une réponse. */
    protected $hidden = ['mdp_utilisateur'];

    /** Hache automatiquement toute valeur affectée au champ du mot de passe. */
    protected $casts = ['mdp_utilisateur' => 'hashed'];

    /** Champs métier autorisés pour l'assignation de masse. */
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