<?php

namespace App\Http\Controllers;

use App\Events\ActivityFetchCompleted;
use App\Events\ZipSyncProgressEvent;
use App\Jobs\SyncAthleteStravaActivity;
use App\Models\Athlete;
use App\Models\AthleteAccount;
use App\Models\AthleteActivity;
use App\Models\AthleteActivityMap;
use App\Models\AthleteActivityZipStat;
use App\Models\RemoveAccount;
use App\Models\ZipCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ZipIntersectionTrait;
use App\Traits\StravaHelper;
class AthleteController extends Controller
{
    use ZipIntersectionTrait,StravaHelper;
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
            //    event(new ActivityFetchCompleted('completed', auth('athlete')->id())); 
        // $activities=AthleteActivity::where('athlete_id',auth('athlete')->user()->id)->get();
        $activities=AthleteActivity::with(['media','passedZips'])->where('athlete_id', auth('athlete')->user()->id)->orderBy('date', 'desc')->take(10)->get();
       // $startDate = Carbon::now()->subWeeks(1);
        $startDate = Carbon::now()->startOfWeek();
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
            $result = AthleteActivityZipStat::whereHas('activity')->where('athlete_id',auth('athlete')->user()->id)
                ->selectRaw("
                    COUNT(DISTINCT zip_code) as total_zips,
                    COUNT(DISTINCT CASE WHEN date >= ? THEN zip_code END) as total_zips_from_start
                ", [$startDate])
                ->first();

            $totalZips = $result->total_zips;
            $totalZipsWeeks = $result->total_zips_from_start;
            $weeks = 4;
           $favouriteZip = AthleteActivityZipStat::whereHas('activity')->select(
                'zip_code',
                DB::raw('SUM(distance_mi) as total_distance'),
                DB::raw('COUNT(*) as total_visits')
            )
            ->where('athlete_id', auth('athlete')->user()->id)
            ->groupBy('zip_code')
            ->orderByDesc('total_distance')
            ->first();
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
                    'favouriteZip'=>$favouriteZip,
                  
                ];
        
        $zipcodeRanks = AthleteActivityZipStat::whereHas('activity')->select(
                'zip_code',
                DB::raw('COUNT(*) as total_attempts'),
                DB::raw('SUM(distance_mi) as total_distance'),
                DB::raw('MAX(elevation_gain_ft) as highest_elevation'),
                DB::raw('MAX(max_speed_mph) as highest_speed'),
                DB::raw('MAX(date) as last_activity_date')
            )
            ->where('athlete_id', auth('athlete')->user()->id)
            ->groupBy('zip_code')
            ->orderByDesc('total_attempts') 
            ->paginate(5);
    $start = Carbon::now()->startOfWeek(); // Mon
    $end = Carbon::now()->endOfWeek();     // Sun
    // $start = Carbon::now()->subMonth()->startOfMonth()->startOfWeek();
    // $end   = $start->copy()->endOfWeek();
    $query = AthleteActivityZipStat::whereHas('activity')->select(
        DB::raw('DATE(date) as day'),
        DB::raw('COUNT(DISTINCT zip_code) as total_zips')
    )
    ->whereBetween('date', [$start, $end]);

    // apply only if logged in
    if (auth('athlete')->check()) {
        $query->where('athlete_id', auth('athlete')->id());
    }

    $data = $query
        ->groupBy('day')
        ->pluck('total_zips', 'day');

    // prepare full week (Mon–Sun)
    $week = [];
    $labels = [];
    $counts = [];

    for ($i = 0; $i < 7; $i++) {
        $date = $start->copy()->addDays($i);
        $dayName = $date->format('D');

        $labels[] = $dayName;
        $counts[] = $data[$date->toDateString()] ?? 0;
    }

    // best day
    $max = max($counts);
    $bestIndex = array_search($max, $counts);
    $bestDay = $labels[$bestIndex];
    $weeklyData=['labels'=>$labels,
        'counts'=>$counts,
        'start'=>$start,
        'end'=>$end,
        'bestDay'=>$bestDay,
        'max'=>$max];
     $passedZips = AthleteActivityZipStat::wherehas('activity')->where('athlete_id', auth('athlete')->id())
    ->selectRaw('zip_code, COUNT(*) as total')
    ->groupBy('zip_code')
    ->pluck('total', 'zip_code')
    ->toArray();
               //  return view('overview')->with(['activities'=>$activities,'totalZips'=>$totalZips,'summary'=>$summary,'longestRide'=>$longestRide]);
        return view('account.index')->with(['activities'=>$activities,'totalZips'=>$totalZips,'summary'=>$summary,'zipcodeRanks'=>$zipcodeRanks,'sportTypes' => AthleteActivity::distinct()->pluck('sport_type'),'weeklyData'=>$weeklyData,'isSyncing' => auth('athlete')->user()->is_syncing,'passedZips'=>$passedZips]);
    
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
        $zipStats = AthleteActivityZipStat::whereHas('activity')->select(
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
            $totalZips = AthleteActivityZipStat::whereHas('activity')->where('athlete_id', $athlete->id)
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
 public function removeAccount(){
          $activities=AthleteActivity::with(['media','passedZips'])->where('athlete_id', auth('athlete')->user()->id)->orderBy('date', 'desc')->take(10)->get();
       // $startDate = Carbon::now()->subWeeks(1);
       
        $summary = AthleteActivity::where('athlete_id', auth('athlete')->user()->id)
                ->selectRaw('
                COUNT(*) as total_activities,
                SUM(distance) as total_distance,
                SUM(moving_time) as total_moving_time,
                SUM(elevation) as total_elevation
            ')
            ->first();
            $totalActivities = $summary->total_activities ?? 0;
            $totalDistanceMiles = ($summary->total_distance ?? 0) / 1609.34;
            $result = AthleteActivityZipStat::wherehas('activity')->where('athlete_id',auth('athlete')->user()->id)
                ->selectRaw("
                    COUNT(DISTINCT zip_code) as total_zips")
                ->first();

        $totalPassedZips = $result->total_zips;
        $totalZipcode=ZipCode::distinct('zip_code')->count();
        $completdPercentage=0;
        if($totalPassedZips>0 && $totalZipcode>0)
        $completdPercentage=($totalPassedZips/$totalZipcode)*100;
   
        $summary=[
            'totalActivities'=>$totalActivities,
            'totalDistanceMiles'=>$totalDistanceMiles,
            'totalPassedZips'=>$totalPassedZips,
            'completdPercentage'=>$completdPercentage
        ];

  
        return view('account.remove-account')->with(['summary'=>$summary]); 
    }
  public function deleteAccount(Request $request){
        
        $request->validate([
            'reason' => 'required',
            'other_reason' => 'required_if:reason,other',
            'comments' => 'required',
            'feedback' => 'nullable',
            'delete_confirm' => ['required', 'in:DELETE'],
        ]);
        $athlete=Athlete::whereId(auth('athlete')->id())->first();
        RemoveAccount::create([
            'athlete_id'   => auth('athlete')->id(),
            'athlete_strava_id' => $athlete->athlete_id,
            'first_name'        => $athlete->first_name,
            'last_name'         => $athlete->last_name,
            'email'             => $athlete->email,
            'reason'            => $request->reason,
            'other_reason'      => $request->other_reason,
            'comments'          => $request->comments,
            'feedback'          => $request->feedback,
        ]);
        $athlete=Athlete::where('id', auth('athlete')->user()->id)->first();
        $this->disconnectStrava($athlete);
        $this->archiveAthleteData($athlete->id);
        return response()->json(['result'=>'success']);

    }
    public function pauseAccount(Request $request){
           $request->validate([
            'pause_confirm' => 'required',
            ]);

              $athlete=Athlete::where('id', auth('athlete')->user()->id)->first();
              $athlete->update(['status'=> 2]);
         return response()->json(['result'=>'success']);   
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
