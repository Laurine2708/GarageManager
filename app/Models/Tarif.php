<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    protected $table = 'tarif';

    protected $primaryKey = 'id_tarif';

    public $timestamps = false;

    protected $fillable = [
        'libelle_tarif',
        'montant_tarif',
    ];
}