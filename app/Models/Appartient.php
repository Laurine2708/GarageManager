<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Association entre un utilisateur et un véhicule.
 *
 * Cette table pivot ne possède ni clé primaire autonome ni timestamps.
 *
 * @property int $id_vehicule Identifiant du véhicule associé.
 * @property int $id_utilisateur Identifiant de l'utilisateur associé.
 */
class Appartient extends Model
{
    /** Table pivot métier. */
    protected $table = 'appartient';

    /** La table pivot ne comporte pas de colonnes de suivi Laravel. */
    public $timestamps = false;

    /** Identifiants que l'association autorise à renseigner en masse. */
    protected $fillable = [
        'id_vehicule',
        'id_utilisateur',
    ];

    /** L'identité d'une ligne repose sur la paire d'identifiants du pivot. */
    public $incrementing = false;

    /** Aucune colonne unique ne joue le rôle de clé primaire. */
    protected $primaryKey = null;
}