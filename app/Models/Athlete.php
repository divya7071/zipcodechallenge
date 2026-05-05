<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Athlete extends Authenticatable
{  
    use SoftDeletes;
    protected $fillable = ['athlete_id', 'first_name', 'last_name', 'email', 'password', 'city', 'state', 'country', 'sex',
     'profile_medium', 'profile','premium','follower_count','friend_count','athlete_type','badge_type_id','last_strava_activity_id','strava_sync_started_at','strava_synced_at','is_syncing', 'status'];
   
    public function account()
    {
        return $this->hasOne(AthleteAccount::class);
    }
    public function activities()
    {
        return $this->hasMany(AthleteActivity::class,'athlete_id','id');
    }
    public function clubs()
    {
        return $this->hasMany(AthleteClub::class,'athlete_id','id');
    }
    public function medias()
    {
        return $this->hasMany(AthleteActivityMedia::class,'athlete_id','id');
    }
}
