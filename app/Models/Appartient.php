<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appartient extends Model
{
    protected $table = 'appartient';

    public $timestamps = false;

    protected $fillable = [
        'id_vehicule',
        'id_utilisateur',
    ];

    public $incrementing = false;

    protected $primaryKey = null;
}