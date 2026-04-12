<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SyncAthleteStravaActivity;
use App\Models\Athlete;
use App\Traits\StravaHelper;

class SyncStravaActivities extends Command
{
    use StravaHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'strava:sync-activities {athlete_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch jobs to sync all athlete activities from Strava';

    public function handle()
    {
        $athleteId = $this->argument('athlete_id');

        $perPage   = 200;
        if ($athleteId) {
                $athlete=Athlete::whereId($athleteId)->first();
                SyncAthleteStravaActivity::dispatch($athleteId, $perPage);
        } else {
           $athletes= Athlete::get();
                foreach ($athletes as $athlete) {
                    SyncAthleteStravaActivity::dispatch($athlete->id,$perPage);
                  
                }
           
        }


        $athletes = Athlete::whereNotNull('athlete_id')->get();
        
        $this->info("Dispatching sync for {$athletes->count()} athletes...");
       
        foreach ($athletes as $athlete) {
            $this->getAthleteStravaActivities($athlete);
            
        }

        $this->info('All sync jobs dispatched successfully.');
    }
}
