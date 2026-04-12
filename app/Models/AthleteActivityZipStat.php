<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AthleteActivityZipStat extends Model
{
     protected $fillable = [
        'athlete_activity_id',
        'athlete_id',
        'zip_code',
        'distance_mi',
        'elevation_gain_ft',
        'elapsed_sec',
        'moving_sec',
        'speed_mph',
        'max_speed_mph',
        'rank',
        'date'
    ];

    public function activity()
    {
        return $this->belongsTo(AthleteActivity::class,'athlete_activity_id','id');
    }
    public function athlete()
    {
         return $this->belongsTo(Athlete::class,'athlete_id','id');
    }
}
