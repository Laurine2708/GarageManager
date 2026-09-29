<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tache extends Model
{
    protected $table = 'tache';

    protected $primaryKey = 'id_tache';

    public $timestamps = false;

    protected $fillable = [
        'libelle_tache',
        'id_statut',
        'id_intervention',
    ];
}