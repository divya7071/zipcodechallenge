<?php

namespace App\Http\Controllers;

use App\Jobs\SyncAthleteStravaActivity;
use App\Models\Athlete;
use App\Models\AthleteActivity;
use App\Models\AthleteActivityZipStat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ZipIntersectionTrait;

class AthleteController extends Controller
{
    use ZipIntersectionTrait;
     public function index()
    {
        if (!auth('athlete')->check()) {
            return redirect()->route('athlete.strava.login');
        }
        $athlete = auth('athlete')->user();
        if (!$athlete->account) {
            return redirect()->route('athlete.strava.connect');
        }
       
       return redirect()->route('strava.index');
    }
    public function overview(){
        // $activities=AthleteActivity::where('athlete_id',auth('athlete')->user()->id)->get();
        $activities=AthleteActivity::with(['media','passedZips'])->where('athlete_id', auth('athlete')->user()->id)->orderBy('date', 'desc')->take(10)->get();
        $startDate = Carbon::now()->subWeeks(4);
        $summary = AthleteActivity::where('athlete_id', auth('athlete')->user()->id)
            ->where('date', '>=', $startDate)
            ->selectRaw('
                COUNT(*) as total_activities,
                SUM(distance) as total_distance,
                SUM(moving_time) as total_moving_time,
                SUM(elevation) as total_elevation
            ')
            ->first();
            $totalActivities = $summary->total_activities ?? 0;

            $totalDistanceMiles = ($summary->total_distance ?? 0) / 1609.34;

            $totalMovingTime =$this->formatDuration($summary->total_moving_time);

            $totalElevationFt = $summary->total_elevation ?? 0;
            $result = AthleteActivityZipStat::where('athlete_id',auth('athlete')->user()->id)
                ->selectRaw("
                    COUNT(DISTINCT zip_code) as total_zips,
                    COUNT(DISTINCT CASE WHEN date >= ? THEN zip_code END) as total_zips_from_start
                ", [$startDate])
                ->first();

            $totalZips = $result->total_zips;
            $totalZipsWeeks = $result->total_zips_from_start;
            $weeks = 4;

        $avgActivitiesPerWeek = round($totalActivities / $weeks, 1);

        $avgDistancePerWeek = round($totalDistanceMiles / $weeks, 1);

        $avgElevationPerWeek = round($totalElevationFt / $weeks);

        $avgMovingSeconds = ($summary->total_moving_time ?? 0) / $weeks;

        $avgTimePerWeek = $this->formatDuration($avgMovingSeconds);
        $longestRide = AthleteActivity::where('athlete_id', auth('athlete')->user()->id)
        ->orderByDesc('distance')
        ->first();
        $longestRideMiles=0;
        if($longestRide)
        $longestRideMiles= round($longestRide->distance / 1609.34, 1);
        $summary=[
                    'totalActivities'=>$totalActivities,
                    'totalDistanceMiles'=>$totalDistanceMiles,
                    'totalMovingTime'=>$totalMovingTime,
                    'totalElevationFt'=>$totalElevationFt,
                    'avgActivitiesPerWeek'=>$avgActivitiesPerWeek,
                    'avgDistancePerWeek'=>$avgDistancePerWeek,
                    'avgElevationPerWeek'=>$avgElevationPerWeek,
                    'avgTimePerWeek'=>$avgTimePerWeek,
                    'totalZips'=>$totalZipsWeeks,
                    'longestRideMiles'=>$longestRideMiles,
                    'longestRide'=>$longestRide,

                ];
        return view('overview')->with(['activities'=>$activities,'totalZips'=>$totalZips,'summary'=>$summary,'longestRide'=>$longestRide]);
    
    }
    public function detail($athlete_id){

        $athlete = Athlete::where('athlete_id', $athlete_id)
            ->with(['medias' => function ($q) {
                $q->whereNotNull('media')
                ->where('media', '!=', '')
                ->where('media', '!=', '[]')
                ->where('media', 'NOT LIKE', '%placeholder-photo%')
                ->limit(1);
            }])
            ->first();
        $coverImages = [];

        $medias = $athlete->medias()
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($medias as $mediaRow) {

            if (!$mediaRow->media) continue;

            $allMedia = json_decode($mediaRow->media);

            foreach ($allMedia as $media) {

                if (
                    $media->type == 1 &&
                    !empty($media->url) &&
                    !str_contains($media->url, 'placeholder-photo')
                ) {
                    $coverImages[] = $media->url;
                }
               if (count($coverImages) == 5) {
                    break 2;
                }
            }
        }
      
        $activities = AthleteActivity::with(['media', 'passedZips'])->where('athlete_id', $athlete->id)->where('date', '>=', Carbon::now()->subWeeks(4))
        ->orderBy('date', 'desc')->get();
        $distanceCalendar = $activities
        ->groupBy(function($item){
            return $item->date->format('Y-m-d');
        })
        ->map(function($day){
            
            return number_format($day->sum('distance')/ 1609.344, 2); 
        });
        $zipStats = AthleteActivityZipStat::select(
            'zip_code',
            'athlete_id',
            DB::raw('COUNT(*) as total_attempts'),
            DB::raw('SUM(distance_mi) as total_distance'),
            DB::raw('MAX(date) as last_activity_date')
        )
        ->groupBy('zip_code', 'athlete_id')
        ->get()
        ->groupBy('zip_code');

        $localLegends = collect();

        foreach ($zipStats as $zip => $athletes) {

            $top = $athletes->sortByDesc('total_attempts')->first();

            if ($top->athlete_id == auth('athlete')->user()->id) {
                $localLegends->push($top);
            }
        }

        $startDate = Carbon::now()->subWeeks(4);

        $summary = AthleteActivity::where('athlete_id', $athlete->id)
            ->where('date', '>=', $startDate)
            ->selectRaw('
                COUNT(*) as total_activities,
                SUM(distance) as total_distance,
                SUM(moving_time) as total_moving_time,
                SUM(elevation) as total_elevation
            ')
            ->first();
            $totalActivities = $summary->total_activities ?? 0;

            $totalDistanceMiles = ($summary->total_distance ?? 0) / 1609.34;

            $totalMovingTime =$this->formatDuration($summary->total_moving_time);

            $totalElevationFt = $summary->total_elevation ?? 0;
            $totalZips = AthleteActivityZipStat::where('athlete_id', $athlete->id)
            ->where('date', '>=', $startDate)
            ->distinct('zip_code')
            ->count('zip_code');
            $weeks = 4;

        $avgActivitiesPerWeek = round($totalActivities / $weeks, 1);

        $avgDistancePerWeek = round($totalDistanceMiles / $weeks, 1);

        $avgElevationPerWeek = round($totalElevationFt / $weeks);

        $avgMovingSeconds = ($summary->total_moving_time ?? 0) / $weeks;

        $avgTimePerWeek = $this->formatDuration($avgMovingSeconds);
       
        $longestRide = AthleteActivity::where('athlete_id', $athlete->id)
        ->orderByDesc('distance')
        ->first();
        $longestRideMiles= round($longestRide->distance / 1609.34, 1);
        $summary=[
                    'totalActivities'=>$totalActivities,
                    'totalDistanceMiles'=>$totalDistanceMiles,
                    'totalMovingTime'=>$totalMovingTime,
                    'totalElevationFt'=>$totalElevationFt,
                    'avgActivitiesPerWeek'=>$avgActivitiesPerWeek,
                    'avgDistancePerWeek'=>$avgDistancePerWeek,
                    'avgElevationPerWeek'=>$avgElevationPerWeek,
                    'avgTimePerWeek'=>$avgTimePerWeek,
                    'totalZips'=>$totalZips,
                    'longestRideMiles'=>$longestRideMiles,
                    'longestRide'=>$longestRide,

                ];
        return view('athlete.detail')->with(['athlete'=>$athlete,'activities'=>$activities,'distanceCalendar'=>$distanceCalendar,'coverImages'=>$coverImages,
        'localLegends'=>$localLegends,'summary'=>$summary]); 
        
    }
    public function importZips()
    {
        $filePath = storage_path('app/zip_codes.csv'); 

        DB::statement("
            LOAD DATA LOCAL INFILE '$filePath'
            INTO TABLE zip_codes
            FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
            LINES TERMINATED BY '\\n'
            IGNORE 1 ROWS
            (zip_code, @wkt_geom) -- Map CSV columns to table/variables
            SET boundary = ST_GeomFromText(@wkt_geom, 4326) -- Convert WKT to Geometry
        ");
    }
    function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return $hours . 'h ' . str_pad($minutes, 2, '0', STR_PAD_LEFT) . 'm';
    }
    protected function extractNearbyFeatures(string $polyline, int $bufferKm = 30): array
    {
        $route = $this->decodePolyline($polyline);

        if (count($route) < 2) return [];

        $padding = $bufferKm / 111;

        $routeBox = $this->bbox($route);

        $routeBox = [
            'minLng' => $routeBox['minLng'] - $padding,
            'maxLng' => $routeBox['maxLng'] + $padding,
            'minLat' => $routeBox['minLat'] - $padding,
            'maxLat' => $routeBox['maxLat'] + $padding,
        ];

        $features = [];

        foreach ($this->zipFeatureStream() as $feature) {

            if (!isset($feature['geometry']['coordinates'][0])) continue;

            $outerRing = $feature['geometry']['coordinates'][0];
            $polyBox = $this->bbox($outerRing);

            if ($this->bboxIntersects($routeBox, $polyBox)) {
                $features[] = $feature;
            }
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features
        ];
    }
}
