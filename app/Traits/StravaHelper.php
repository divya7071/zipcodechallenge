<?php

namespace App\Traits;

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
use Polyline;

trait StravaHelper
{
    use ZipIntersectionTrait;
    public function buildStravaAuthorizationUrl(array $params = [])
    {
        $defaultParams = [
            "response_type" => "code",
            "approval_prompt" => "force",
            "scope" => "read,activity:read,activity:write",
        ];

        $queryParams = array_merge($defaultParams, $params);

        return "https://www.strava.com/oauth/authorize?" .
            http_build_query($queryParams);
    }

    public function getStravaAccessToken(Request $request)
    {
        $clientId = Setting::where("code", "client_id")->value("value");
        $clientSecret = Setting::where("code", "client_secret")->value("value");
        $redirectUri = route("admin.handleCallback");

        $responseToken = Http::post("https://www.strava.com/oauth/token", [
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
        $page = 1;
        $lastActivity = AthleteActivity::where("athlete_id", $athlete->id)
            ->orderBy("date", "desc")
            ->first();
        if (!$lastActivity) {
            $page = 1;
            do {
                $params = [
                    "page" => $page,
                    "per_page" => $perPage,
                ];
                $activityResponse = Http::withoutVerifying()
                    ->withToken($accessToken)
                    ->get(
                        "https://www.strava.com/api/v3/athlete/activities",
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
                            "https://www.strava.com/api/v3/athlete/activities",
                            [
                                "page" => $page,
                                "per_page" => $perPage,
                            ]
                        );
                }

                $activities = $activityResponse->json();
                if (empty($activities)) {
                    break;
                }
                $this->saveStravaActivity($activities, $athlete);
                // Save here

                $page++;
                sleep(30);
            } while (count($activities) == 200);
        } else {
           // $after = $lastActivity ? strtotime($lastActivity->date) : null;
            $after =$lastActivity
            ? Carbon::parse($lastActivity->date)->subDays(2)->timestamp
            : null;
            do {
                // log::info($page);
                $params = [
                    "page" => $page,
                    "per_page" => $perPage,
                ];

                if ($after) {
                    $params["after"] = $after;
                }
                $activityResponse = Http::withoutVerifying()
                    ->withToken($accessToken)
                    ->get(
                        "https://www.strava.com/api/v3/athlete/activities",
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
                            "https://www.strava.com/api/v3/athlete/activities",
                            [
                                "page" => $page,
                                "per_page" => $perPage,
                            ]
                        );
                }

                $activities = $activityResponse->json();
                if (empty($activities)) {
                    break;
                }
               Log::info('Activity '.json_encode($activities));
                $this->saveStravaActivity($activities, $athlete);
                $page++;
                sleep(30);
            } while (count($activities) == $perPage);
        }
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

        $response = Http::asForm()->post("https://www.strava.com/oauth/token", [
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
        foreach ($activities as $activity) {
            $activityId = $activity["id"];

            $lat = $activity["start_latlng"]?? $activity["start_latlng"][0];
            $lng =  $activity["start_latlng"]?? $activity["start_latlng"][1];
            $location ='';
            if($lat){
            $response = Http::withHeaders([
                "User-Agent" => "MyStravaClone/1.0 (admin@mydomain.com)",
                "Accept" => "application/json",
            ])
                ->timeout(10)
                ->get("https://nominatim.openstreetmap.org/reverse", [
                    "lat" => $lat,
                    "lon" => $lng,
                    "format" => "jsonv2",
                ]);
                Log::info('Activity location: '.json_encode($response->json()));
            $location = $response->json()["display_name"] ?? null;
            }

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
                    "start_location" => $location,
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
          //  Log::info($savedActivityMap);
            $mapData = json_decode($savedActivityMap->map, true);

            if (!empty($mapData["summary_polyline"])) {
                $polyline = $mapData["summary_polyline"];
                $zipCount=AthleteActivityZipStat::where('athlete_activity_id',$savedActivity->id)->count();
            //    if(!$zipCount)
                FindZipActivityJob::dispatch($savedActivity->id,$polyline,$accessToken,$activityId);
              
            }
            $photoUrls = [];

            $photoResponse = Http::withoutVerifying()
                ->withToken($accessToken)
                ->get(
                    "https://www.strava.com/api/v3/activities/{$activityId}/photos",
                    ["size" => 600]
                );

            if ($photoResponse->unauthorized()) {
                $tokenData = $this->refreshAthleteAccessToken(
                    $refreshToken,
                    $athlete
                );

                if (!isset($tokenData["access_token"])) {
                    continue;
                }

                $accessToken = $tokenData["access_token"];

                $photoResponse = Http::withoutVerifying()
                    ->withToken($accessToken)
                    ->get(
                        "https://www.strava.com/api/v3/activities/{$activityId}/photos",
                        ["size" => 600]
                    );
            }
            // Log::info("photoResponse");
            // Log::info(json_encode($photoResponse->json()));
            foreach ($photoResponse->json() ?? [] as $key => $photo) {
                if (isset($photo["urls"]) && !empty($photo["urls"])) {
                    $photoUrls[$key] = [
                        "url" =>
                            $photo["urls"]["600"] ??
                            array_values($photo["urls"])[0],
                        "type" => $photo["type"],
                        "video_url" => $photo["video_url"] ?? null,
                    ];
                }
                AthleteActivityMedia::updateOrCreate(
                    [
                        "athlete_activity_id" => $savedActivity["activity_id"],
                        "athlete_id" => $athlete["id"],
                    ],
                    [
                        "media" => json_encode($photoUrls),
                    ]
                );
            }
        }
    }
    public function updateStravaActivity($activityId)
    {
        $accessToken = Setting::where("code", "strava_access_token")->value(
            "value"
        );
        $refreshToken = Setting::where("code", "strava_refresh_token")->value(
            "value"
        );

        $activityResponse = Http::withoutVerifying()
            ->withToken($accessToken)
            ->get("https://www.strava.com/api/v3/activities/{$activityId}");

        if ($activityResponse->unauthorized()) {
            $tokenData = $this->refreshAccessToken($refreshToken);

            if (isset($tokenData["access_token"])) {
                $accessToken = $tokenData["access_token"];

                $activityResponse = Http::withToken($accessToken)->get(
                    "https://www.strava.com/api/v3/activities/{$activityId}"
                );
            } else {
                return response()->json(
                    ["error" => "Token refresh failed."],
                    401
                );
            }
        }

        if (!$activityResponse->successful()) {
            return response()->json(
                ["error" => "Failed to fetch activity."],
                400
            );
        }

        $activity = $activityResponse->json();

        $photoResponse = Http::withToken($accessToken)->get(
            "https://www.strava.com/api/v3/activities/{$activityId}/photos",
            [
                "size" => 600,
            ]
        );

        if ($photoResponse->unauthorized()) {
            $tokenData = $this->refreshAccessToken($refreshToken);

            if (isset($tokenData["access_token"])) {
                $accessToken = $tokenData["access_token"];

                $photoResponse = Http::withToken($accessToken)->get(
                    "https://www.strava.com/api/v3/activities/{$activityId}/photos",
                    [
                        "size" => 600,
                    ]
                );
            } else {
                return response()->json(
                    ["error" => "Token refresh failed."],
                    401
                );
            }
        }

        $photoUrls = [];
        foreach ($photoResponse->json() as $photo) {
            if (isset($photo["urls"]) && is_array($photo["urls"])) {
                $photoUrls[] =
                    $photo["urls"]["600"] ?? array_values($photo["urls"])[0];
            }
        }

        StravaActivity::updateOrCreate(
            ["strava_id" => $activityId],
            [
                "name" => $activity["name"],
                "date" => Carbon::parse($activity["start_date"]),
                "distance" => $activity["distance"],
                "moving_time" => $activity["moving_time"],
                "elapsed_time" => $activity["elapsed_time"],
                "type" => $activity["type"],
                "sport_type" => $activity["sport_type"],
                "workout_type" => $activity["workout_type"] ?? null,
                "photos" => $photoUrls,
                "status" => 1,
            ]
        );

        return response()->json([
            "message" => "Activity updated successfully!",
        ]);
    }


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

        $response = Http::withoutVerifying()
            ->withToken($accessToken)
            ->get(
                "https://www.strava.com/api/v3/activities/{$activityId}/streams",
                [
                    "keys" => implode(",", $keys),
                    "key_by_type" => true,
                ]
            );

        if ($response->failed()) {
            \Log::error("Strava streams failed", [
                "activity_id" => $activityId,
                "response" => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    }
    protected function calculateZipEntryStats(array $streams,array $zipPolygons): array 
    {
        $coords = $streams["latlng"]["data"];
        $distances = $streams["distance"]["data"];
        $times = $streams["time"]["data"];
        $speeds = $streams["velocity_smooth"]["data"] ?? [];
        $altitudes = $streams["altitude"]["data"] ?? [];
        $totalTime = end($times);
        $segmentElevationGain = 0;
        $zipStats = [];

      
        foreach ($coords as $i => $coord) {
            if ($i === 0) {
                continue;
            } // skip first point, need a previous point

            $prevPoint = [$coords[$i - 1][1], $coords[$i - 1][0]]; // [lng, lat]
            $currPoint = [$coord[1], $coord[0]];

            // Segment distance and time
            $segmentDistance = $distances[$i] - $distances[$i - 1]; // meters
            $segmentTime = $times[$i] - $times[$i - 1]; // seconds
            if (isset($altitudes[$i]) && isset($altitudes[$i - 1])) {
            $elevationDiff = $altitudes[$i] - $altitudes[$i - 1];

            if ($elevationDiff > 0) {
                $segmentElevationGain = $elevationDiff; // meters
            }
}
            foreach ($zipPolygons as $zip => $polygon) {
                // Check if current point is inside ZIP
                if ($this->pointInPolygon($currPoint, $polygon)) {
                    if (!isset($zipStats[$zip])) {
                        $zipStats[$zip] = [
                            "distance_mi" => 0,
                            "elapsed_sec" => 0,
                            "moving_sec" => 0,   
                            "speed_mph" => 0,
                            "speed_sum" => 0,
                            "speed_count" => 0,
                            "max_speed_mph" => 0,  
                            "elevation_gain_ft" => 0,
                        ];

                    }

                    // Add segment distance and time to this ZIP
                    $zipStats[$zip]["distance_mi"] +=
                        $segmentDistance / 1609.344; // miles
                    // $zipStats[$zip]["elapsed_sec"] += $segmentTime;
                    $zipStats[$zip]["elapsed_sec"] += $segmentTime;
                    // Add moving time only if speed > 0
                    if (isset($speeds[$i]) && $speeds[$i] > 0) {
                        $zipStats[$zip]["moving_sec"] += $segmentTime;
                    }

                    // Update average speed in mph
                    // if ($zipStats[$zip]["elapsed_sec"] > 0) {
                    //     $zipStats[$zip]["speed_mph"] = round(
                    //         $zipStats[$zip]["distance_mi"] /
                    //             ($zipStats[$zip]["elapsed_sec"] / 3600),
                    //         2
                    //     );
                    // }
                    $speedMph = $speeds[$i] * 2.23694;
                    if (isset($speeds[$i]) && $speeds[$i] > 0) {
                        $zipStats[$zip]["speed_sum"] += $speeds[$i];
                        $zipStats[$zip]["speed_count"]++;
                    }
                    if ($speedMph > $zipStats[$zip]["max_speed_mph"]) {
                        $zipStats[$zip]["max_speed_mph"] = round($speedMph, 2);
                    }
                    $zipStats[$zip]["elevation_gain_ft"] += $segmentElevationGain * 3.28084;
                }
            }
        }

        // Round distance for cleaner output
        foreach ($zipStats as $zip => $stats) {
        
            if ($stats["speed_count"] > 0) {
                $avgSpeedMs = $stats["speed_sum"] / $stats["speed_count"];
                $zipStats[$zip]["speed_mph"] = round($avgSpeedMs * 2.23694, 2);
            }
            $zipStats[$zip]["distance_mi"] = round($stats["distance_mi"], 2);
            $zipStats[$zip]["elevation_gain_ft"] = round($stats["elevation_gain_ft"], 1);
            unset($zipStats[$zip]["speed_sum"], $zipStats[$zip]["speed_count"]);
        }

        return $zipStats;
    }

    // protected function calculateZipEntryStats(array $streams, array $zipPolygons): array
    // {
    //     $coords     = $streams['latlng']['data'];
    //     $distances  = $streams['distance']['data'];
    //     $times      = $streams['time']['data'];
    //     $speeds     = $streams['velocity_smooth']['data'] ?? [];

    //     $zipStats = [];
    //     $entered  = [];

    //     foreach ($coords as $i => $coord) {

    //         foreach ($zipPolygons as $zip => $polygon) {

    //             if (isset($entered[$zip])) continue;
    //             $point = [$coord[1], $coord[0]];
    //             if ($this->pointInPolygon($point, $polygon)) {

    //                 $zipStats[$zip] = [
    //                     'distance_km' => round($distances[$i] / 1000, 2),
    //                     'speed_kmh'   => isset($speeds[$i])
    //                         ? round($speeds[$i] * 3.6, 2)
    //                         : null,
    //                     'elapsed_sec' => $times[$i],
    //                 ];

    //                 $entered[$zip] = true;
    //             }
    //         }
    //     }

    //     return $zipStats;
    // }
}
