<?php

namespace App\Traits;

use App\Events\ActivityFetchCompleted;
use App\Jobs\FindZipActivityJob;
use App\Models\ActivityZipStat;
use App\Models\AthleteAccount;
use App\Models\AthleteActivity;
use App\Models\AthleteActivityMap;
use App\Models\AthleteActivityMedia;
use App\Models\AthleteActivityZipStat;
use App\Models\Setting;
use App\Models\StravaActivity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Traits\ZipIntersectionTrait;
use App\Jobs\FetchActivityLocation;
use App\Jobs\FetchActivityPhotos;
use App\Models\Athlete;
use App\Traits\ApiHelper;
use Polyline;

trait StravaHelper
{
    use ZipIntersectionTrait,ApiHelper;
    public function buildStravaAuthorizationUrl(array $params = [])
    {
         $strava_auth_url = env('STRAVA_AUTH_URL');
        $defaultParams = [
            "response_type" => "code",
            "approval_prompt" => "force",
            "scope" => "read,activity:read,activity:write",
        ];

        $queryParams = array_merge($defaultParams, $params);

        return "{$strava_auth_url}/authorize?" .
            http_build_query($queryParams);
    }

    public function getStravaAccessToken(Request $request)
    {
        $clientId = Setting::where("code", "client_id")->value("value");
        $clientSecret = Setting::where("code", "client_secret")->value("value");
        $redirectUri = route("admin.handleCallback");
        $strava_auth_url = env('STRAVA_AUTH_URL');
        $responseToken = Http::post("{$strava_auth_url}/token", [
                "client_id" => $clientId,
            "client_secret" => $clientSecret,
            "code" => $request->code,
            "grant_type" => "authorization_code",
            "redirect_uri" => $redirectUri,
        ]);

        if ($responseToken->successful()) {
            $data = $responseToken->json();

            Setting::updateOrCreate(
                ["code" => "strava_access_token"],
                ["value" => $data["access_token"]]
            );
            Setting::updateOrCreate(
                ["code" => "strava_refresh_token"],
                ["value" => $data["refresh_token"]]
            );

            return redirect()
                ->route("admin.settings.index", ["platform" => "strava"])
                ->with("success_message", "Strava authorized successfully!");
        }

        $error = $responseToken->json()["message"] ?? "Unknown error occurred.";

        return redirect()
            ->route("admin.settings.index", ["platform" => "strava"])
            ->with("failure_message", "Authorization failed. Error: " . $error);
    }
    public function getAthleteStravaActivities($athlete, $perPage = 100)
    {
        $accessToken = $athlete->account->access_token;
        $refreshToken = $athlete->account->refresh_token;
        $strava_url = env('STRAVA_URL');
        $page = 1;
        $lastActivity = AthleteActivity::where("athlete_id", $athlete->id)
            ->orderBy("date", "desc")
            ->first();
        
        
        $page = 1;
        $after = !empty($lastActivity)
            ? Carbon::parse($lastActivity->date)->subDays(2)->timestamp
            : null;
            
        do {
            $params = [
                "page" => $page,
                "per_page" => $perPage,
            ];
            
            if(!empty($after)){
                $params["after"] = $after;
            }
            $activityResponse = Http::withoutVerifying()
                ->withToken($accessToken)
                ->get(
                    "{$strava_url}/athlete/activities",
                    $params
                );

            if ($activityResponse->unauthorized()) {
                $tokenData = $this->refreshAthleteAccessToken(
                    $refreshToken,
                    $athlete
                );
                // Log::info(json_encode($tokenData));
                if (!isset($tokenData["access_token"])) {
                    return redirect()
                        ->route("admin.strava.index")
                        ->with("failure_message", "Token refresh failed.");
                }

                $accessToken = $tokenData["access_token"];

                AthleteAccount::updateOrCreate(
                    ["athlete_id" => $athlete->id],
                    [
                        "access_token" => $tokenData["access_token"],
                        "refresh_token" => $tokenData["refresh_token"],
                        "token_expires_at" => Carbon::createFromTimestamp(
                            $tokenData["expires_at"]
                        ),
                    ]
                );

                $activityResponse = Http::withoutVerifying()
                    ->withToken($accessToken)
                    ->get(
                        "{$strava_url}/athlete/activities",
                        [
                            "page" => $page,
                            "per_page" => $perPage,
                        ]
                    );
            }
            
            $activities = $activityResponse->json();
             $this->logApi('Strava', 'SYNC', $athlete->id, 'GET', "{$strava_url}/athlete/activities", json_encode($params), json_encode($activities), 200, '');
            if (!count($activities)) {
                event(new ActivityFetchCompleted('completed', $athlete["id"])); 
                break;
            }
            
            $this->saveStravaActivity($activities, $athlete);

            $page++;
            sleep(3);
        } while (count($activities) == $perPage);
        
        $athlete->update([
            "last_strava_activity_id" =>
                $activities[0]["id"] ?? $athlete->last_strava_activity_id,
            "strava_synced_at" => now(),
            "is_syncing" => false,
        ]);
        return response()->json([
            "success_message" => "Strava activities synced successfully!",
        ]);
    }

    public function refreshAthleteAccessToken($refreshToken, $athlete)
    {
        $clientId = Setting::where("code", "client_id")->value("value");
        $clientSecret = Setting::where("code", "client_secret")->value("value");
        $strava_auth_url = env('STRAVA_AUTH_URL');
        $response = Http::asForm()->post("{$strava_auth_url}/token", [
            "client_id" => $clientId,
            "client_secret" => $clientSecret,
            "grant_type" => "refresh_token",
            "refresh_token" => $refreshToken,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            AthleteAccount::updateOrCreate(
                ["strava_athlete_id" => $athlete["athlete_id"]],
                [
                    "access_token" => $data["access_token"],
                    "refresh_token" => $data["refresh_token"],
                    "strava_athlete_id" => $athlete["athlete_id"],
                    "token_expires_at" => Carbon::parse(
                        $data["expires_at"]
                    )->format("Y-m-d H:i:s"),
                ]
            );

            return $response->json();
        }

        return $response->json();
    }
  public function saveStravaActivity($activities, $athlete)
    {
       
        $accessToken = $athlete->account->access_token;
        $refreshToken = $athlete->account->refresh_token;
        $strava_url = env('STRAVA_URL');
        foreach ($activities as  $key=> $activity) {
            
            if(!is_array($activity) || !isset($activity['id'])){
                continue;
            }
            if(!in_array($activity['type'], ['Ride','Run','Walk'])){
                continue;
            }
            $activityId = $activity['id'];
            $savedActivity = AthleteActivity::updateOrCreate(
                ["activity_id" => $activityId],
                [
                    "athlete_id" => $athlete["id"],
                    "athlete_strava_id" => $athlete["athlete_id"],
                    "name" => $activity["name"],
                    "date" => Carbon::parse($activity["start_date"]),
                    "distance" => $activity["distance"],
                    "moving_time" => $activity["moving_time"],
                    "elapsed_time" => $activity["elapsed_time"],
                    "type" => $activity["type"],
                    "sport_type" => $activity["sport_type"],
                    "workout_type" => $activity["workout_type"] ?? null,
                    "elevation" => $activity["total_elevation_gain"] ?? null,
                    "average_speed" => $activity["average_speed"] ?? null,
                    "max_speed" => $activity["max_speed"] ?? null,
                    "device_name" => $activity["device_name"] ?? null,
                    "average_watts" => $activity["average_watts"] ?? null,
                    "weighted_average_watts" => $activity["weighted_average_watts"] ?? null,
                    "relative_effort" => null,
                    "passed_zips" => null,
                    "timezone" => $activity["timezone"],
                    "status" => 1,
                ]
            );
            $savedActivityMap = AthleteActivityMap::updateOrCreate(
                [
                    "athlete_activity_id" => $activityId,
                ],
                [
                    "activity_id" => $savedActivity->id,
                    "athlete_id" => $athlete->id,
                    "map" => json_encode($activity["map"]),
                ]
            );
            
             
            if (!empty($activity["start_latlng"])) {
                FetchActivityLocation::dispatch($savedActivity->id,$activity["start_latlng"]);
            }
            
            $mapData = json_decode($savedActivityMap->map, true);
            
            if (!empty($mapData["summary_polyline"])) {
                $polyline = $mapData["summary_polyline"];
                FindZipActivityJob::dispatch($savedActivity->id,$polyline,$accessToken,$activityId)->onQueue('zips');
            }
            if ($key === 0) {
                event(new ActivityFetchCompleted('completed', $athlete["id"]));
            }    
            // FetchActivityPhotos::dispatch( $savedActivity->id,$activityId,$accessToken,$refreshToken,$athlete->id);
        }
    }
    // public function saveStravaActivity($activities, $athlete)
    // {
       
    //     $accessToken = $athlete->account->access_token;
    //     $refreshToken = $athlete->account->refresh_token;
    //     $strava_url = env('STRAVA_URL');
    //     foreach ($activities as $activity) {
    //         $activityId = $activity["id"];

    //         $lat = $activity["start_latlng"]?? $activity["start_latlng"][0];
    //         $lng =  $activity["start_latlng"]?? $activity["start_latlng"][1];
    //         $location ='';
    //         if($lat){
    //         $response = Http::withHeaders([
    //             "User-Agent" => "MyStravaClone/1.0 (admin@mydomain.com)",
    //             "Accept" => "application/json",
    //         ])
    //             ->timeout(10)
    //             ->get("https://nominatim.openstreetmap.org/reverse", [
    //                 "lat" => $lat,
    //                 "lon" => $lng,
    //                 "format" => "jsonv2",
    //             ]);
    //             Log::info('Activity location: '.json_encode($response->json()));
    //         $location = $response->json()["display_name"] ?? null;
    //         }

    //         $savedActivity = AthleteActivity::updateOrCreate(
    //             ["activity_id" => $activityId],
    //             [
    //                 "athlete_id" => $athlete["id"],
    //                 "athlete_strava_id" => $athlete["athlete_id"],
    //                 "name" => $activity["name"],
    //                 "date" => Carbon::parse($activity["start_date"]),
    //                 "distance" => $activity["distance"],
    //                 "moving_time" => $activity["moving_time"],
    //                 "elapsed_time" => $activity["elapsed_time"],
    //                 "type" => $activity["type"],
    //                 "sport_type" => $activity["sport_type"],
    //                 "workout_type" => $activity["workout_type"] ?? null,
    //                 "elevation" => $activity["total_elevation_gain"] ?? null,
    //                 "average_speed" => $activity["average_speed"] ?? null,
    //                 "max_speed" => $activity["max_speed"] ?? null,
    //                 "device_name" => $activity["device_name"] ?? null,
    //                 "average_watts" => $activity["average_watts"] ?? null,
    //                 "weighted_average_watts" => $activity["weighted_average_watts"] ?? null,
    //                 "relative_effort" => null,
    //                 "passed_zips" => null,
    //                 "start_location" => $location,
    //                 "timezone" => $activity["timezone"],
    //                 "status" => 1,
    //             ]
    //         );
    //         $savedActivityMap = AthleteActivityMap::updateOrCreate(
    //             [
    //                 "athlete_activity_id" => $activityId,
    //             ],
    //             [
    //                 "activity_id" => $savedActivity->id,
    //                 "athlete_id" => $athlete->id,
    //                 "map" => json_encode($activity["map"]),
    //             ]
    //         );
    //       //  Log::info($savedActivityMap);
    //         $mapData = json_decode($savedActivityMap->map, true);

    //         if (!empty($mapData["summary_polyline"])) {
    //             $polyline = $mapData["summary_polyline"];
    //             $zipCount=AthleteActivityZipStat::where('athlete_activity_id',$savedActivity->id)->count();
    //         //    if(!$zipCount)
    //             FindZipActivityJob::dispatch($savedActivity->id,$polyline,$accessToken,$activityId);
              
    //         }
    //         $photoUrls = [];
           
    //         $photoResponse = Http::withoutVerifying()
    //             ->withToken($accessToken)
    //             ->get(
    //                 "{$strava_url}/activities/{$activityId}/photos",
    //                 ["size" => 600]
    //             );

    //         if ($photoResponse->unauthorized()) {
    //             $tokenData = $this->refreshAthleteAccessToken(
    //                 $refreshToken,
    //                 $athlete
    //             );

    //             if (!isset($tokenData["access_token"])) {
    //                 continue;
    //             }

    //             $accessToken = $tokenData["access_token"];

    //             $photoResponse = Http::withoutVerifying()
    //                 ->withToken($accessToken)
    //                 ->get(
    //                     "{$strava_url}/activities/{$activityId}/photos",
    //                     ["size" => 600]
    //                 );
    //         }
    //         // Log::info("photoResponse");
    //         // Log::info(json_encode($photoResponse->json()));
    //         foreach ($photoResponse->json() ?? [] as $key => $photo) {
    //             if (isset($photo["urls"]) && !empty($photo["urls"])) {
    //                 $photoUrls[$key] = [
    //                     "url" =>
    //                         $photo["urls"]["600"] ??
    //                         array_values($photo["urls"])[0],
    //                     "type" => $photo["type"],
    //                     "video_url" => $photo["video_url"] ?? null,
    //                 ];
    //             }
    //             AthleteActivityMedia::updateOrCreate(
    //                 [
    //                     "athlete_activity_id" => $savedActivity["activity_id"],
    //                     "athlete_id" => $athlete["id"],
    //                 ],
    //                 [
    //                     "media" => json_encode($photoUrls),
    //                 ]
    //             );
    //         }
    //     }
    // }
    // public function updateStravaActivity($activityId)
    // {
    //     $accessToken = Setting::where("code", "strava_access_token")->value(
    //         "value"
    //     );
    //     $refreshToken = Setting::where("code", "strava_refresh_token")->value(
    //         "value"
    //     );
    //     $strava_url = env('STRAVA_URL');
    //     $activityResponse = Http::withoutVerifying()
    //         ->withToken($accessToken)
    //         ->get("{$strava_url}/activities/{$activityId}");

    //     if ($activityResponse->unauthorized()) {
    //         $tokenData = $this->refreshAccessToken($refreshToken);

    //         if (isset($tokenData["access_token"])) {
    //             $accessToken = $tokenData["access_token"];

    //             $activityResponse = Http::withToken($accessToken)->get(
    //                 "{$strava_url}/activities/{$activityId}"
    //             );
    //         } else {
    //             return response()->json(
    //                 ["error" => "Token refresh failed."],
    //                 401
    //             );
    //         }
    //     }

    //     if (!$activityResponse->successful()) {
    //         return response()->json(
    //             ["error" => "Failed to fetch activity."],
    //             400
    //         );
    //     }

    //     $activity = $activityResponse->json();

    //     $photoResponse = Http::withToken($accessToken)->get(
    //         "{$strava_url}/activities/{$activityId}/photos",
    //         [
    //             "size" => 600,
    //         ]
    //     );

    //     if ($photoResponse->unauthorized()) {
    //         $tokenData = $this->refreshAccessToken($refreshToken);

    //         if (isset($tokenData["access_token"])) {
    //             $accessToken = $tokenData["access_token"];

    //             $photoResponse = Http::withToken($accessToken)->get(
    //                 "{$strava_url}/activities/{$activityId}/photos",
    //                 [
    //                     "size" => 600,
    //                 ]
    //             );
    //         } else {
    //             return response()->json(
    //                 ["error" => "Token refresh failed."],
    //                 401
    //             );
    //         }
    //     }

    //     $photoUrls = [];
    //     foreach ($photoResponse->json() as $photo) {
    //         if (isset($photo["urls"]) && is_array($photo["urls"])) {
    //             $photoUrls[] =
    //                 $photo["urls"]["600"] ?? array_values($photo["urls"])[0];
    //         }
    //     }

    //     StravaActivity::updateOrCreate(
    //         ["strava_id" => $activityId],
    //         [
    //             "name" => $activity["name"],
    //             "date" => Carbon::parse($activity["start_date"]),
    //             "distance" => $activity["distance"],
    //             "moving_time" => $activity["moving_time"],
    //             "elapsed_time" => $activity["elapsed_time"],
    //             "type" => $activity["type"],
    //             "sport_type" => $activity["sport_type"],
    //             "workout_type" => $activity["workout_type"] ?? null,
    //             "photos" => $photoUrls,
    //             "status" => 1,
    //         ]
    //     );

    //     return response()->json([
    //         "message" => "Activity updated successfully!",
    //     ]);
    // }


    public function distancePerZip(string $polyline): array
    {
        $route = $this->decodePolyline($polyline);

        if (count($route) < 2) {
            return [];
        }

        $zipDistances = [];

        foreach ($this->zipFeatureStream() as $feature) {
            if (
                !is_array($feature) ||
                !isset($feature["geometry"]["type"]) ||
                $feature["geometry"]["type"] !== "Polygon"
            ) {
                continue;
            }

            $zip =
                $feature["properties"]["ZCTA5CE20"] ??
                ($feature["properties"]["GEOID20"] ??
                    ($feature["properties"]["zcta5ce20"] ?? null));

            if (!$zip) {
                continue;
            }

            $outerRing = $feature["geometry"]["coordinates"][0];

            for ($i = 0; $i < count($route) - 1; $i++) {
                $midPoint = [
                    ($route[$i][0] + $route[$i + 1][0]) / 2,
                    ($route[$i][1] + $route[$i + 1][1]) / 2,
                ];

                if ($this->pointInPolygon($midPoint, $outerRing)) {
                    $distance = $this->haversine($route[$i], $route[$i + 1]);

                    $zipDistances[$zip] =
                        ($zipDistances[$zip] ?? 0) + $distance;
                }
            }
        }

        return $zipDistances;
    }

    public function getActivityStreams(
        string $accessToken,
        int $activityId,
        array $keys = []
    ) {
        // Default streams you usually need
        if (empty($keys)) {
            $keys = ["time", "latlng", "distance", "velocity_smooth","altitude"];
        }
        $strava_url = env('STRAVA_URL');
        $response = Http::withoutVerifying()
            ->withToken($accessToken)
            ->get(
                "{$strava_url}/activities/{$activityId}/streams",
                [
                    "keys" => implode(",", $keys),
                    "key_by_type" => true,
                ]
            );
         $this->logApi('Strava', 'fetch', $activityId, 'GET', "{$strava_url}/activities/{$activityId}/streams", json_encode($keys), json_encode([]), 200, '');
          if ($response->failed()) {
            \Log::error("Strava streams failed", [
                "activity_id" => $activityId,
                "response" => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    }
  protected function calculateZipEntryStats(array $streams, array $zipPolygons): array
{
    $coords     = $streams["latlng"]["data"];
    $distances  = $streams["distance"]["data"];
    $times      = $streams["time"]["data"];
    $speeds     = $streams["velocity_smooth"]["data"] ?? [];
    $altitudes  = $streams["altitude"]["data"] ?? [];

    if (count($coords) < 2) return [];

    // --------------------------------------------------
    // 1. Find split index (turnaround point)
    // --------------------------------------------------
    $start = $coords[0];
    $maxIndex = 0;
    $maxDist = 0;

    foreach ($coords as $i => $point) {
        $d = $this->haversine(
            $start[0], $start[1],
            $point[0], $point[1]
        );

        if ($d > $maxDist) {
            $maxDist = $d;
            $maxIndex = $i;
        }
    }

    $zipStats = [];

    // --------------------------------------------------
    // 2. Loop through segments
    // --------------------------------------------------
    foreach ($coords as $i => $coord) {
        if ($i === 0) continue;

        // IMPORTANT: coords are [lat, lng] → convert to [lng, lat]
        $currPoint = [$coord[1], $coord[0]];

        $segmentDistance = $distances[$i] - $distances[$i - 1];
        $segmentTime     = $times[$i] - $times[$i - 1];

        if ($segmentDistance <= 0 || $segmentTime <= 0) continue;

        // elevation
        $segmentElevationGain = 0;
        if (isset($altitudes[$i], $altitudes[$i - 1])) {
            $diff = $altitudes[$i] - $altitudes[$i - 1];
            if ($diff > 0) {
                $segmentElevationGain = $diff;
            }
        }

        // determine direction
        $direction = ($i <= $maxIndex) ? 'up' : 'down';

        foreach ($zipPolygons as $zip => $polygon) {

            if (!$this->pointInPolygon($currPoint, $polygon)) {
                continue;
            }

            // init
            if (!isset($zipStats[$zip])) {
                $zipStats[$zip] = [
                    "up" => [
                        "distance_mi" => 0,
                        "elapsed_sec" => 0,
                        "moving_sec" => 0,
                        "speed_sum" => 0,
                        "speed_count" => 0,
                        "max_speed_mph" => 0,
                        "elevation_gain_ft" => 0,
                    ],
                    "down" => [
                        "distance_mi" => 0,
                        "elapsed_sec" => 0,
                        "moving_sec" => 0,
                        "speed_sum" => 0,
                        "speed_count" => 0,
                        "max_speed_mph" => 0,
                        "elevation_gain_ft" => 0,
                    ],
                ];
            }

            // distance
            $zipStats[$zip][$direction]["distance_mi"] += $segmentDistance / 1609.344;

            // time
            $zipStats[$zip][$direction]["elapsed_sec"] += $segmentTime;

            // moving time + speed
            if (isset($speeds[$i]) && $speeds[$i] > 0) {
                $zipStats[$zip][$direction]["moving_sec"] += $segmentTime;
                $zipStats[$zip][$direction]["speed_sum"] += $speeds[$i];
                $zipStats[$zip][$direction]["speed_count"]++;
            }

            // max speed
            if (isset($speeds[$i])) {
                $speedMph = $speeds[$i] * 2.23694;
                if ($speedMph > $zipStats[$zip][$direction]["max_speed_mph"]) {
                    $zipStats[$zip][$direction]["max_speed_mph"] = round($speedMph, 2);
                }
            }

            // elevation
            $zipStats[$zip][$direction]["elevation_gain_ft"] += $segmentElevationGain * 3.28084;

            break; // stop after first matching ZIP
        }
    }

    // --------------------------------------------------
    // 3. Final calculations
    // --------------------------------------------------
    foreach ($zipStats as $zip => $dirs) {
        foreach (['up', 'down'] as $dir) {

            $stats = $zipStats[$zip][$dir];

            // avg speed
            if ($stats["speed_count"] > 0) {
                $avgMs = $stats["speed_sum"] / $stats["speed_count"];
                $zipStats[$zip][$dir]["speed_mph"] = round($avgMs * 2.23694, 2);
            } else {
                $zipStats[$zip][$dir]["speed_mph"] = 0;
            }

            // rounding
            $zipStats[$zip][$dir]["distance_mi"] = round($stats["distance_mi"], 2);
            $zipStats[$zip][$dir]["elevation_gain_ft"] = round($stats["elevation_gain_ft"], 1);

            // cleanup
            unset(
                $zipStats[$zip][$dir]["speed_sum"],
                $zipStats[$zip][$dir]["speed_count"]
            );
        }
    }

    return $zipStats;
}

protected function getZipSequence(array $route, array $zipPolygons): array
{
    $zipSequence = [];
    $lastZip = null;

    foreach ($route as $i => $point) {

        // FIX: keep correct order [lng, lat]
        $lngLat = [$point[0], $point[1]];

        $currentZip = null;

        foreach ($zipPolygons as $zip => $polygon) {
            if ($this->pointInPolygon($lngLat, $polygon)) {
                $currentZip = $zip;
                break;
            }
        }

        // only add valid ZIP transitions
        if ($currentZip !== null && $currentZip !== $lastZip) {
            $zipSequence[] = [
                "zip" => $currentZip,
                "index" => $i
            ];
            $lastZip = $currentZip;
        }
    }

    return [
        "sequence" => array_column($zipSequence, 'zip'),
        "detailed" => $zipSequence,
        "start_zip" => $zipSequence[0]['zip'] ?? null,
        "end_zip" => !empty($zipSequence) ? end($zipSequence)['zip'] : null,
    ];
}
protected function formatZipDirectionalRows(array $zipSequence, array $zipStats): array
{
    $result = [];
    $order = 1;

    $sequence = $zipSequence['sequence']; // [78628, 78633, 78628]

    if (count($sequence) < 2) return [];

    // Find turnaround index (middle point)
    $turnIndex = floor(count($sequence) / 2);

    foreach ($sequence as $index => $zip) {

        // determine direction
        $direction = ($index <= $turnIndex) ? 'up' : 'down';

        if (!isset($zipStats[$zip][$direction])) {
            continue;
        }

        $stats = $zipStats[$zip][$direction];

        $result[] = [
            "zip" => $zip,
            "sort_order" => $order++,
            "direction" => $direction,
            "distance_mi" => $stats["distance_mi"] ?? 0,
            "elapsed_sec" => $stats["elapsed_sec"] ?? 0,
            "moving_sec" => $stats["moving_sec"] ?? 0,
            "speed_mph" => $stats["speed_mph"] ?? 0,
            "max_speed_mph" => $stats["max_speed_mph"] ?? 0,
            "elevation_gain_ft" => $stats["elevation_gain_ft"] ?? 0,
        ];
    }

    return $result;
}
protected function buildZipOrders(array $zipSequence): array
{
    $sequence = $zipSequence['sequence']; // e.g. [78628, 78633, 78628]

    $turnIndex = floor(count($sequence) / 2);

    $orders = [];

    // -----------------------
    // UP direction
    // -----------------------
    $order = 1;
    for ($i = 0; $i <= $turnIndex; $i++) {
        $zip = $sequence[$i];

        // avoid overwrite if repeated
        if (!isset($orders[$zip]['up'])) {
            $orders[$zip]['up'] = $order++;
        }
    }

    // -----------------------
    // DOWN direction
    // -----------------------
    $order = 1;
    for ($i = count($sequence) - 1; $i > $turnIndex; $i--) {
        $zip = $sequence[$i];

        if (!isset($orders[$zip]['down'])) {
            $orders[$zip]['down'] = $order++;
        }
    }

    return $orders;
}
public function disconnectStrava($athlete)
{
    $accessToken = $athlete->account->access_token;
    $strava_auth_url = env('STRAVA_AUTH_URL');
    $response = Http::withToken($accessToken)
        ->post("{$strava_auth_url}/deauthorize");
    $this->logApi('Strava', 'POST', $athlete->athlete_id, 'post', "{$strava_auth_url}/deauthorize", json_encode([]), json_encode($response), 200, '');
    if ($response->successful()) {
       Log::error('Strava Deauthorized', [
            'response' => $response->body()
        ]);

        return true;
    } else {
        Log::error('Strava Deauthorize Failed', [
            'response' => $response->body()
        ]);

        return false;
    }
}
public function archiveAthleteData($athleteId)
{
    DB::transaction(function () use ($athleteId) {

      $exists = DB::table('athlete_archive_tables')
        ->where('id', $athleteId)
        ->exists();
    if (!$exists) {
        DB::table('athlete_archive_tables')->insertUsing(
            [
                'athlete_id','first_name','last_name','email','password',
                'city','state','country','sex','profile_medium','profile','archived_at'
            ],
            DB::table('athletes')
                ->selectRaw("
                    id as athlete_id, first_name, last_name, email, password,
                    city, state, country, sex, profile_medium, profile, NOW()
                ")
                ->where('id', $athleteId)
        );

        DB::table('athlete_accounts_archive_tables')->insertUsing(
            [
                'athlete_id','strava_athlete_id','access_token',
                'refresh_token','token_expires_at','archived_at'
            ],
            DB::table('athlete_accounts')
                ->selectRaw("
                    athlete_id, strava_athlete_id, access_token,
                    refresh_token, token_expires_at, NOW()
                ")
                ->where('athlete_id', $athleteId)
        );
    }
    $exists = DB::table('athlete_activities_archive_tables')
        ->where('athlete_id', $athleteId)
        ->exists();

    if (!$exists) {
        DB::table('athlete_activities_archive_tables')->insertUsing(
            [
                'activity_id','athlete_id','athlete_strava_id','name','distance','moving_time',
                'elapsed_time','type','sport_type','workout_type','elevation','relative_effort',
                'map','passed_zips','start_location','end_location','average_speed','max_speed',
                'device_name','average_watts','weighted_average_watts','date','timezone','archived_at'
            ],
            DB::table('athlete_activities')
                ->selectRaw("
                    activity_id, athlete_id, athlete_strava_id, name, distance, moving_time,
                    elapsed_time, type, sport_type, workout_type, elevation, relative_effort,
                    map, passed_zips, start_location, end_location, average_speed, max_speed,
                    device_name, average_watts, weighted_average_watts, date, timezone, NOW()
                ")
                ->where('athlete_id', $athleteId)
        );
    }
       $exists = DB::table('athlete_activity_maps_archive_tables')
        ->where('athlete_id', $athleteId)
        ->exists();
        if (!$exists) {
        DB::table('athlete_activity_maps_archive_tables')->insertUsing(
            ['athlete_activity_id','activity_id','athlete_id','map','archived_at'],
            DB::table('athlete_activity_maps')
                ->selectRaw("
                    athlete_activity_id, activity_id, athlete_id, map, NOW()
                ")
                ->where('athlete_id', $athleteId)
        );

     DB::table('athlete_activity_zip_stats_archive_tables')->insertUsing(
            [
                'athlete_activity_id','athlete_id','zip_code','distance_mi',
                'elevation_gain_ft','elapsed_sec','moving_sec','speed_mph',
                'max_speed_mph','distance_mi_up','distance_mi_down','speed_mph_up','speed_mph_down','sort_order','sort_order_down','date','rank','archived_at'
            ],
            DB::table('athlete_activity_zip_stats')
                ->selectRaw("
                    athlete_activity_id, athlete_id, zip_code, distance_mi,
                    elevation_gain_ft, elapsed_sec, moving_sec, speed_mph,
                    max_speed_mph,distance_mi_up,distance_mi_down,speed_mph_up,speed_mph_down,sort_order,sort_order_down, date, `rank`, NOW()
                ")
                ->where('athlete_id', $athleteId)
        );
        }
        
        Athlete::where('id', $athleteId)->forceDelete();
        AthleteAccount::where('athlete_id', $athleteId)->forceDelete();
        AthleteActivity::where('athlete_id', $athleteId)->forceDelete();
        AthleteActivityZipStat::wherehas('activity')->where('athlete_id', $athleteId)->forceDelete();
        AthleteActivityMap::where('athlete_id', $athleteId)->forceDelete();
     });
}
protected function haversine($lat1, $lng1, $lat2, $lng2): float
{
    $earthRadius = 6371000; // meters

    $lat1 = deg2rad($lat1);
    $lng1 = deg2rad($lng1);
    $lat2 = deg2rad($lat2);
    $lng2 = deg2rad($lng2);

    $dLat = $lat2 - $lat1;
    $dLng = $lng2 - $lng1;

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos($lat1) * cos($lat2) *
         sin($dLng / 2) * sin($dLng / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}
   
}
