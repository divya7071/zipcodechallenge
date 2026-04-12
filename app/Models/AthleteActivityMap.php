<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AthleteActivityMap extends Model
{
    use HasFactory;

    protected $table = 'athlete_activity_maps';

    protected $fillable = [
        'athlete_id',
        'activity_id',
        'athlete_activity_id',
        'map',
    ];

    public function activity()
    {
        return $this->belongsTo(AthleteActivity::class, 'athlete_activity_id');
    }
}
