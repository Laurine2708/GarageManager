<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /** L'utilisateur référencé par `id_utilisateur` est le mécanicien assigné. */
    public function mecanicien(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }

    /** Véhicule traité par l'intervention. */
    public function vehicule(): BelongsTo
    {
        return $this->belongsTo(Vehicule::class, 'id_vehicule', 'id_vehicule');
    }

    /** Statut métier affiché dans les compteurs et le bandeau. */
    public function statut(): BelongsTo
    {
        return $this->belongsTo(Statut::class, 'id_statut', 'id_statut');
    }

    /** Rendez-vous d'origine, utilisé pour afficher la date d'intervention. */
    public function rendezVous(): BelongsTo
    {
        return $this->belongsTo(Rdv::class, 'id_rdv', 'id_rdv');
    }
}