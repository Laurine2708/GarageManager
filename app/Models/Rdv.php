<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Rendez-vous d'un client pour un véhicule.
 *
 * @property int $id_rdv Identifiant du rendez-vous.
 * @property mixed $date_rdv Date et heure prévues.
 * @property string $motif_rdv Motif communiqué par le client.
 * @property int $id_vehicule Véhicule concerné.
 * @property int $id_utilisateur Client ayant pris le rendez-vous.
 */
class Rdv extends Model
{
    /** Table métier contenant les rendez-vous. */
    protected $table = 'rdv';

    /** Clé primaire non conventionnelle du modèle. */
    protected $primaryKey = 'id_rdv';

    /** Le schéma métier ne comporte pas de colonnes de suivi Laravel. */
    public $timestamps = false;

    /** Champs rendez-vous autorisés pour l'assignation de masse. */
    protected $fillable = [
        'date_rdv',
        'motif_rdv',
        'id_vehicule',
        'id_utilisateur',
    ];

    /** Client auquel appartient le rendez-vous. */
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }

    /** Véhicule concerné par le rendez-vous. */
    public function vehicule(): BelongsTo
    {
        return $this->belongsTo(Vehicule::class, 'id_vehicule', 'id_vehicule');
    }

    /** Interventions planifiées à partir de ce rendez-vous. */
    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class, 'id_rdv', 'id_rdv');
    }
}