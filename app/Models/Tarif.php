<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tarif proposé par le garage.
 *
 * @property int $id_tarif Identifiant du tarif.
 * @property string $libelle_tarif Libellé du tarif.
 * @property mixed $montant_tarif Montant facturé.
 */
class Tarif extends Model
{
    /** Table métier contenant les tarifs. */
    protected $table = 'tarif';

    /** Clé primaire non conventionnelle du modèle. */
    protected $primaryKey = 'id_tarif';

    /** Le schéma métier ne comporte pas de colonnes de suivi Laravel. */
    public $timestamps = false;

    /** Champs tarif autorisés pour l'assignation de masse. */
    protected $fillable = [
        'libelle_tarif',
        'montant_tarif',
    ];
}