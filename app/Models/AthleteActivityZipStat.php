<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class AthleteActivityZipStat extends Model
{
    use SoftDeletes;
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
        'distance_mi_up',
        'distance_mi_down',
        'speed_mph_up',
        'speed_mph_down',
        'sort_order',
        'sort_order_down',
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
