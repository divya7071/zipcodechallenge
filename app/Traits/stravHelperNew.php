<?php

namespace App\Traits;

use App\Models\AthleteAccount;
use App\Models\AthleteActivity;
use App\Models\Setting;
use App\Models\StravaActivity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Traits\ZipIntersectionTrait;
use Polyline;

trait StravaHelperNew
{
    use ZipIntersectionTrait;
    public function buildStravaAuthorizationUrl(array $params = [])
    {
        $defaultParams = [
            'response_type' => 'code',
            'approval_prompt' => 'force',
            'scope' => 'read,activity:read,activity:write',
        ];
    
        $queryParams = array_merge($defaultParams, $params);
    
        return 'https://www.strava.com/oauth/authorize?' . http_build_query($queryParams);
    }
    

    public function getStravaAccessToken(Request $request)
    {
        $clientId = Setting::where('code', 'client_id')->value('value');
        $clientSecret = Setting::where('code', 'client_secret')->value('value');
        $redirectUri = route('admin.handleCallback');
    
        $responseToken = Http::post('https://www.strava.com/oauth/token', [
            // 'client_id' => '193132',
            // 'client_secret' => '5af50729cdec38b692bb93f8bb931a5622a26e21',
            'client_id' => $clientId,
            'client_secret' =>$clientSecret,
            'code' => $request->code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ]);
    
        if ($responseToken->successful()) {
            $data = $responseToken->json();
    
            Setting::updateOrCreate(['code' => 'strava_access_token'], ['value' => $data['access_token']]);
            Setting::updateOrCreate(['code' => 'strava_refresh_token'], ['value' => $data['refresh_token']]);
    
            return redirect()->route('admin.settings.index', ['platform' => 'strava'])
                             ->with('success_message', 'Strava authorized successfully!');
        }
    
        $error = $responseToken->json()['message'] ?? 'Unknown error occurred.';
    
        return redirect()->route('admin.settings.index', ['platform' => 'strava'])
                         ->with('failure_message', 'Authorization failed. Error: ' .$error);
    }
    public function syncAthleteActivities($athlete, $perPage)
    {
        $page = 1;
        $accessToken  = $athlete->account->access_token;
        $refreshToken = $athlete->account->refresh_token;
        do {
            $response = $this->stravaGet(
                $athlete,
                'https://www.strava.com/api/v3/athlete/activities',
                [
                    'page'     => $page,
                    'per_page' => $perPage
                ]
            );
            
            $activities = $response->json();

            if (empty($activities)) {
                break;
            }

            foreach ($activities as $activity) {
                $this->storeActivity($athlete, $activity,$accessToken, $refreshToken);
            }

            $page++;
        } while (count($activities) === $perPage);
    }

     private function stravaGet($athlete, $url, $params = [])
    {
        $accessToken  = $athlete->account->access_token;
        $refreshToken = $athlete->account->refresh_token;

        $response = Http::withoutVerifying()
            ->withToken($accessToken)
            ->get($url, $params);

        if ($response->unauthorized()) {
            $tokenData = $this->refreshAthleteAccessToken($refreshToken, $athlete);

            if (!isset($tokenData['access_token'])) {
                throw new \Exception('Token refresh failed');
            }

            $response = Http::withoutVerifying()
                ->withToken($tokenData['access_token'])
                ->get($url, $params);
        }

        return $response;
    }

    private function storeActivity($athlete, $activity,$accessToken)
    {
        $activityId = $activity['id'];
        $activityId = $activity['id'];

        $photoResponse = Http::withToken($accessToken)
            ->get("https://www.strava.com/api/v3/activities/{$activityId}/photos", [
                'size' => 600,
            ]);

        if ($photoResponse->unauthorized()) {
            $tokenData = $this->refreshAthleteAccessToken($refreshToken, $athlete);

            if (!isset($tokenData['access_token'])) {
                \Log::error("Photo token refresh failed for activity {$activityId}");
                return;
            }

            $accessToken = $tokenData['access_token'];

            $photoResponse = Http::withToken($accessToken)
                ->get("https://www.strava.com/api/v3/activities/{$activityId}/photos", [
                    'size' => 600,
                ]);
        }

        $photoUrls = [];
        foreach ($photoResponse->json() ?? [] as $photo) {
            if (!empty($photo['urls'])) {
                $photoUrls[] = $photo['urls']['600']
                    ?? array_values($photo['urls'])[0];
            }
        }
        // $photoResponse = $this->stravaGet(
        //     $athlete,
        //     "https://www.strava.com/api/v3/activities/{$activityId}/photos",
        //     ['size' => 600]
        // );

        // $photoUrls = [];
        // foreach ($photoResponse->json() ?? [] as $photo) {
        //     if (!empty($photo['urls'])) {
        //         $photoUrls[] = $photo['urls']['600'] ?? array_values($photo['urls'])[0];
        //     }
        // }

        $record = AthleteActivity::updateOrCreate(
            ['activity_id' => $activityId],
            [
                'athlete_id'        => $athlete->id,
                'athlete_strava_id' => $athlete->athlete_id,
                'name'              => $activity['name'],
                'date'              => Carbon::parse($activity['start_date']),
                'distance'          => $activity['distance'],
                'moving_time'       => $activity['moving_time'],
                'elapsed_time'      => $activity['elapsed_time'],
                'type'              => $activity['type'],
                'sport_type'        => $activity['sport_type'],
                'workout_type'      => $activity['workout_type'] ?? null,
                'photos'            => $photoUrls,
                'elevation'         => $activity['total_elevation_gain'] ?? null,
                'timezone'          => $activity['timezone'],
                'status'            => 1,
            ]
        );

        if (!empty($activity['map']['polyline'])) {
            $record->passed_zips = $this->findZipsFromPolyline(
                $activity['map']['summary_polyline']
            );
            $record->save();
        }
    }
  public function getAthleteStravaActivities($athlete, $perPage = 50)
    {
        $accessToken  = $athlete->account->access_token;
        $refreshToken = $athlete->account->refresh_token;
         Log::info(json_encode([$accessToken,$refreshToken])); 
        $page = 1;

        do {
            $activityResponse = Http::withoutVerifying()
                ->withToken($accessToken)
                ->get('https://www.strava.com/api/v3/athlete/activities', [
                    'page'     => $page,
                    'per_page' => $perPage,
                ]);

            if ($activityResponse->unauthorized()) {
                $tokenData = $this->refreshAthleteAccessToken($refreshToken, $athlete);
                Log::info(json_encode($tokenData));
                if (!isset($tokenData['access_token'])) {
                    \Log::error("Strava token refresh failed for athlete {$athlete->id}");
                    return;
                }

                $accessToken = $tokenData['access_token'];

                $activityResponse = Http::withoutVerifying()
                    ->withToken($accessToken)
                    ->get('https://www.strava.com/api/v3/athlete/activities', [
                        'page'     => $page,
                        'per_page' => $perPage,
                    ]);
            }

            $activities = $activityResponse->json();
            
            if (empty($activities)) {
                break;
            }

            foreach ($activities as $activity) {
                $this->storeActivity($athlete, $activity, $accessToken, $refreshToken);
            }

            $page++;

          
            usleep(300000); 

        } while (count($activities) === $perPage);
    }

    // public function findIntersectZip($activity)
    // {
    
    //     $mapData = json_decode($activity->map, true);

    //     if (empty($mapData['polyline'])) {
    //         return; 
    //     }
    //     $coordinates = \Polyline::decode($mapData['polyline']);
    //     if (count($coordinates) < 2) {
    //         return; 
    //     }
       
    //     $geojsonCoords = array_map(fn($p) => [$p[1], $p[0]], $coordinates);
    //     $simplifiedCoords = [];
    //     foreach ($geojsonCoords as $i => $coord) {
    //         if ($i % 10 === 0) {
    //             $simplifiedCoords[] = $coord;
    //         }
    //     }

    //     if (count($simplifiedCoords) < 2) {
    //         return;
    //     }

    //     $lineStringJson = json_encode([
    //         'type' => 'LineString',
    //         'coordinates' => $simplifiedCoords
    //     ]);
    //     $zips = DB::table('zip_codes')
    //         ->select('zip_code')
    //         ->whereRaw(
    //             "MBRIntersects(boundary_geo, ST_GeomFromGeoJSON(?))",
    //             [$lineStringJson]
    //         )
    //         ->whereRaw(
    //             "ST_Intersects(boundary_geo, ST_GeomFromGeoJSON(?))",
    //             [$lineStringJson]
    //         )
    //         ->pluck('zip_code')   
    //         ->unique()
    //         ->values()
    //         ->toArray();
    //     $activity->passed_zips = $zips;
    //     $activity->save();
    // }
    // public function findIntersectZipWithoutDB($activity)
    // {
    //     $map = json_decode($activity->map, true);

    //     if (empty($map['polyline'])) return [];

    //     $route = $this->decodePolyline($map['polyline']);
    //     $routeBBox = $this->bbox($route);

    //     // Load ZIP GeoJSON (once if possible)
    //     $zipData = json_decode(
    //         file_get_contents(storage_path('app/zips/zip_codes.geojson')),
    //         true
    //     );

    //     $matchedZips = [];

    //     foreach ($zipData['features'] as $feature) {

    //         $zip = $feature['properties']['ZCTA5CE10'] ?? null;
    //         $geom = $feature['geometry'];

    //         if (!$zip || $geom['type'] !== 'Polygon') continue;

    //         $poly = $geom['coordinates'];

    //         // Bounding box filter
    //         $polyBBox = $this->bbox($poly[0]);
    //         if (!$this->bboxIntersects($routeBBox, $polyBBox)) continue;

    //         // Precise intersection
    //         if ($this->lineIntersectsPolygon($route, $poly)) {
    //             $matchedZips[] = $zip;
    //         }
    //     }

    //     return array_values(array_unique($matchedZips));
    // }

    public function refreshAthleteAccessToken($refreshToken,$athlete)
    {
        //dd($refreshToken);
        $clientId = Setting::where('code', 'client_id')->value('value');
        $clientSecret = Setting::where('code', 'client_secret')->value('value');
      
     $response = Http::asForm()->post('https://www.strava.com/oauth/token', [
            // 'client_id' => '193132',
            // 'client_secret' => '5af50729cdec38b692bb93f8bb931a5622a26e21',
            'client_id' => $clientId,
            'client_secret' =>$clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            AthleteAccount::updateOrCreate(
                                ['strava_athlete_id'=>$athlete['athlete_id']],
                                ['access_token' => $data['access_token'],
                                'refresh_token'=>$data['refresh_token'],
                                'strava_athlete_id'=>$athlete['athlete_id'],
                                'token_expires_at'=>Carbon::parse($data['expires_at'])->format('Y-m-d H:i:s')
                                ]
                            );
                                            
            return $response->json();
        }

        return $response->json();
    }
    public function getStravaActivities()
    {
        $accessToken = Setting::where('code','strava_access_token')->value('value');
        $refreshToken = Setting::where('code','strava_refresh_token')->value('value');

        $activityResponse = Http::withoutVerifying()->withToken($accessToken)
            ->get('https://www.strava.com/api/v3/athlete/activities');

            
        if ($activityResponse->unauthorized()) {
            $tokenData = $this->refreshAccessToken($refreshToken);
             
            if (isset($tokenData['access_token'])) {
                $newAccessToken = $tokenData['access_token'];

                $activityResponse = Http::withoutVerifying()->withToken($newAccessToken)
                    ->get('https://www.strava.com/api/v3/athlete/activities');
                   

            } else {
                return redirect()->route('admin.strava.index')
                    ->with('failure_message', 'Token refresh failed.');
            }
        }

        $activities = $activityResponse->json();
     
        foreach ($activities as $activity) {

            $activityId = $activity['id'];
            $stravaActivity =  Http::withToken($accessToken)
            ->get("https://www.strava.com/api/v3/activities/{$activityId}/photos", [
                'size' => 600
            ]);
            $allPhotos = $stravaActivity->json();

            if ($stravaActivity->unauthorized()) {
                $tokenData = $this->refreshAccessToken($refreshToken);
    
                if (isset($tokenData['access_token'])) {
                    $newAccessToken = $tokenData['access_token'];
    
                    $stravaActivity = Http::withToken($newAccessToken)
                    ->get("https://www.strava.com/api/v3/activities/{$activityId}/photos", [
                        'size' => 600 
                    ]);
                    $allPhotos = $stravaActivity->json();
                } else {
                    return redirect()->route('admin.strava.index')
                        ->with('failure_message', 'Token refresh failed.');
                }
            }

            $activityDetails = $stravaActivity->json();

            $photoUrls = [];
            foreach ($allPhotos as $photo) {
                if (isset($photo['urls']) && is_array($photo['urls'])) {
                    $photoUrls[] = $photo['urls']['600'] ?? array_values($photo['urls'])[0];
                }
            }
            StravaActivity::updateOrCreate(
                ['strava_id' => $activityId],
                [
                    'name' => $activity['name'],
                    'date' => Carbon::parse($activity['start_date']),
                    'distance' => $activity['distance'],
                    'moving_time' => $activity['moving_time'],
                    'elapsed_time' => $activity['elapsed_time'],
                    'type' => $activity['type'],
                    'sport_type' => $activity['sport_type'],
                    'workout_type' => $activity['workout_type'] ?? null,
                    'photos' => $photoUrls,
                    'status' => 1,
                ]
            );
        }

        return redirect()->route('admin.strava.index')
            ->with('success_message', 'Strava activities synced successfully!');
    }

    public function updateStravaActivity($activityId)
    {
        $accessToken = Setting::where('code', 'strava_access_token')->value('value');
        $refreshToken = Setting::where('code', 'strava_refresh_token')->value('value');

        $activityResponse = Http::withoutVerifying()->withToken($accessToken)
            ->get("https://www.strava.com/api/v3/activities/{$activityId}");

        if ($activityResponse->unauthorized()) {
            $tokenData = $this->refreshAccessToken($refreshToken);

            if (isset($tokenData['access_token'])) {
                $accessToken = $tokenData['access_token'];

                $activityResponse = Http::withToken($accessToken)
                    ->get("https://www.strava.com/api/v3/activities/{$activityId}");
            } else {
                return response()->json(['error' => 'Token refresh failed.'], 401);
            }
        }

        if (!$activityResponse->successful()) {
            return response()->json(['error' => 'Failed to fetch activity.'], 400);
        }

        $activity = $activityResponse->json();

        $photoResponse = Http::withToken($accessToken)
            ->get("https://www.strava.com/api/v3/activities/{$activityId}/photos", [
                'size' => 600
            ]);

        if ($photoResponse->unauthorized()) {
            $tokenData = $this->refreshAccessToken($refreshToken);

            if (isset($tokenData['access_token'])) {
                $accessToken = $tokenData['access_token'];

                $photoResponse = Http::withToken($accessToken)
                    ->get("https://www.strava.com/api/v3/activities/{$activityId}/photos", [
                        'size' => 600
                    ]);
            } else {
                return response()->json(['error' => 'Token refresh failed.'], 401);
            }
        }

        $photoUrls = [];
        foreach ($photoResponse->json() as $photo) {
            if (isset($photo['urls']) && is_array($photo['urls'])) {
                $photoUrls[] = $photo['urls']['600'] ?? array_values($photo['urls'])[0];
            }
        }

        StravaActivity::updateOrCreate(
            ['strava_id' => $activityId],
            [
                'name' => $activity['name'],
                'date' => Carbon::parse($activity['start_date']),
                'distance' => $activity['distance'],
                'moving_time' => $activity['moving_time'],
                'elapsed_time' => $activity['elapsed_time'],
                'type' => $activity['type'],
                'sport_type' => $activity['sport_type'],
                'workout_type' => $activity['workout_type'] ?? null,
                'photos' => $photoUrls,
                'status' => 1,
            ]
        );

        return response()->json(['message' => 'Activity updated successfully!']);
    }

    public function refreshAccessToken($refreshToken)
    {
        //dd($refreshToken);
        $clientId = Setting::where('code', 'client_id')->value('value');
        $clientSecret = Setting::where('code', 'client_secret')->value('value');
     $response = Http::asForm()->post('https://www.strava.com/oauth/token', [
            'client_id' => '193132',
            'client_secret' => '5af50729cdec38b692bb93f8bb931a5622a26e21',
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            Setting::updateOrCreate(['code' => 'strava_access_token'], ['value' => $data['access_token']]);
            Setting::updateOrCreate(['code' => 'strava_refresh_token'], ['value' => $data['refresh_token']]); 
            return $response->json();
        }

        return $response->json();
    }
}
