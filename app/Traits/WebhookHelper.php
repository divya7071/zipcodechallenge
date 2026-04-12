<?php

namespace App\Traits;

use App\Models\Setting;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

trait WebhookHelper
{
    public function storeWebhookPayload(string $provider, Request $request): Webhook
    {
        Log::info("Webhook Received from $provider", $request->all());

        return Webhook::create([
            'provider' => $provider,
            'payload' => json_encode($request->all()),
        ]);
    }

    public function verifyStravaChallenge(Request $request)
    {
        if ($request->has('hub_mode') && $request->hub_mode === 'subscribe') {
            return response()->json(['hub.challenge' => $request->hub_challenge]);
        }

        return response('Invalid challenge', 400);
    }

    public function createStravaSubscription(): array
    {
        $clientId = Setting::where('code', 'client_id')->value('value');
        $clientSecret = Setting::where('code', 'client_secret')->value('value');

        $response = Http::post('https://www.strava.com/api/v3/push_subscriptions', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'callback_url' => url('/webhook/strava'),
            'verify_token' => 'strava_webhook',
        ]);

        if (!$response->successful()) {
            Log::error('Strava webhook creation failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
    

        return $response->json();
    }

    public function deleteStravaSubscription(): array
    {
        $subs = Http::get('https://www.strava.com/api/v3/push_subscriptions', [
            'client_id' => config('services.strava.client_id'),
            'client_secret' => config('services.strava.client_secret'),
        ])->json();

        if (empty($subs)) return ['message' => 'No subscription found'];

        $id = $subs[0]['id'];
        $response = Http::delete("https://www.strava.com/api/v3/push_subscriptions/{$id}", [
            'client_id' => config('services.strava.client_id'),
            'client_secret' => config('services.strava.client_secret'),
        ]);

        return $response->json();
    }
}
