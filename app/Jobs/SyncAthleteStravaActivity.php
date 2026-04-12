<?php

namespace App\Jobs;

use App\Models\Athlete;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Traits\StravaHelper;
use Illuminate\Support\Facades\Log;

class SyncAthleteStravaActivity implements ShouldQueue
{
    use Queueable,StravaHelper;

    protected $athlete;
    public function __construct($athlete)
    {
         $this->athlete=$athlete;
         
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
       
        $athlete=Athlete::whereId($this->athlete->id)->first();
         try {
               
                Log::info('athlete');
                Log::info(json_encode($athlete));
                $this->getAthleteStravaActivities($this->athlete);
                $athlete->update([
                    'strava_synced_at' => now(),
                    'is_syncing' => false, 
                ]);
                
         } catch (\Throwable $e) {

                $athlete->update(['is_syncing' => false]);

            throw $e;
        }
    }
}