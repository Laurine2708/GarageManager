<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /** Client auquel appartient le rendez-vous. */
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }

    /** Véhicule concerné par le rendez-vous. */
    public function vehicule(): BelongsTo
    {
        return $this->belongsTo(Vehicule::class, 'id_vehicule', 'id_vehicule');
    }

    /** Interventions planifiées à partir de ce rendez-vous. */
    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class, 'id_rdv', 'id_rdv');
    }
}