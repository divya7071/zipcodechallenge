<?php

namespace App\Jobs;

use App\Models\AthleteActivity;
use App\Models\AthleteActivityMedia;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class FetchActivityPhotos implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $activityId;
    protected $stravaActivityId;
    protected $accessToken;
    protected $refreshToken;
    protected $athleteId;

    public function __construct($activityId, $stravaActivityId, $accessToken, $refreshToken, $athleteId)
    {
        $this->activityId = $activityId;
        $this->stravaActivityId = $stravaActivityId;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->athleteId = $athleteId;
    }

    public function handle()
    {
        $strava_url = env('STRAVA_URL');

        $response = Http::withoutVerifying()
            ->withToken($this->accessToken)
            ->get("{$strava_url}/activities/{$this->stravaActivityId}/photos", [
                "size" => 600
            ]);

        // Handle token expired
        if ($response->unauthorized()) {
            // You may call a helper here to refresh token
            return;
        }

        $photoUrls = [];

        foreach ($response->json() ?? [] as $key => $photo) {
            if (isset($photo["urls"])) {
                $photoUrls[$key] = [
                    "url" => $photo["urls"]["600"] ?? array_values($photo["urls"])[0],
                    "type" => $photo["type"],
                    "video_url" => $photo["video_url"] ?? null,
                ];
            }
        }

        AthleteActivityMedia::updateOrCreate(
            [
                "athlete_activity_id" => $this->activityId,
                "athlete_id" => $this->athleteId,
            ],
            [
                "media" => json_encode($photoUrls),
            ]
        );
    }
}
