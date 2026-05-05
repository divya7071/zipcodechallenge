<?php

namespace App\Jobs;

use App\Models\Athlete;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Traits\StravaHelper;
use Illuminate\Support\Facades\Log;
use App\Events\ActivityFetchCompleted;
use App\Events\ZipSyncProgressEvent;
use App\Models\AthleteActivity;

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
                
                // $data = AthleteActivity::where('athlete_id', $athlete->id)
                // ->where('created_at', '>=', $athlete->strava_sync_started_at)
                // ->selectRaw("
                //     COUNT(*) as total,
                //     SUM(CASE WHEN sync_zip_status = 1 THEN 1 ELSE 0 END) as completed
                // ")
                // ->first();
            //     event(new ZipSyncProgressEvent(
            //     auth('athlete')->id(),
            //     $data->total,
            //     $data->completed
            // ));
                
                
         } catch (\Throwable $e) {

                $athlete->update(['is_syncing' => false]);

            throw $e;
        }
    }
}