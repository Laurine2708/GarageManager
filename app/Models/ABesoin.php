<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ABesoin extends Model
{
    protected $table = 'a_besoin';

    public $timestamps = false;

    protected $fillable = [
        'id_intervention',
        'id_piece',
    ];

    public $incrementing = false;

    protected $primaryKey = null;
}