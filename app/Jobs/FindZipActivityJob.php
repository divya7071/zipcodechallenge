<?php

namespace App\Jobs;

use App\Models\AthleteActivity;
use App\Models\AthleteActivityZipStat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Traits\StravaHelper;

class FindZipActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,StravaHelper;

    protected $activityId;
    protected $polyline;
    protected $accessToken;
    protected $stravaActivityId;

    public function __construct($activityId, $polyline, $accessToken, $stravaActivityId)
    {
        $this->activityId = $activityId;
        $this->polyline = $polyline;
        $this->accessToken = $accessToken;
        $this->stravaActivityId = $stravaActivityId;
    }

    public function handle()
    {
        try {

            $savedActivity = AthleteActivity::find($this->activityId);

            if (!$savedActivity) {
                return;
            }

            $zipPolygons = $this->findZipsFromPolyline($this->polyline);

            $savedActivity->passed_zips = array_keys($zipPolygons);
            $savedActivity->save();

            $streams = $this->getActivityStreams(
                $this->accessToken,
                $this->stravaActivityId
            );

            if (!$streams || empty($streams["latlng"]["data"])) {
                return;
            }

            $zipStats = $this->calculateZipEntryStats(
                $streams,
                $zipPolygons
            );

            foreach ($zipStats as $zip => $stats) {
                AthleteActivityZipStat::updateOrCreate(
                    [
                        "athlete_activity_id" => $savedActivity->id,
                        "zip_code" => $zip,
                    ],
                    [
                        "distance_mi" => $stats["distance_mi"],
                        "elapsed_sec" => $stats["elapsed_sec"],
                        "moving_sec" => $stats["moving_sec"],
                        "speed_mph" => $stats["speed_mph"],
                        "max_speed_mph" => $stats["max_speed_mph"],
                        "elevation_gain_ft" => $stats["elevation_gain_ft"],
                        "athlete_id" => $savedActivity->athlete_id,
                        'date' => $savedActivity->date
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error("FindZipActivityJob failed: " . $e->getMessage());
        }
    }
}
