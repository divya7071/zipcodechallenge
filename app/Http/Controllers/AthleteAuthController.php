<?php

namespace App\Http\Controllers;

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
    public function login()
    {
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
      
        return redirect("https://www.strava.com/oauth/authorize?$query");
    }

    // OAuth callback
    public function callback(Request $request)
    {
      
        if (!$request->code) {
            abort(403, 'Authorization failed');
        }
        
        $clientId = Setting::where('code', 'client_id')->value('value');
        $clientSecret = Setting::where('code', 'client_secret')->value('value');
 
        $response = Http::asForm()->post(
            'https://www.strava.com/api/v3/oauth/token',
            [
                 'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'code'          => $request->code,
                'grant_type'    => 'authorization_code',
            ]
            );
        $token = $response->json(); 
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
          
          //  $this->storeStravaClubs($athleteModel, $token['access_token']);
           // $this->storeAthletesBikes($athleteModel, $token['access_token']);
              Auth::guard('athlete')->login($account->athlete);
            SyncAthleteStravaActivity::dispatch(auth('athlete')->user());
           // $this->getAthleteStravaActivities($account->athlete);
            return redirect()->route('overview');
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
   
        
        // $this->storeStravaClubs($athleteModel, $token['access_token']);
       //  $this->storeAthletesBikes($athleteModel, $token['access_token']);
         Auth::guard('athlete')->login($athleteModel);
        SyncAthleteStravaActivity::dispatch(auth('athlete')->user());
        // $this->getAthleteStravaActivities($athleteModel);
        //Artisan::call('strava:sync-activities', ['athlete_id' => $athleteModel->id]);
        //SyncAthleteStravaActivity::dispatch($athleteModel);
         return redirect()->route('overview');
    }
    private function storeStravaClubs($athlete, string $accessToken): void
    {
        $response = Http::withToken($accessToken)
            ->get('https://www.strava.com/api/v3/athlete/clubs');

        if (! $response->successful()) {
            logger()->error('Strava clubs fetch failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return;
        }

        foreach ($response->json() as $club) {
            AthleteClub::updateOrCreate(
                ['strava_club_id' => $club['id']],
                [
                    'athlete_id'     => $athlete->id,
                    'name'           => $club['name'],
                    'sport_type'     => $club['sport_type'] ?? null,
                    'url'            => $club['url'] ?? null,
                    'city'           => $club['city'] ?? null,
                    'state'          => $club['state'] ?? null,
                    'country'        => $club['country'] ?? null,
                    'profile'        => $club['profile'] ?? null,
                    'profile_medium' => $club['profile_medium'] ?? null,
                    'is_private'     => $club['private'] ?? false,
                    'featured'       => $club['featured'] ?? false,
                    'verified'       => $club['verified'] ?? false,
                    'member_count'   => $club['member_count'] ?? 0,
                ]
            );
        }
    }

    private function storeAthletesBikes($athlete, string $accessToken): void
    {
        $response = Http::withToken($accessToken)
            ->get('https://www.strava.com/api/v3/athlete');

        if (! $response->successful()) {
            logger()->error('Strava athlete fetch failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return;
        }
        $athlete = $response->json(); 
       log::info(json_encode($athlete));
        if($athlete['bikes']){
        foreach($athlete['bikes'] as $bike){
             AthleteBike::updateOrCreate(
                ['athlete_id' =>$athlete->id,
                'strava_gear_id' => $bike['id']
                ],
                [
                'primary' => $bike['primary'],
                'name' => $bike['name'],
                'resource_state' => $bike['resource_state'],
                'distance' => $bike['distance']
            ]);
        }
       }
    }


    public function logout()
    {
        Auth::guard('athlete')->logout();
        return redirect('/');
    }
}
