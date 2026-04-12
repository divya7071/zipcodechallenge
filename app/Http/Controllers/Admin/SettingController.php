<?php

namespace App\Http\Controllers\Admin;
use App\Models\Setting;
use App\Traits\StravaHelper;
use Illuminate\Http\Request;
use App\Traits\WebhookHelper;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    use StravaHelper, WebhookHelper;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $settings=Setting::where('type',$request->platform)->where('status',1)->get();
        $isAuthorized = Setting::where('code', 'strava_access_token')->value('value');
        return view('admin.settings.form')->with([
            'settings' => $settings,
            'platform' => $request->platform,
            'isAuthorized' => $isAuthorized,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        foreach ($request->except('_token') as $key => $row) {
            Setting::where('code', $key)->update(['value' => $row]);
        }
        return back()->with(['success_message' => 'Settings Updated Successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function authorizeStrava(Request $request)
    {
        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');

        $url = $this->buildStravaAuthorizationUrl([
            'client_id' => $clientId,
            'client_secret' =>$clientSecret,
            'redirect_uri' => route('admin.handleCallback'),
            'state' => 'xyz',
        ]);

        Setting::updateOrCreate(['code' => 'client_id'], ['value' => $request->input('client_id')]);
        Setting::updateOrCreate(['code' => 'client_secret'], ['value' => $request->input('client_secret')]);

        return response()->json([
            'redirect_url' => $url,
        ]);
    }

    public function handleStravaCallback(Request $request)
    {
        $code = $request->query('code');
        info($request->all());
          if (!$code) {
            return redirect()->route('admin.settings.index', ['platform' => 'strava'])
                            ->with('failure_message', 'Authorization failed or denied.');
        }

        return $this->getStravaAccessToken($request);
    }

    public function createStravaWebhook(Request $request)
    {
        $response = $this->createStravaSubscription();
        if (isset($response['id'])) {
            Setting::updateOrCreate(
                ['code' => 'strava_webhook_id'],
                ['value' => $response['id']]
            );
            return response()->json(['message' => 'Webhook created successfully.']);
        }

        return response()->json(['message' => 'Failed to create webhook.'], 400);
    }

    public function deleteStravaWebhook(Request $request)
    {
        $webhookId = Setting::where('code', 'strava_webhook_id')->value('value');

        if (!$webhookId) {
            return response()->json(['message' => 'No webhook ID found.'], 404);
        }

        $deleted = $this->deleteStravaSubscription($webhookId);

        if ($deleted) {
            Setting::where('code', 'strava_webhook_id')->update(['value' => null]);
            return response()->json(['message' => 'Webhook deleted successfully.']);
        }

        return response()->json(['message' => 'Failed to delete webhook.'], 400);
    }

}
