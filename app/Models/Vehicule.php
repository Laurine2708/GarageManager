<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Véhicule enregistré dans le parc du garage.
 *
 * @property int $id_vehicule Identifiant du véhicule.
 * @property string $marque_vehicule Marque.
 * @property string $modele_vehicule Modèle.
 * @property string $immatriculation_vehicule Immatriculation.
 * @property string $date_mec_vehicule Date de première mise en circulation.
 * @property string $motorisation_vehicule Type de motorisation.
 * @property string $vin_vehicule Numéro d'identification du véhicule.
 * @property string $code_moteur_vehicule Code moteur.
 */
class Vehicule extends Model
{
    /** Table métier contenant les véhicules. */
    protected $table = 'vehicule';

    /** Clé primaire non conventionnelle du modèle. */
    protected $primaryKey = 'id_vehicule';

    /** Le schéma métier ne comporte pas de colonnes de suivi Laravel. */
    public $timestamps = false;

    /** Champs véhicule autorisés pour l'assignation de masse. */
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