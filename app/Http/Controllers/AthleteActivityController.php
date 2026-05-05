<?php

namespace App\Http\Controllers;

use App\Events\ActivityFetchCompleted;
use App\Jobs\SyncAthleteStravaActivity;
use App\Models\ActivityZipStat;
use App\Models\Athlete;
use App\Models\AthleteActivity;
use App\Models\AthleteActivityZipStat;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Traits\StravaHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Traits\ZipIntersectionTrait;
use Mpdf\Tag\A;

class AthleteActivityController extends Controller
{
   
    use StravaHelper,ZipIntersectionTrait;
    public function index(Request $request, Datatables $datatables)
    {   
       //event(new ActivityFetchCompleted('fetching', auth('athlete')->id()));
        if($request->ajax()){
           
                $query = AthleteActivity::where('athlete_id', auth('athlete')->user()->id)
                    ->select([
                        'id',
                        'name',
                        'sport_type',
                        'date',
                        'moving_time',
                        'distance',
                        'elevation',
                        'passed_zips',
                        'relative_effort'
                    ]);

             if ($request->sport_type) {
                    $query->where('sport_type', $request->sport_type);
                }

                if ($request->start_date && $request->end_date) {
                    $query->whereBetween('date', [$request->start_date, $request->end_date]);
                }
                if ($request->name) {
                    $query->where('name', 'like', '%' . $request->name . '%');
                }
                $query->orderByDesc('date');

                return DataTables::of($query)
                ->editColumn('sport_type', function ($row) {
                    $class = $row->sport_type === 'Ride' ? 'bg-danger' :
                            ($row->sport_type === 'Run' ? 'bg-success' : 'bg-info');

                    return '<span class="badge '.$class.'">'.$row->sport_type.'</span>';
                })
                ->editColumn('date',function ($row) {
                   return $row->date? \Carbon\Carbon::parse($row->date)->format('D, m/d/Y H:i:s'): '-';
                })
                ->editColumn('moving_time',function ($row) {
                    return gmdate("H:i:s",  $row->moving_time);
                })
                ->editColumn('distance',function ($row) {
                    return  number_format($row->distance / 1609.344, 2).' mi'; 
                })
                ->editColumn('elevation',function ($row) {
                    return  ($row->elevation > 0 ? number_format($row->elevation) : '-');
                })
             ->addColumn('zips', function ($row) {
                    

                    $zips = json_decode($row->passed_zips, true);

                       if (!is_array($zips)) {
                             return '<span class="text-warning">Finding passed zips...</span>';
                        }
                        if (is_array($zips)&&empty($zips)) {
                            return '-';
                        }
                        if (empty($row->passed_zips)) return '—';

                    return collect($zips)->map(fn ($z) =>
                        '<span class="badge bg-light text-dark me-1">'.$z.'</span>'
                    )->implode('');
                })
                  ->addColumn('action', function ($row) {
                        return '
                            <div class="d-flex align-items-center gap-1">
                                
                                <a href="'.route('account.activity.show', $row->id).'" class="btn btn-primary btn-sm ">
                                    View
                                </a>
                    
                                <a class="btn btn-dark btn-sm map-btn open-map-drawer" data-id="'.$row->id.'" >
                                    <i class="bi bi-map"></i>
                                </a>
                    
                            </div>
                        ';
                    })                 
                // ->addColumn('action', function ($row) {
                //     return '
                //         <a href="'.route('account.activity.show',$row->id).'" class="btn">
                //             View
                //         </a>
                //         <a class="map-btn btn open-map-drawer" data-id="'.$row->id.'">
                //         <i class="bi bi-map" style="color:#FFFF;"></i>
                          
                //         </a>';
                  
                // })
                ->rawColumns(['sport_type','zips','action'])
                ->make(true);
        } 
        
        return view('account.activity.index', [
            'hasActivities' => AthleteActivity::where('athlete_id', auth('athlete')->id())->exists(),
            'syncedAt' => auth('athlete')->user()->strava_synced_at,
            'shouldSync' => is_null(auth('athlete')->user()->strava_synced_at),
            'isSyncing' => auth('athlete')->user()->is_syncing,
            'activity'=>AthleteActivity::where('athlete_id', auth('athlete')->id())->first(),
            'sportTypes' => AthleteActivity::distinct()->pluck('sport_type')
        ]);
    }
    public function mapView(Request $request){
       
        $activity = AthleteActivity::with('activity_map')->whereId($request->activityId)->first();
        $html =  view('account.activity.map-info', compact('activity'))->render();
        $return_array['status'] = true;
        $return_array['html'] = $html;
        $return_array['activity'] = $activity;
           
        return response()->json($return_array);

    }

    public function show(AthleteActivity $activity)
    {
        $polyline =$activity->activity_map?json_decode($activity->activity_map->map)->summary_polyline:'';
        $upSegments = $activity->passedZips
            ->filter(fn($row) => !is_null($row->sort_order))
            ->sortBy('sort_order')
            ->values()
            ->map(function ($row) {
                return [
                    'zip' => $row->zip_code,
                    'sort_order' => $row->sort_order,
                    'direction' => 'up',
                    'distance_mi' => $row->distance_mi_up,
                    'speed_mph' => $row->speed_mph_up,
                    'elapsed_sec' => $row->elapsed_sec,
                ];
            });
         $downSegments = $activity->passedZips
            ->filter(fn($row) => !is_null($row->sort_order_down))
            ->sortBy('sort_order_down')
            ->values()
            ->map(function ($row) {
                return [
                    'zip' => $row->zip_code,
                    'sort_order_down' => $row->sort_order_down,
                    'direction' => 'down',
                    'distance_mi' => $row->distance_mi_down,
                    'speed_mph' => $row->speed_mph_down,
                    'elapsed_sec' => $row->elapsed_sec,
                ];
            });   
        $geojson = [
            'type' => 'FeatureCollection',
            'features' => []
        ];

        if ($polyline) {
            $geojson = $this->extractNearbyFeatures($polyline, 30);
        }
        return view('account.activity.activity-detail', [
            'activity' => $activity,
            'zips' => $geojson,
            'upSegments' => $upSegments,
            'downSegments' =>$downSegments,
            'startZip' => $upSegments->first()['zip'] ?? null,
            'turnZip' => $upSegments->last()['zip'] ?? null,
            'endZip'   => $downSegments->last()['zip'] ?? $upSegments->first()['zip'] ?? null
        ]);
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
    public function show1(AthleteActivity $activity)
    {
        // $accessToken = $activity->athlete->account->access_token;
        // $refreshToken = $activity->athlete->account->refresh_token;
        // $polyline=$activity->activity_map?json_decode($activity->activity_map->map)->summary_polyline:'';
        // $savedActivity=$activity;
        // $zipPolygons = $this->findZipsFromPolyline($polyline);


        //     $streams = $this->getActivityStreams(
        //         $accessToken,
        //         $activity->activity_id
        //     );

        //     if (!$streams || empty($streams["latlng"]["data"])) {
        //         return;
        //     }

        //     $zipStats = $this->calculateZipEntryStats(
        //         $streams,
        //         $zipPolygons
        //     );
     
        //     foreach ($zipStats as $zip => $stats) {
        //         AthleteActivityZipStat::updateOrCreate(
        //             [
        //                 "athlete_activity_id" => $savedActivity->id,
        //                 "zip_code" => $zip,
        //             ],
        //             [
        //                 "distance_mi" => $stats["distance_mi"],
        //                 "elapsed_sec" => $stats["elapsed_sec"],
        //                 "moving_sec" => $stats["moving_sec"],
        //                 "speed_mph" => $stats["speed_mph"],
        //                 "speed_mph" => $stats["speed_mph"],
        //                 "max_speed_mph" => $stats["max_speed_mph"],
        //                 "athlete_id" => $savedActivity->athlete_id,
        //             ]
        //         );
        //     }


        //     $activity->load('passedZips');
        //     foreach ($activity->passedZips as $effort) {
        //         $topThree = AthleteActivityZipStat::where('athlete_id', $activity->athlete_id)
        //             ->where('zip_code', $effort->zip_code)
        //             ->orderBy('elapsed_sec', 'ASC') 
        //             ->take(3)
        //             ->get();

        //         $rank = $topThree->search(function ($item) use ($effort) {
        //             return $item->id === $effort->id;
        //         });

        //         $effort->rank = $rank !== false ? $rank + 1 : null;
        //     }
    
            return view('account.activity.activity-detail', [
            'activity' => $activity
            ]);
       
    }
    public function syncActivity(){

        $athlete = auth('athlete')->user();
        if ($athlete->is_syncing) {
            return response()->json(['status' => 'already_syncing']);
        }

        return response()->json(['status' => 'sync_started']);
    }
    public function syncStatus(Request $request)
    {
        $athlete = auth('athlete')->user();

        return response()->json([
            'is_syncing' => (bool) $athlete->is_syncing,
            'activity_count' => AthleteActivity::where(
                'athlete_id',
                $athlete->id
            )->count(),

            'last_synced_at' => optional($athlete->strava_synced_at)
                ->toDateTimeString(),
        ]);
    }
    public function savePassedZips(Request $request, AthleteActivity $activity)
    {
    
        $request->validate([
            'passed_zips' => 'required|array',
        ]);

        if (!empty($activity->passed_zips)) {
            return response()->json([
                'status' => 'already_saved',
            ]);
        }

        $activity->passed_zips = array_values(array_unique($request->passed_zips));
        $activity->save();

        return response()->json([
            'status' => 'saved',
            'passed_zips' => $activity->passed_zips,
        ]);
    }
    public function getActivityPolylines(){
        $athletActivities=AthleteActivity::with('activity_map')->where('created_at', '>=', now()->subWeek())->where('athlete_id',auth('athlete')->user()->id)->get();
      
        $polyLines=[];
        $activityListHtml='<ul id="activityList">';
        foreach($athletActivities as $key=>$activity){
            
            if(!empty($activity->activity_map)){
                 $zips = is_array($activity->passed_zips) ? $activity->passed_zips :[];
                 $zips=implode(', ',$zips);
                $polyLines[] = [
                        'name' => $activity->name,
                        'type' => $activity->type,
                        'distance' =>number_format($activity->distance / 1609.344, 2).' mi',
                        'duration' => gmdate("H:i:s",  $activity->moving_time),
                        'date' => $activity->date->format('D, m/d/Y H:i:s'),
                        'passedZips' => $zips,
                        'activityId' => $activity->id,
                        'summary_polyline' =>$activity->activity_map?json_decode($activity->activity_map->map)->summary_polyline:'',
                ];
                $activityListHtml.='<li  class="list-group-item list-group-item-action text-center highlightRoute" data-id="'.$activity->id.'">'.$activity->date->format('D, m/d/Y H:i:s').' '.$activity->name.'</li>';
       
            }
        }
        $activityListHtml.='</ul>';
        return response()->json(['polylines'=>$polyLines,'activityListHtml'=>$activityListHtml]);
    }
    public function loadActivityMore(Request $request)
    {
        $offset = $request->offset ?? 0;

        $activities = AthleteActivity::with(['media','athlete'])
            ->orderBy('date', 'desc')
            ->where('athlete_id', auth('athlete')->user()->id)
            ->skip($offset)
            ->take(10)
            ->get();

        $html = view('account.partials.activity-more', compact('activities'))->render();

        return response()->json([
            'status' => true,
            'count' => $activities->count(),
            'html' => $html
        ]);
    }
  public function segmantDetails($id)
    {
        $zip = AthleteActivityZipStat::findOrFail($id);
        $subQuery = AthleteActivityZipStat::select(
                'athlete_id',
                DB::raw('MAX(distance_mi) as max_distance')
            )
            ->where('zip_code', $zip->zip_code)
            ->groupBy('athlete_id');

        $zip->topRanks = AthleteActivityZipStat::with('athlete')
            ->joinSub($subQuery, 'max_table', function ($join) {
                $join->on('athlete_activity_zip_stats.athlete_id', '=', 'max_table.athlete_id')
                    ->on('athlete_activity_zip_stats.distance_mi', '=', 'max_table.max_distance');
            })
            ->where('athlete_activity_zip_stats.zip_code', $zip->zip_code)
            ->orderByDesc('athlete_activity_zip_stats.distance_mi')  
            ->orderByDesc('athlete_activity_zip_stats.speed_mph')   
            ->take(10)
            ->get();

        $zip->mostPassed = AthleteActivityZipStat::with('athlete')
        ->select(
            'athlete_id',
            DB::raw('COUNT(*) as total_passes')
        )
        ->where('zip_code', $zip->zip_code)
        ->where('created_at', '>=', Carbon::now()->subDays(90))
        ->groupBy('athlete_id')
        ->orderByDesc('total_passes')
        ->first();

        return view('account.activity.segment-details', compact('zip'));
    }
    public function media($id)
    {
        $activity = AthleteActivity::findOrFail($id);
        $media = json_decode($activity->media->media);

        return response()->json($media);
    }
   public function activity_log(Request $request){
       
        // $query = AthleteActivity::with(['passedZips'])->where('athlete_id', auth('athlete')->id())
        //     ->orderBy('date', 'desc');

        
        // if ($request->last_date) {
        //     $query->where('date', '<', $request->last_date);
        // }

        // $activities = $query->take(28)->get();

        // if ($activities->isEmpty()) {
        //     return '';
        // }

        // $weeks = $activities->groupBy(function ($activity) {
        //     return Carbon::parse($activity->date)
        //         ->startOfWeek()
        //         ->format('Y-m-d');
        // });
        // foreach($weeks as $weekStart => $activities){
        //      for($i = 0; $i < 7; $i++){
             
        //           //  $day = \Carbon\Carbon::parse($weekStart)->addDays($i)->format('Y-m-d');
        //             $dayDate = \Carbon\Carbon::parse($weekStart)->addDays($i);
        //             $day = $dayDate->format('Y-m-d');

        //             $dayActivities = $activities->filter(function ($activity) use ($day) {
        //                 return $activity->date->format('Y-m-d') === $day;
        //             });

        //              $dayDate = \Carbon\Carbon::parse($weekStart)->addDays($i);
        //             $day = $dayDate->format('Y-m-d');

        //             $dayActivities = $activities->filter(function ($activity) use ($day) {
        //                 return $activity->date->format('Y-m-d') === $day;
        //             });
        //           if($dayActivities->count()){
        //            // dd($dayActivities->first()->passedZips);
        //           }
        //      }
               
        // }
       return view('account.activity.activity-calendar');
   }


    public function activity_log_more(Request $request)
    {
        $query = AthleteActivity::with(['passedZips'])->where('athlete_id', auth('athlete')->id())
            ->orderBy('date', 'desc');

        
        if ($request->last_date) {
            $query->where('date', '<', $request->last_date);
        }

        $activities = $query->take(28)->get();

        if ($activities->isEmpty()) {
            return '';
        }
        
        $weeks = $activities->groupBy(function ($activity) {
            return Carbon::parse($activity->date)
                ->startOfWeek()
                ->format('Y-m-d');
        });

        return view('account.activity.activity-weeks', compact('weeks'))->render();
    }
    public function mapBbox(Request $request)
    {
        $bbox = $request->bbox;

        $features = [];

        foreach ($this->zipFeatureStream() as $feature) {

            if (!isset($feature['geometry']['coordinates'][0])) continue;

            $outerRing = $feature['geometry']['coordinates'][0];
            $polyBox = $this->bbox($outerRing);

            if (
                $polyBox['maxLng'] >= $bbox[0] &&
                $polyBox['minLng'] <= $bbox[2] &&
                $polyBox['maxLat'] >= $bbox[1] &&
                $polyBox['minLat'] <= $bbox[3]
            ) {
                $features[] = $feature;
            }
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
    }

} 
