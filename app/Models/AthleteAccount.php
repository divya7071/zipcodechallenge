<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class AthleteAccount extends Model
{
    protected $fillable = [
        'athlete_id',
        'strava_athlete_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
    ];

    public function athlete()
    {
        return $this->belongsTo(Athlete::class);
    }
}
