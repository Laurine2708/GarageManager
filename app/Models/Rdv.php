<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rdv extends Model
{
    protected $table = 'rdv';

    protected $primaryKey = 'id_rdv';

    public $timestamps = false;

    protected $fillable = [
        'date_rdv',
        'motif_rdv',
        'id_vehicule',
        'id_utilisateur',
    ];
}