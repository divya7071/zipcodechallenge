<?php

namespace App\Jobs;

use App\Models\AthleteActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class FetchActivityLocation implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $activityId;
    protected $latlng;

    public function __construct($activityId, $latlng)
    {
        $this->activityId = $activityId;
        $this->latlng = $latlng;
    }

    public function handle()
    {
        if (empty($this->latlng) || count($this->latlng) < 2) {
            return;
        }

        $lat = $this->latlng[0];
        $lng = $this->latlng[1];

        $response = Http::withHeaders([
            "User-Agent" => "MyStravaClone/1.0",
        ])
        ->timeout(10)
        ->get("https://nominatim.openstreetmap.org/reverse", [
            "lat" => $lat,
            "lon" => $lng,
            "format" => "jsonv2",
        ]);

        if ($response->successful()) {
            $location = $response->json()["display_name"] ?? null;

            AthleteActivity::where('id', $this->activityId)
                ->update(['start_location' => $location]);
        }
    }
}