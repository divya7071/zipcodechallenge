<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AthleteClub extends Model
{
    protected $fillable = [
        'strava_club_id',
        'name',
        'sport_type',
        'url',
        'city',
        'state',
        'country',
        'profile',
        'profile_medium',
        'is_private',
        'featured',
        'verified',
        'member_count',
        'athlete_id',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'featured'   => 'boolean',
        'verified'   => 'boolean',
    ];

    public function athlete()
    {
        return $this->belongsTo(Athlete::class);
    }
}
