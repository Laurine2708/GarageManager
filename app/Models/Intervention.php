<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Intervention effectuée ou planifiée sur un véhicule.
 *
 * @property int $id_intervention Identifiant de l'intervention.
 * @property string $description_intervention Description des travaux.
 * @property mixed $temps_intervention Durée déclarée.
 * @property mixed $date_depart_intervention Date de départ de l'intervention.
 * @property int $id_rdv Rendez-vous d'origine.
 * @property int $id_tarif Tarif appliqué.
 * @property int $id_utilisateur Mécanicien affecté.
 * @property int $id_statut Statut courant.
 * @property int $id_vehicule Véhicule concerné.
 * @property int|null $kilometrage_intervention Kilométrage relevé.
 */
class Intervention extends Model
{
    /** Table métier contenant les interventions. */
    protected $table = 'intervention';

    /** Clé primaire non conventionnelle du modèle. */
    protected $primaryKey = 'id_intervention';

    /** Le schéma métier ne comporte pas de colonnes de suivi Laravel. */
    public $timestamps = false;

    /** Champs métier autorisés pour l'assignation de masse. */
    protected $fillable = [
        'description_intervention',
        'temps_intervention',
        'date_depart_intervention',
        'id_rdv',
        'id_tarif',
        'id_utilisateur',
        'id_statut',
        'id_vehicule',
        'kilometrage_intervention',
    ];

    /** L'utilisateur référencé par `id_utilisateur` est le mécanicien assigné. */
    public function mecanicien(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }

    /** Véhicule traité par l'intervention. */
    public function vehicule(): BelongsTo
    {
        return $this->belongsTo(Vehicule::class, 'id_vehicule', 'id_vehicule');
    }

    /** Statut métier affiché dans les compteurs et le bandeau. */
    public function statut(): BelongsTo
    {
        return $this->belongsTo(Statut::class, 'id_statut', 'id_statut');
    }

    /** Rendez-vous d'origine, utilisé pour afficher la date d'intervention. */
    public function rendezVous(): BelongsTo
    {
        return $this->belongsTo(Rdv::class, 'id_rdv', 'id_rdv');
    }
}