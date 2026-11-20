<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tâche décrivant une étape rattachée à une intervention.
 *
 * @property int $id_tache Identifiant de la tâche.
 * @property string $libelle_tache Libellé de la tâche.
 * @property int $id_statut Statut de la tâche.
 * @property int $id_intervention Intervention parente.
 */
class Tache extends Model
{
    /** Table métier contenant les tâches. */
    protected $table = 'tache';

    /** Clé primaire non conventionnelle du modèle. */
    protected $primaryKey = 'id_tache';

    /** Le schéma métier ne comporte pas de colonnes de suivi Laravel. */
    public $timestamps = false;

    /** Champs tâche autorisés pour l'assignation de masse. */
    protected $fillable = [
        'libelle_tache',
        'id_statut',
        'id_intervention',
    ];
}