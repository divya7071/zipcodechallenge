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
            $route = $this->decodePolyline($this->polyline);
            $zipPolygons = $this->findZipsFromPolyline($this->polyline);
            $savedActivity->sync_zip_status=1;
            $savedActivity->passed_zips = array_keys($zipPolygons);
            $savedActivity->save();
            $zipDirection = $this->getZipSequence($route, $zipPolygons);
           
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
        $zipOrders = $this->buildZipOrders($zipDirection);
        Log::info('zipOrders');
        Log::info(json_encode($zipOrders));
         foreach ($zipStats as $zip => $dirs) {

            $orderUp   = $zipOrders[$zip]['up']   ?? null;
            $orderDown = $zipOrders[$zip]['down'] ?? null;

            AthleteActivityZipStat::updateOrCreate(
                [
                    "athlete_activity_id" => $savedActivity->id,
                    "zip_code" => $zip,
                ],
                [
                    "athlete_id" => $savedActivity->athlete_id,
                    "date" => $savedActivity->date,
                    "distance_mi_up" => $dirs['up']['distance_mi'] ?? 0,
                    "speed_mph_up"   => $dirs['up']['speed_mph'] ?? 0,
                    "distance_mi_down" => $dirs['down']['distance_mi'] ?? 0,
                    "speed_mph_down"   => $dirs['down']['speed_mph'] ?? 0,
                    "distance_mi" => ($dirs['up']['distance_mi'] ?? 0)
                                + ($dirs['down']['distance_mi'] ?? 0),

                    "elapsed_sec" => ($dirs['up']['elapsed_sec'] ?? 0)
                                + ($dirs['down']['elapsed_sec'] ?? 0),

                    "moving_sec" => ($dirs['up']['moving_sec'] ?? 0)
                                + ($dirs['down']['moving_sec'] ?? 0),

                    "max_speed_mph" => max(
                        $dirs['up']['max_speed_mph'] ?? 0,
                        $dirs['down']['max_speed_mph'] ?? 0
                    ),
                    "speed_mph" => max(
                        $dirs['up']['speed_mph'] ?? 0,
                        $dirs['down']['speed_mph'] ?? 0
                    ),
                    "elevation_gain_ft" => ($dirs['up']['elevation_gain_ft'] ?? 0)
                                        + ($dirs['down']['elevation_gain_ft'] ?? 0),
                    "sort_order"      => $orderUp,
                    "sort_order_down" => $orderDown,
                ]
            );
            }
            // foreach ($zipStats as $zip => $stats) {
            //     AthleteActivityZipStat::updateOrCreate(
            //         [
            //             "athlete_activity_id" => $savedActivity->id,
            //             "zip_code" => $zip,
            //         ],
            //         [
            //             "distance_mi" => $stats["distance_mi"],
            //             "elapsed_sec" => $stats["elapsed_sec"],
            //             "moving_sec" => $stats["moving_sec"],
            //             "speed_mph" => $stats["speed_mph"],
            //             "max_speed_mph" => $stats["max_speed_mph"],
            //             "elevation_gain_ft" => $stats["elevation_gain_ft"],
            //             "athlete_id" => $savedActivity->athlete_id,
            //             'date' => $savedActivity->date
            //         ]
            //     );
            // }

        } catch (\Exception $e) {
            Log::error("FindZipActivityJob failed: " . $e->getMessage());
        }
    }
}
