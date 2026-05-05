<?php

namespace App\Console\Commands;

use App\Models\AthleteActivity;
use Illuminate\Console\Command;
use App\Jobs\FindZipActivityJob;
use App\Models\Athlete;

class FindIntersectedZips extends Command
{
    protected $signature = 'activities:zip';

    protected $description = 'Dispatch ZIP calculation jobs';

    public function handle()
    {
        $activities = AthleteActivity::get();
       // $ids = $query->pluck('id');
        $athlete=Athlete::whereId(2)->first();
        $accessToken = $athlete->account->access_token;
        $refreshToken = $athlete->account->refresh_token;
        foreach ($activities as $activity) {
        $polyline = $polyline=$activity->activity_map?json_decode($activity->activity_map->map)->summary_polyline:'';
        if(!empty($polyline))
            FindZipActivityJob::dispatch($activity->id,$polyline,$accessToken,$activity->activity_id);
        }

        
    }
}
