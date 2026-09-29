<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicule extends Model
{
    protected $table = 'vehicule';

    protected $primaryKey = 'id_vehicule';

    public $timestamps = false;

    protected $fillable = [
        'marque_vehicule',
        'modele_vehicule',
        'immatriculation_vehicule',
        'date_mec_vehicule',
        'motorisation_vehicule',
        'vin_vehicule',
        'code_moteur_vehicule',
    ];
}