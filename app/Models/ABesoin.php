<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Association entre une intervention et une pièce requise.
 *
 * @property int $id_intervention Identifiant de l'intervention.
 * @property int $id_piece Identifiant de la pièce nécessaire.
 */
class ABesoin extends Model
{
    /** Table pivot métier des besoins en pièces. */
    protected $table = 'a_besoin';

    /** La table pivot ne comporte pas de colonnes de suivi Laravel. */
    public $timestamps = false;

    /** Identifiants que l'association autorise à renseigner en masse. */
    protected $fillable = [
        'id_intervention',
        'id_piece',
    ];

    /** L'identité d'une ligne repose sur les deux identifiants associés. */
    public $incrementing = false;

    /** Aucune colonne unique ne joue le rôle de clé primaire. */
    protected $primaryKey = null;
}