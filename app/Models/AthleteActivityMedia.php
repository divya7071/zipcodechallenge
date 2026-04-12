<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AthleteActivityMedia extends Model
{ 
    
    protected $fillable = [
        'athlete_activity_id',
        'athlete_id',
        'media',
    ];

    public function activity()
    {
        return $this->belongsTo(AthleteActivity::class, 'athlete_activity_id');
    }
}
