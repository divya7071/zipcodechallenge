<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AthleteBike extends Model
{
    protected $fillable = [
        'strava_gear_id',
        'primary',
        'name',
        'resource_state',
        'distance',
        'athlete_id', 
    ];

    protected $casts = [
        'primary' => 'boolean',
        'distance' => 'integer',
        'resource_state' => 'integer',
    ];

    /**
     * Relationship: Gear belongs to Athlete
     * (Only if you added athlete_id in migration)
     */
    public function athlete()
    {
        return $this->belongsTo(Athlete::class);
    }
}
