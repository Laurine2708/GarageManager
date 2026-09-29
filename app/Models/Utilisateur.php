<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Utilisateur extends Model
{
    protected $table = 'utilisateur';

    protected $primaryKey = 'id_utilisateur';

    public $timestamps = false;

    protected $fillable = [
        'nom_utilisateur',
        'prenom_utilisateur',
        'adresse_utilisateur',
        'CP_utilisateur',
        'ville_utilisateur',
        'login_utilisateur',
        'mdp_utilisateur',
        'role_utilisateur',
    ];
}