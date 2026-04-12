<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Traits\WebhookHelper;
use App\Http\Controllers\Controller;
use App\Jobs\SyncStravaActivityJob;

class StravaWebhookController extends Controller
{
    use WebhookHelper;

    public function receive(Request $request)
    {
        if ($request->isMethod('get')) {
            return $this->verifyStravaChallenge($request);
        }

        $webhook = $this->storeWebhookPayload('strava', $request);

        dispatch(new SyncStravaActivityJob($request->input('object_id')));

        return response('Received', 200);
    }

}
