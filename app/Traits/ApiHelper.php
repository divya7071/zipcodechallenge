<?php

namespace App\Traits;

use App\Models\ApiLog;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

trait ApiHelper
{

    public function logApi($platform, $type, $reference, $method, $url, $payload, $response, $code, $message)
    {   
        $apiLog = new ApiLog();
        $apiLog->platform = $platform;
        $apiLog->type = $type;
        $apiLog->reference_id = $reference;
        $apiLog->method = $method;
        $apiLog->url = $url;
        $apiLog->payload = $payload;
        $apiLog->response = $response;
        $apiLog->code = $code;
        $apiLog->message = $message;
        $apiLog->save();
        return true;
    }
}



