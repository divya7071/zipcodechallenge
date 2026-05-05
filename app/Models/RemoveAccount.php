<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemoveAccount extends Model
{
    protected $table = 'remove_accounts';

    protected $fillable = [
        'athlete_id',
        'athlete_strava_id',
        'first_name',
        'last_name',
        'email',
        'email',
        'reason',
        'other_reason',
        'comments',
        'feedback',
    ];

    /**
     * Relationship with Athlete
     */
    public function athlete()
    {
        return $this->belongsTo(Athlete::class);
    }
}