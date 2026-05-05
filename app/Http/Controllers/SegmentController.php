<?php

namespace App\Http\Controllers;

use App\Models\AthleteActivity;
use App\Models\AthleteActivityZipStat;
use App\Models\ZipCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Traits\ZipIntersectionTrait;

class SegmentController extends Controller
{
    use ZipIntersectionTrait;
    public function index(Request $request,Datatables $datatables)
    {   
    if ($request->ajax()) {
       $subQuery = AthleteActivityZipStat::whereHas('activity')->select(
        'zip_code',
        DB::raw('COUNT(*) as total_attempts'),
        DB::raw('SUM(distance_mi) as total_distance'),
        DB::raw('MAX(elevation_gain_ft) as highest_elevation'),
        DB::raw('MAX(max_speed_mph) as highest_speed')
    )
    ->where('athlete_id', auth('athlete')->user()->id)
    ->groupBy('zip_code');

    $query = DB::table(DB::raw("({$subQuery->toSql()}) as stats"))
        ->mergeBindings($subQuery->getQuery());
            return DataTables::of($query)

                ->addColumn('zipcode', function ($row) {
                    return $row->zip_code;
                })

                ->addColumn('action', function ($row) {
                    return '<a href="'.route('account.segments.leaderboard', $row->zip_code).'" 
                                class="btn btn-sm btn-primary act-view-btn">
                                View
                            </a>';
                })

                ->rawColumns(['action'])
                ->make(true);
        }
        return view('account.passed_zip');
    }
    public function leaderboard(Request $request,Datatables $datatables)
    {
        $totalZips = ZipCode::distinct('zip_code')->count('zip_code');
        if ($request->ajax()) {

            $zipQuery = ZipCode::query();
                if ($request->country) {
                    $zipQuery->where('country_code', $request->country);
                }

                if ($request->state) {
                    $zipQuery->where('state', $request->state);
                }

                $allCount = $zipQuery->distinct('zip_code')->count('zip_code');

     
               $query = AthleteActivityZipStat::whereHas('activity')->with('athlete')
                ->join('zip_codes', 'athlete_activity_zip_stats.zip_code', '=', 'zip_codes.zip_code')
                ->join('athlete_activities', 'athlete_activity_zip_stats.athlete_activity_id', '=', 'athlete_activities.id')
                ->select(
                    'athlete_activity_zip_stats.athlete_id',
                    DB::raw('COUNT(DISTINCT athlete_activity_zip_stats.zip_code) as total_zips'),
                    DB::raw('SUM(athlete_activity_zip_stats.distance_mi) as total_distance')
                )
                ->groupBy('athlete_activity_zip_stats.athlete_id');
            
            if ($request->country) {
                $query->where('zip_codes.country_code', $request->country);
            }

            if ($request->state) {
                $query->where('zip_codes.state', $request->state);
            }
            if ($request->sport_type) {
                $query->where('athlete_activities.sport_type', $request->sport_type);
            }
            if ($request->start_date && $request->end_date) {
                $query->whereBetween('athlete_activity_zip_stats.date', [
                    $request->start_date,
                    $request->end_date
                ]);
            }
            $query->groupBy('athlete_activity_zip_stats.athlete_id');
        
            return DataTables::of($query)
                ->addIndexColumn()
               ->editColumn('athlete_name', function ($row) {
                    $name = $row->athlete->first_name . ' ' . $row->athlete->last_name;

                    return '<span class="text-warning me-1"><i class="bi bi-star-fill"></i></span>' . $name;
                })
                ->addColumn('athlete_name', function ($row) {
                    $name = $row->athlete->first_name . ' ' . $row->athlete->last_name;

                    return '<span class="text-warning me-1"><i class="bi bi-star-fill"></i></span>' . $name;
                })

                ->addColumn('total_zips', function ($row) {
                    return $row->total_zips;
                })
                 ->editColumn('total_distance', function ($row) {
                    return $row->total_distance.' mi';
                })
               ->addColumn('percentage', function ($row) use ($totalZips) {
                    $percent = $totalZips ? ($row->total_zips / $totalZips) * 100 : 0;
                    return '<div class="circular-progress" style="--value:'.$percent.'%">
                    <span>  '.number_format($percent,2).' %</span>
                </div>';
                      
                })
                ->addColumn('rank', function ($row) {
                    static $rank = 1;
                    return $rank++;
                })

                ->addColumn('action', function ($row) {
                    return '<a href="'.route('account.segments.leaderboard', $row->athlete_id).'" class="btn btn-sm btn-primary">View</a>';
                })

               ->order(function ($query) {
                        $query->orderByDesc('total_zips');
                    })
                 ->with([
                    'allCount' => $allCount 
                ])

                ->rawColumns(['athlete_name','percentage','action'])
                ->make(true);
        }
        $allCount = ZipCode::count();
        $countries=ZipCode::pluck('country','country_code')->unique();;
        $states=ZipCode::pluck('state')->unique();
        $totalAthletes = AthleteActivityZipStat::whereHas('activity')->distinct('athlete_id')->count('athlete_id');
        return view('account.leader-board')->with(['allCount'=>$allCount,'sportTypes' => AthleteActivity::distinct()->pluck('sport_type'),'countries'=>$countries,'states'=>$states,'totalAthletes'=>$totalAthletes]);
    }
    public function leaderboard_old(Request $request,Datatables $datatables)
    {
        if ($request->ajax()) {
        $subQuery = AthleteActivityZipStat::whereHas('activity')->select(
                'zip_code',
                DB::raw('MAX(distance_mi) as max_distance')
            )
            ->groupBy('zip_code');

            $query = AthleteActivityZipStat::whereHas('activity')->with('athlete')
            ->joinSub($subQuery, 'max_table', function ($join) {
                $join->on('athlete_activity_zip_stats.zip_code', '=', 'max_table.zip_code')
                    ->on('athlete_activity_zip_stats.distance_mi', '=', 'max_table.max_distance');
            })
            ->select('athlete_activity_zip_stats.*');

            return DataTables::of($query)
                ->addIndexColumn()

               ->addColumn('athlete_name', function ($row) {

                    $name = $row->athlete->first_name . ' ' . $row->athlete->last_name;

                    return '<span class="text-warning me-1"><i class="bi bi-star-fill"></i></span>' . $name;
                })

                ->editColumn('distance_mi', function ($row) {
                    return number_format($row->distance_mi, 2) . ' mi';
                })

                ->editColumn('speed_mph', function ($row) {
                    return number_format($row->speed_mph, 2) . ' mi/h';
                })

                ->editColumn('moving_sec', function ($row) {
                    return gmdate('H:i:s', $row->moving_sec);
                })

                ->addColumn('action', function ($row) {
                    return '<a href="'.route('account.segments.leaderboard',$row->zip_code).'" class="btn btn-sm btn-primary">View</a>';
                })

                ->order(function ($query) {
                    $query->orderByDesc('distance_mi')
                        ->orderByDesc('speed_mph');
                })
                ->rawColumns(['athlete_name','action'])
                ->make(true);
         }
        return view('account.leader_board');
    }


    public function zip_leaderboard($zipcode,Datatables $datatables)
    {
  
        // $subQuery = AthleteActivityZipStat::select(
        // 'athlete_id',
        //     DB::raw('MAX(distance_mi) as max_distance')
        // )
        // ->where('zip_code', $zipcode)
        // ->groupBy('athlete_id');
  
        // $query = AthleteActivityZipStat::with('athlete')->whereHas('activity')->distinct('athlete_activity_zip_stats.athlete_id')
        // ->joinSub($subQuery, 'max_table', function ($join) {
        //     $join->on('athlete_activity_zip_stats.athlete_id', '=', 'max_table.athlete_id')
        //          ->on('athlete_activity_zip_stats.distance_mi', '=', 'max_table.max_distance');
        // })
        // ->where('athlete_activity_zip_stats.zip_code', $zipcode)
        
        // ->select('athlete_activity_zip_stats.*');
         $query = AthleteActivityZipStat::whereHas('activity')->where('athlete_id',auth('athlete')->user()->id)->whereHas('activity')
        ->where('athlete_activity_zip_stats.zip_code', $zipcode)
        
        ->select('athlete_activity_zip_stats.*');
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('date',function ($row) {
                   return $row->date? \Carbon\Carbon::parse($row->date)->format('M d, Y'): '-';
                }) 
            ->addColumn('athlete_name', function ($row) {
                return $row->athlete->first_name . ' ' . $row->athlete->last_name;
            })

            ->editColumn('distance_mi', function ($row) {
                return number_format($row->distance_mi, 2) . ' mi';
            })

            ->editColumn('moving_sec', function ($row) {
                return gmdate('H:i:s', $row->moving_sec);
            })

            ->editColumn('speed_mph', function ($row) {
                return number_format($row->speed_mph, 2) . ' mi/h';
            })

            ->order(function ($query) {
                $query->orderByDesc('athlete_activity_zip_stats.distance_mi')
                    ->orderByDesc('athlete_activity_zip_stats.speed_mph');
            })

            ->make(true);
    }

    public function detail($zipcode)
    {
        $authAthleteId = auth()->user()->id;

        $baseQuery = AthleteActivityZipStat::whereHas('activity')->where('zip_code', $zipcode);

        $segment = $baseQuery->clone()
            ->with(['athlete','activity.activity_map'])
            ->orderByDesc('distance_mi')
            ->orderByDesc('speed_mph')
            ->first();

        $totalAttempts = $baseQuery->clone()->where('athlete_id', $authAthleteId)->count();

        $totalPeople = $baseQuery->clone()
            ->distinct('athlete_id')
            ->count('athlete_id');

        $topThree = $baseQuery->clone()
            ->with('athlete')
            ->orderByDesc('distance_mi')
            ->orderByDesc('speed_mph')
            ->take(3)
            ->get();

        $myBest = $baseQuery->clone()
            ->where('athlete_id', $authAthleteId)
            ->orderByDesc('distance_mi')
            ->orderByDesc('speed_mph')
            ->first();
        $highestElevation = (clone $baseQuery)
            ->where('athlete_id', $authAthleteId)
            ->max('elevation_gain_ft');

        $lowestElevation = (clone $baseQuery)
            ->where('athlete_id', $authAthleteId)
            ->min('elevation_gain_ft');
        $localLegend = AthleteActivityZipStat::whereHas('activity')->select(
            'athlete_id',
            DB::raw('COUNT(*) as total_attempts')
            )
            ->where('zip_code', $zipcode)
            ->groupBy('athlete_id')
            ->orderByDesc('total_attempts')
            ->with('athlete')
            ->first();
        $myRank = null;
        $myBestTime = null;

        if ($myBest) {

           
            $myRank = $baseQuery->clone()
                ->where(function ($q) use ($myBest) {
                    $q->where('distance_mi', '>', $myBest->distance_mi)
                    ->orWhere(function ($q2) use ($myBest) {
                        $q2->where('distance_mi', $myBest->distance_mi)
                            ->where('speed_mph', '>', $myBest->speed_mph);
                    });
                })
                ->count() + 1;

            $myBestTime = gmdate('H:i:s', $myBest->moving_sec);
        }
     
        $selectedZip = $this->getSingleZipFeature($zipcode);
      
        return view('account.zip_leader_board', [
            'segment'        => $segment,
            'totalAttempts'  => $totalAttempts,
            'totalPeople'    => $totalPeople,
            'myRank'         => $myRank,
            'myBestTime'     => $myBestTime,
            'topThree'       => $topThree,
            'localLegend'    => $localLegend,
            'zipcode'        => $zipcode,
            'selectedZip'    => $selectedZip,
            'myBest'         => $myBest,
            'highestElevation'=> $highestElevation,
            'lowestElevation' => $lowestElevation,
           
        ]);
    }
  public function pendingZips(Request $request)
    {
        $zips = ZipCode::whereNotIn('zip_code', function ($query) {
                $query->select('zip_code')
                    ->from('athlete_activity_zip_stats');
            })
            ->paginate(60);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('partials.zip-items', compact('zips'))->render(),
                'remaining' => $zips->total() - $zips->currentPage() * $zips->perPage()
            ]);
        }

        $remaining = $zips->total() - $zips->perPage();

        return view('account.todo-list', compact('zips', 'remaining'));
    }
    // public function pendingZips(Request $request)
    // {
    //     $type = $request->type ?? 'todo';

    //     if ($type === 'passed') {
    //         $zips = ZipCode::whereIn('zip_code', function ($query) {
    //                 $query->select('zip_code')->from('athlete_activity_zip_stats');
    //             })
    //             ->paginate(30);
    //     } else {
    //         $zips = ZipCode::whereNotIn('zip_code', function ($query) {
    //                 $query->select('zip_code')->from('athlete_activity_zip_stats');
    //             })
    //             ->paginate(30);
    //     }

    //     if ($request->ajax()) {
    //         return view('account.partials.zip-items', compact('zips'))->render();
    //     }

    //     return view('account.todo-list', compact('zips'));
    // }
    public function passedZips(Request $request)
    {
        $zips  = AthleteActivityZipStat::whereHas('activity')->where('athlete_id', auth('athlete')->id())
            ->distinct('zip_code')->select('zip_code')
            ->paginate(60);
            
       if ($request->ajax()) {
            return response()->json([
                'html' => view('partials.zip-items', compact('zips'))->render(),
                'remaining' => $zips->total() - $zips->currentPage() * $zips->perPage()
            ]);
        }

        $remaining = $zips->total() - $zips->perPage();

        return view('account.passed-list', compact('zips', 'remaining'));
    }
    
    // public function passedZips(Request $request)
    // {
    //     $zips =   $passedZips = AthleteActivityZipStat::where('athlete_id', auth('athlete')->id())
    //         ->distinct()
    //         ->paginate(60);
       
    //     if ($request->ajax()) {
    //         return view('partials.zip-items', compact('zips'))->render();
    //     }

    //     return view('passed-list', compact('zips'));
    // }
    // public function exploreMap()
    // {
    //     $passedZips = AthleteActivityZipStat::where('athlete_id', auth('athlete')->id())
    //         ->distinct()
    //         ->pluck('zip_code')
    //         ->toArray();
    //     $activity=AthleteActivity::with(['media','athlete'])
    //         ->orderBy('date', 'desc')
    //         ->first();
    //   $polyline =$activity->activity_map?json_decode($activity->activity_map->map)->summary_polyline:'';
    //     return view('account.explore-map', compact('passedZips','polyline'));
    // }
    public function exploreMap()
    {
        $passedZips = AthleteActivityZipStat::whereHas('activity')->where('athlete_id', auth('athlete')->id())
        ->selectRaw('zip_code, COUNT(*) as total')
        ->groupBy('zip_code')
        ->pluck('total', 'zip_code')
        ->toArray();
            
        $activity=AthleteActivity::with(['media','athlete'])
            ->orderBy('date', 'desc')
            ->first();
       $polyline =$activity->activity_map?json_decode($activity->activity_map->map)->summary_polyline:'';
        return view('account.explore-map', compact('passedZips','polyline'));
    }
    public function loadMapPassedZips()
    {
           $zips = AthleteActivityZipStat::whereHas('activity')->where('athlete_id', auth('athlete')->id())
            ->distinct()
            ->pluck('zip_code')
            ->toArray();
           $favouriteZip = AthleteActivityZipStat::whereHas('activity')->select(
            'zip_code',
            DB::raw('SUM(distance_mi) as total_distance'),
            DB::raw('COUNT(*) as total_visits')
        )
        ->where('athlete_id', auth('athlete')->user()->id)
        ->groupBy('zip_code')
        ->orderByDesc('total_distance')
        ->first();
      
        if (empty($zips)) {
            return response()->json([
                'type' => 'FeatureCollection',
                'features' => []
            ]);
        }

        $remaining = array_fill_keys($zips, true);

        $features = [];
        $foundZips = [];

        foreach ($this->zipFeatureStream() as $feature) {

            $zip = $feature['properties']['postcode'] ?? null;

            if (!$zip) continue;

            // 4. Match only needed ZIPs
            if (isset($remaining[$zip])) {

                $features[] = $feature;

                // Track found ZIP
                $foundZips[$zip] = true;
              
                unset($remaining[$zip]);
             
                if (empty($remaining)) {
                    break;
                }
            }
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
            'found' => array_keys($foundZips),
             'favouriteZip' => $favouriteZip->zip_code ?? null
        ]);
    }
    public function zipsInView(Request $request)
    {
        $MAX_FEATURES = 3000;
        $count = 0;
        // try {

            $bbox = [
                'minLng' => (float)$request->minLng,
                'maxLng' => (float)$request->maxLng,
                'minLat' => (float)$request->minLat,
                'maxLat' => (float)$request->maxLat,
            ];

            $features = [];

            foreach ($this->zipFeatureStream() as $feature) {

                if ($count >= $MAX_FEATURES) break;

                if (!isset($feature['geometry']['coordinates'])) continue;

                 $geometry = $feature['geometry'] ?? null;

                if (!$geometry || !isset($geometry['type'], $geometry['coordinates'])) {
                    continue;
                }

                $type = $geometry['type'];
                $coords = $geometry['coordinates'];

                $outerRing = null;

                if ($type === 'Polygon') {
                    $outerRing = $coords[0] ?? null;
                }

                elseif ($type === 'MultiPolygon') {
                    $outerRing = $coords[0][0] ?? null;
                }

                if (!is_array($outerRing) || count($outerRing) < 3) {
                    continue;
                }
              
                $polyBox = $this->bbox($outerRing);
                // dd($polyBox);
                // bbox intersection check
                if (
                    $polyBox['maxLng'] < $bbox['minLng'] ||
                    $polyBox['minLng'] > $bbox['maxLng'] ||
                    $polyBox['maxLat'] < $bbox['minLat'] ||
                    $polyBox['minLat'] > $bbox['maxLat']
                ) {
                    continue;
                }
                
               
                $features[] = $feature;
                $count++;
            }
        //   dd($features);
            return response()->json([
                'type' => 'FeatureCollection',
                'features' => $features
            ]);

        // } catch (\Throwable $e) {
        //     return response()->json([
        //         'error' => $e->getMessage()
        //     ], 500);
        // }
    }
    public function getSingleZipview(Request $request){
         $selectedZip = $this->getSingleZipFeature($request->zipcode);
            return response()->json([
            'selectedZip' => json_decode(json_encode($selectedZip), true)
        ]);
    }
  public function getAllZipsInView(Request $request)
    {
        try {
             
            $north = $request->north;
            $south = $request->south;
            $east  = $request->east;
            $west  = $request->west;

            $features = [];

            foreach ($this->zipFeatureStream() as $feature) {

                if (!isset($feature['geometry']['coordinates'])) continue;

                $geometry = $feature['geometry'];

                $type = $geometry['type'];
                $coords = $geometry['coordinates'];

                // Get outer ring
                if ($type === 'Polygon') {
                    $outerRing = $coords[0] ?? null;
                } elseif ($type === 'MultiPolygon') {
                    $outerRing = $coords[0][0] ?? null;
                } else {
                    continue;
                }

                if (!is_array($outerRing)) continue;

                // ✅ FAST BOUND CHECK (exit early)
                $inside = false;

                foreach ($outerRing as $point) {
                    [$lng, $lat] = $point;

                    if ($lat >= $south && $lat <= $north && $lng >= $west && $lng <= $east) {
                        $inside = true;
                        break; // ✅ stop early
                    }
                }

                if (!$inside) continue;

                $features[] = $feature;

                // ✅ LIMIT results (VERY IMPORTANT)
                if (count($features) > 500) break;
            }

            return response()->json([
                'type' => 'FeatureCollection',
                'features' => $features
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
 private function bbox1($coordinates)
    {
    $minLng = INF;
    $minLat = INF;
    $maxLng = -INF;
    $maxLat = -INF;

    foreach ($coordinates as $coord) {

        if (!isset($coord[0], $coord[1])) continue;

        $lng = (float) $coord[0];
        $lat = (float) $coord[1];

        $minLng = min($minLng, $lng);
        $maxLng = max($maxLng, $lng);
        $minLat = min($minLat, $lat);
        $maxLat = max($maxLat, $lat);
    }
    }
    public function getAllZipsInView1(Request $request)
    {
    try {

        $north = $request->north;
        $south = $request->south;
        $east  = $request->east;
        $west  = $request->west;

        $features = [];

        foreach ($this->zipFeatureStream() as $feature) {

            if (!isset($feature['geometry']['coordinates'])) continue;

            $geometry = $feature['geometry'];

            $type = $geometry['type'];
            $coords = $geometry['coordinates'];

            // Get outer ring
            if ($type === 'Polygon') {
                $outerRing = $coords[0] ?? null;
            } elseif ($type === 'MultiPolygon') {
                $outerRing = $coords[0][0] ?? null;
            } else {
                continue;
            }

            if (!is_array($outerRing)) continue;

            // ✅ FAST BOUND CHECK (exit early)
            $inside = false;

            foreach ($outerRing as $point) {
                [$lng, $lat] = $point;

                if ($lat >= $south && $lat <= $north && $lng >= $west && $lng <= $east) {
                    $inside = true;
                    break; 
                }
            }

            if (!$inside) continue;

            $features[] = $feature;

          
            if (count($features) > 500) break;
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }


    return [
        'minLng' => $minLng,
        'maxLng' => $maxLng,
        'minLat' => $minLat,
        'maxLat' => $maxLat,
    ];
}
    private function bbox($coords)
    {
        $minLng = $minLat = PHP_INT_MAX;
        $maxLng = $maxLat = PHP_INT_MIN;

        foreach ($coords as $point) {
            $lng = $point[0];
            $lat = $point[1];

            $minLng = min($minLng, $lng);
            $maxLng = max($maxLng, $lng);
            $minLat = min($minLat, $lat);
            $maxLat = max($maxLat, $lat);
        }

        return compact('minLng','maxLng','minLat','maxLat');
    }

    private function bboxIntersects($a, $b)
    {
        return !(
            $a['maxLng'] < $b['minLng'] ||
            $a['minLng'] > $b['maxLng'] ||
            $a['maxLat'] < $b['minLat'] ||
            $a['minLat'] > $b['maxLat']
        );
    }
}
