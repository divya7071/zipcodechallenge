<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesOsmStaticMap;
use Carbon\Carbon;

class AthleteActivity extends Model 
{

    use GeneratesOsmStaticMap;
    protected $fillable = ['activity_id', 'athlete_id', 'athlete_strava_id', 'name', 'distance', 'moving_time', 'elapsed_time',
     'type', 'sport_type', 'workout_type','elevation','relative_effort','passed_zips','start_location',
     'average_speed','max_speed','device_name','average_watts','weighted_average_watts','photos','date', 'timezone', 'status','zip_status'];
    protected $casts = [
        'date' => 'datetime',
        'photos' => 'array',
    ];
    public function getLocalDateAttribute()
    {
        $timezone = $this->timezone;

        if (str_contains($timezone, ')')) {
            $timezone = trim(explode(')', $timezone)[1]);
        }

        return Carbon::parse($this->date, 'UTC')
            ->setTimezone($timezone);
    }
    public function athlete()
    {
        return $this->belongsTo(Athlete::class, 'athlete_id','id');
    }
    public function activity_map()
    {
        return $this->belongsTo(AthleteActivityMap::class, 'activity_id','athlete_activity_id');
    }
     public function media()
    {
        return $this->belongsTo(AthleteActivityMedia::class,'activity_id','athlete_activity_id');
    }
    public function passedZips()
    {
        return $this->hasMany(AthleteActivityZipStat::class,'athlete_activity_id','id');
    }
   
}
