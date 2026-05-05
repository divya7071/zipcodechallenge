<?php

namespace App\Http\Controllers;

use App\Events\ActivityFetchCompleted;
use App\Models\Athlete;
use App\Models\AthleteAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SyncAthleteStravaActivity;
use App\Models\AthleteBike;
use App\Models\AthleteClub;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Traits\StravaHelper;

class AthleteAuthController extends Controller
{
    use StravaHelper;
     // Redirect to Strava
    // public function login()
    // {
    //     $redirectUri = route('athlete.strava.connect');
    //     $clientId = Setting::where('code', 'client_id')->value('value');
    //     $clientSecret = Setting::where('code', 'client_secret')->value('value');
    //     $query = http_build_query([
    //         'client_id'     => $clientId,
    //         'redirect_uri'  => $redirectUri,
    //         'response_type' => 'code',
    //         'scope'         => 'read,activity:read_all',
    //         'approval_prompt' => 'auto',
    //     ]);
      
    //     return redirect("https://www.strava.com/oauth/authorize?$query");
    // }
    public function login()
    {
        // $account = AthleteAccount::where(
        //     'strava_athlete_id',
        //     117422509
        // )->first();
        
        // Auth::guard('athlete')->login($account->athlete);
        
        // return redirect()->route('account.dashboard');
        $redirectUri = route('athlete.strava.connect');
        $clientId = Setting::where('code', 'client_id')->value('value');
        $clientSecret = Setting::where('code', 'client_secret')->value('value');
        $query = http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => 'read,activity:read_all',
            'approval_prompt' => 'auto',
        ]);
        
        $mobileUrl = "strava://oauth/mobile/authorize?$query";
        $url = "https://www.strava.com/oauth/authorize?$query";

        return response("
            <!DOCTYPE html>
            <html>
            <head>
                <title>Zipcode Challenge</title>
                <meta name='viewport' content='width=device-width, initial-scale=1'>
                <style>
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        height: 100vh;
                        margin: 0;
                        background-color: #f9f9f9;
                        color: #555;
                    }
            
                    .loader {
                        border: 3px solid #f3f3f3;
                        border-top: 3px solid #fc5200;
                        border-radius: 50%;
                        width: 30px;
                        height: 30px;
                        animation: spin 1s linear infinite;
                        margin-bottom: 20px;
                    }
            
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                </style>
            </head>
            <body>
                <div class='loader'></div>
                <p>Redirecting you to Strava</p>
                <script>
                    window.location.href = '{$mobileUrl}';

                    setTimeout(function () {
                        if (!document.hidden) {
                            window.location.href = '{$url}';
                        }
                    }, 2000);
                </script>
            </body>
            </html>
        ");
      
        // return redirect("https://www.strava.com/oauth/authorize?$query");
    }
    // OAuth callback
    public function callback(Request $request)
    {
      
        if (!$request->code) {
            abort(403, 'Authorization failed');
        }
        
        $clientId = Setting::where('code', 'client_id')->value('value');
        $clientSecret = Setting::where('code', 'client_secret')->value('value');
        $authUrl = env('STRAVA_AUTH_URL');
        $response = Http::asForm()->post(
            $authUrl . '/token',
            [
                 'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'code'          => $request->code,
                'grant_type'    => 'authorization_code',
            ]
            );
        $token = $response->json(); 
         $this->logApi('Strava', 'POST', $clientId, 'post',  $authUrl . '/token', json_encode( [
                 'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'code'          => $request->code,
                'grant_type'    => 'authorization_code',
            ]), json_encode($token), 200, '');
        $athlete=$token['athlete']; 
       
        $account = AthleteAccount::where(
            'strava_athlete_id',
            $athlete['id']
        )->first();
    
        if ($account) {
            $athleteModel = Athlete::updateOrCreate(
            ['athlete_id' => $athlete['id']],
            [
            'first_name' => $athlete['firstname'],
            'last_name' => $athlete['lastname'],
            'athlete_id' => $athlete['id'],
            'city' => $athlete['city'],
            'state' => $athlete['state'],
            'country' => $athlete['country'],
            'sex' => $athlete['sex'],
            'profile_medium' => $athlete['profile_medium'] ?? null,
            'profile' => $athlete['profile'] ?? null,
            'premium' => $athlete['premium'] ?? null,
            'follower_count' => $athlete['follower_count'] ?? null,
            'friend_count' => $athlete['friend_count'] ?? null,
            'athlete_type' => $athlete['athlete_type'] ?? null,
            'badge_type_id' => $athlete['badge_type_id'] ?? null,
            'created_at' => Carbon::parse($athlete['created_at']),
        ]);
        Log:info(json_encode($athlete));

       

        AthleteAccount::updateOrCreate(
            ['athlete_id' =>$athleteModel->id],
            [
            'athlete_id' => $athleteModel->id,
            'strava_athlete_id' => $athlete['id'],
            'access_token' => $token['access_token'],
            'refresh_token' => $token['refresh_token'],
            'token_expires_at' => Carbon::createFromTimestamp($token['expires_at']),
        ]);
        
            Auth::guard('athlete')->login($account->athlete);
            $athlete=Athlete::where('athlete_id',$athlete['id'])->first();
           if($athlete->status!=2){
                $athleteModel->update([
                    'strava_sync_started_at' => now(),
                    'is_syncing' => true, 
                ]);
                SyncAthleteStravaActivity::dispatch(auth('athlete')->user());
             }
            return redirect()->route('account.dashboard');
        }
  
       
      $athleteModel = Athlete::updateOrCreate(
            ['athlete_id' => $athlete['id']],
            [
            'first_name' => $athlete['firstname'],
            'last_name' => $athlete['lastname'],
            'athlete_id' => $athlete['id'],
            'city' => $athlete['city'],
            'state' => $athlete['state'],
            'country' => $athlete['country'],
            'sex' => $athlete['sex'],
            'profile_medium' => $athlete['profile_medium'] ?? null,
            'profile' => $athlete['profile'] ?? null,
            'created_at' => Carbon::parse($athlete['created_at']),
        ]);

        AthleteAccount::updateOrCreate(
            ['athlete_id' =>$athleteModel->id],
            [
            'athlete_id' => $athleteModel->id,
            'strava_athlete_id' => $athlete['id'],
            'access_token' => $token['access_token'],
            'refresh_token' => $token['refresh_token'],
            'token_expires_at' => Carbon::createFromTimestamp($token['expires_at']),
        ]);
   
         Auth::guard('athlete')->login($athleteModel);
        $athlete=Athlete::where('athlete_id',$athlete['id'])->first();
        if($athlete->status!=2){
          $athleteModel->update([
                'strava_sync_started_at' => now(),
                'is_syncing' => true, 
            ]);
            SyncAthleteStravaActivity::dispatch(auth('athlete')->user());
        }
        return redirect()->route('account.dashboard');
    }
    // private function storeStravaClubs($athlete, string $accessToken): void
    // {
    //      $strava_url = env('STRAVA_URL');
    //     $response = Http::withToken($accessToken)
    //         ->get('{$strava_url}/athlete/clubs');
       
    //     if (! $response->successful()) {
    //         logger()->error('Strava clubs fetch failed', [
    //             'status' => $response->status(),
    //             'body'   => $response->body(),
    //         ]);
    //         return;
    //     }

    //     foreach ($response->json() as $club) {
    //         AthleteClub::updateOrCreate(
    //             ['strava_club_id' => $club['id']],
    //             [
    //                 'athlete_id'     => $athlete->id,
    //                 'name'           => $club['name'],
    //                 'sport_type'     => $club['sport_type'] ?? null,
    //                 'url'            => $club['url'] ?? null,
    //                 'city'           => $club['city'] ?? null,
    //                 'state'          => $club['state'] ?? null,
    //                 'country'        => $club['country'] ?? null,
    //                 'profile'        => $club['profile'] ?? null,
    //                 'profile_medium' => $club['profile_medium'] ?? null,
    //                 'is_private'     => $club['private'] ?? false,
    //                 'featured'       => $club['featured'] ?? false,
    //                 'verified'       => $club['verified'] ?? false,
    //                 'member_count'   => $club['member_count'] ?? 0,
    //             ]
    //         );
    //     }
    // }

    // private function storeAthletesBikes($athlete, string $accessToken): void
    // {
    //     $response = Http::withToken($accessToken)
    //         ->get('https://www.strava.com/api/v3/athlete');

    //     if (! $response->successful()) {
    //         logger()->error('Strava athlete fetch failed', [
    //             'status' => $response->status(),
    //             'body'   => $response->body(),
    //         ]);
    //         return;
    //     }
    //     $athlete = $response->json(); 
    //   log::info(json_encode($athlete));
    //     if($athlete['bikes']){
    //     foreach($athlete['bikes'] as $bike){
    //          AthleteBike::updateOrCreate(
    //             ['athlete_id' =>$athlete->id,
    //             'strava_gear_id' => $bike['id']
    //             ],
    //             [
    //             'primary' => $bike['primary'],
    //             'name' => $bike['name'],
    //             'resource_state' => $bike['resource_state'],
    //             'distance' => $bike['distance']
    //         ]);
    //     }
    //   }
    // }


    public function logout()
    {
        Auth::guard('athlete')->logout();
        return redirect('/');
    }
}
