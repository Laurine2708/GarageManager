<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    protected $table = 'intervention';

    protected $primaryKey = 'id_intervention';

    public $timestamps = false;

    protected $fillable = [
        'description_intervention',
        'temps_intervention',
        'date_depart_intervention',
        'id_rdv',
        'id_tarif',
        'id_utilisateur',
        'id_statut',
        'id_vehicule',
        'kilometrage_intervention',
    ];
}