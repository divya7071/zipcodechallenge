<?php

namespace Database\Seeders;

use App\Models\ZipCode;
use App\Models\ZipCodeGeometry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ZipCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        \DB::disableQueryLog();

        $path = public_path("geo/us_zipcodes.json");

        if (!File::exists($path)) {
            Log::error("File not found: " . $path);
            return;
        }

        $data = json_decode(File::get($path), true);

        if (!isset($data["features"])) {
            Log::error("Invalid JSON structure");
            return;
        }

        $insertData = [];

        foreach ($data["features"] as $i => $feature) {

            try {
                if (empty($feature["geometry"])) {
                    continue;
                }

                $geometry = $feature["geometry"];

                $zipCodeValue =
                    $feature["properties"]["zcta5ce20"] ??
                    $feature["properties"]["ZCTA5CE20"] ??
                    null;

                if (!$zipCodeValue) {
                    continue;
                }

                $center = $this->getCentroid($geometry);

                if (empty($center['lat']) || empty($center['lng'])) {
                    continue;
                }
                $zipCode=ZipCode::where('zip_code',$zipCodeValue)->first();
                if (!empty($zipCode) && !empty($zipCode->country)) {

                } else {
                $address = [];

               
                $response = Http::withHeaders([
                    'User-Agent' => 'LaravelApp/1.0 (your@email.com)'
                ])->timeout(10)->get(
                    'https://nominatim.openstreetmap.org/reverse',
                    [
                        'lat' => $center['lat'],
                        'lon' => $center['lng'],
                        'format' => 'json'
                    ]
                );

                if ($response->ok()) {
                    $res = $response->json();

                    // Log::info("API RESPONSE", [
                    //     'zip' => $zipCodeValue,
                    //     'response' => $res
                    // ]);

                    if (isset($res['address']) && is_array($res['address'])) {
                        $address = $res['address'];
                    }
                } else {
                    Log::warning("API failed", [
                        'zip' => $zipCodeValue,
                        'status' => $response->status()
                    ]);
                }

               
                $state =
                    $address['state'] ??
                    $address['region'] ??
                    $address['state_district'] ??
                    $address['county'] ??
                    null;

                $country = $address['country'] ?? null;
                $country_code = $address['country_code'] ?? null;
              
                $zipCode = ZipCode::updateOrCreate(
                    ["zip_code" => $zipCodeValue],
                    [
                        "state" => $state,
                        "country" => $country,
                        "country_code" => $country_code,
                    ]
                );
                }
               $geoJson = json_encode($geometry);
               ZipCodeGeometry::updateOrCreate(
                    [
                      "zip_code_id" => $zipCode->id,
                      "zip_code" => $zipCodeValue
                    ],
                    [
                        "geom" => DB::raw("ST_GeomFromGeoJSON('{$geoJson}')"),
                        "centroid" => DB::raw("POINT({$center['lng']}, {$center['lat']})"),
                    ]
                );
              
                              
               sleep(1);

            } catch (\Throwable $e) {
                Log::error("ZIP INSERT FAILED", [
                    "zip" => $zipCodeValue ?? null,
                    "error" => $e->getMessage(),
                ]);
            }
        }

        // Insert remaining
       
        Log::info("Import completed");
    }
    // public function run()
    // {
    //     ini_set("memory_limit", "-1");
    //     set_time_limit(0);
    //     \DB::disableQueryLog();
    //     //$path = storage_path('app/us_zipcodes.json');
    //     $path = public_path("geo/us_zipcodes.json");

    //     if (File::exists($path)) {
    //         $json = File::get($path);
    //         $data = json_decode($json, true);
    //         Log::info("File loaded successfully.");
    //     } else {
    //         Log::error("File still not found at: " . $path);
    //     }

    //     $data = json_decode($json, true);
    //     foreach ($data["features"] as $i => $feature) {
    //         if (!isset($feature["geometry"])) {
    //             Log::warning("NO GEOMETRY", ["index" => $i]);
    //             continue;
    //         }
    //         $props = array_change_key_case($feature["properties"], CASE_LOWER);
    //         $zip =
    //             $feature["properties"]["ZCTA5CE10"] ??
    //             ($feature["properties"]["ZCTA5CE20"] ?? "00000");

    //         if (!$zip) {
    //             Log::warning("ZIP MISSING", ["index" => $i]);
    //             continue;
    //         }

    //         $geometry = json_encode($feature["geometry"], JSON_THROW_ON_ERROR);
        
    //        try {
    //             $geometry = $feature["geometry"];
    //             $center = $this->getCentroid($geometry);
    //             $zipCode = $feature["properties"]["ZCTA5CE10"] ?? 
    //                     $feature["properties"]["ZCTA5CE20"] ?? 
    //                     "00000";
           
    //         if (!empty($center['lat']) && !empty($center['lng'])) {

    //                 $response = Http::get('https://nominatim.openstreetmap.org/reverse', [
    //                     'lat' => $center['lat'],
    //                     'lon' => $center['lng'],
    //                     'format' => 'json'
    //                 ]);

    //                 if (!$response->ok()) {
    //                     Log::warning("API failed", [
    //                         'zip' => $zipCode,
    //                         'status' => $response->status()
    //                     ]);
    //                     continue;
    //                 }

    //                 $res = $response->json();

    //                 Log::info('response:' . json_encode($res));

    //                 if (
    //                     !$res ||
    //                     !is_array($res) ||
    //                     !isset($res['address']) ||
    //                     !is_array($res['address'])
    //                 ) {
    //                     Log::warning("Invalid API response", [
    //                         'zip' => $zipCode,
    //                         'response' => $res
    //                     ]);
    //                     continue;
    //                 }

    //                 $address = $res['address'];
    //             }

    //             $state=$address['state'] ?? null;
    //             $country=$address['country'] ?? null;
    //             $country_code=$address['country_code'] ?? null;
    //              $geoJson = json_encode($geometry);
    //             $zipCode=ZipCode::updateOrCreate(
    //                         ["zip_code" => $zipCode],
    //                         ["state" => $address['state'] ?? null],
    //                         ["country" => $address['country'] ?? null],
    //                         ["country_code" => $address['country_code'] ?? null],
    //                     );
      
    //             $insertData[] = [
    //             'zip_code_id' => $zipCode->id,
    //             'geom' =>  $geoJson,,
    //             'centroid' => DB::raw("POINT({$center['lng']}, {$center['lat']})"),
    //         ];
      
    //      Log::info(json_encode($insertData));
    //     DB::table('zip_code_geometries')->insert($insertData);
                
    //         } catch (\Throwable $e) {
    //             Log::error("ZIP INSERT FAILED", [
    //                 "zip" => $zipCode,
    //                 "error" => $e->getMessage(),
    //             ]);
    //         }
    //     }
    // }
    function getCentroid($geometry)
    {
        $lat = 0;
        $lng = 0;
        $count = 0;

      $coords = $geometry['coordinates']??[]; 
     if(!empty($coords)){
        // Handle MultiPolygon
        if ($geometry['type'] === 'MultiPolygon') {
            $coordinates = $coords[0][0];
        }
        // Handle Polygon
        elseif ($geometry['type'] === 'Polygon') {
            $coordinates = $coords[0];
        }
      //  Log::info(json_encode($coordinates));
        foreach ($coordinates as $point) {
          //  Log::info(json_encode($point)); 
            if (!is_array($point) || count($point) < 2) {
                continue;
            }

            $lng += $point[0];
            $lat += $point[1];
            $count++;
        }
        }
        if ($count === 0) {
            return ['lat' => 0, 'lng' => 0];
        }

        return [
            'lat' => $lat / $count,
            'lng' => $lng / $count
        ];
    }
    function getCentroid1($geometry) {

        $coords = $geometry['coordinates'][0]; 
        $lat = 0;
        $lng = 0;
        $count = count($coords);

         foreach ($coords as $point) {
            if (!is_array($point) || count($point) < 2) {
                continue;
            }

            $lng += $point[0];
            $lat += $point[1];
            $count++;
        }

        if ($count === 0) {
            return ['lat' => 0, 'lng' => 0];
        }

        return [
            'lat' => $lat / $count,
            'lng' => $lng / $count
        ];
    } 
    private function insertGeometryBatch($batch, $existingZips)
    {
        $insertData = [];

        foreach ($batch as $row) {
            if (!isset($existingZips[$row['zip_code']])) {
                continue;
            }

         //   $zipId = $existingZips[$row['zip_code']];
            $zipId = $existingZips[$row['zip_code']]->id;
            $insertData[] = [
                'zip_code_id' => $zipId,
                'geom' => DB::raw("ST_GeomFromGeoJSON('{$row['geom']}')"),
                'centroid' => DB::raw("POINT({$row['lng']}, {$row['lat']})"),
            ];
        }
        // Log::info(json_encode($insertData));
        DB::table('zip_code_geometries')->insert($insertData);
       
    }
}
