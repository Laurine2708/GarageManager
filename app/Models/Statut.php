<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Statut métier utilisé pour qualifier une intervention.
 *
 * @property int $id_statut Identifiant du statut.
 * @property string $nom_statut Libellé affiché.
 */
class Statut extends Model
{
    /** Table métier contenant les statuts. */
    protected $table = 'statut';

    /** Clé primaire non conventionnelle du modèle. */
    protected $primaryKey = 'id_statut';

    /** Le schéma métier ne comporte pas de colonnes de suivi Laravel. */
    public $timestamps = false;

    /** Champ statut autorisé pour l'assignation de masse. */
    protected $fillable = [
        'nom_statut',
    ];
}
