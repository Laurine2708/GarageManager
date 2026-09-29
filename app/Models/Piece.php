<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piece extends Model
{
    protected $table = 'piece';

    protected $primaryKey = 'id_piece';

    public $timestamps = false;
    
    protected $fillable = [ 
        'nom_piece', 
        'reference_piece', 
        'prix_piece', 
        'quantite_stock_piece', 
    ];
}
