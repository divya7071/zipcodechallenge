<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Store contact form data
     */
    public function store(Request $request)
    {
      
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

      
        Contact::create($validated);

        return view('success');
    }
    public function locate(){
            $lat = 40.741895;
            $lng = 73.989308;
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
                Log::info('Activity location: '.$response->json());
            $location = $response->json()["display_name"] ?? null;
        
    }

}
