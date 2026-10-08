<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pièce disponible dans le stock du garage.
 *
 * @property int $id_piece Identifiant de la pièce.
 * @property string $nom_piece Désignation.
 * @property string $reference_piece Référence catalogue.
 * @property mixed $prix_piece Prix unitaire.
 * @property int $quantite_stock_piece Quantité en stock.
 */
class Piece extends Model
{
    /** Table métier contenant les pièces. */
    protected $table = 'piece';

    /** Clé primaire non conventionnelle du modèle. */
    protected $primaryKey = 'id_piece';

    /** Le schéma métier ne comporte pas de colonnes de suivi Laravel. */
    public $timestamps = false;

    /** Champs pièce autorisés pour l'assignation de masse. */
    protected $fillable = [ 
        'nom_piece', 
        'reference_piece', 
        'prix_piece', 
        'quantite_stock_piece', 
    ];
}
