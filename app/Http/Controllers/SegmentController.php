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
        $query = AthleteActivityZipStat::select(
                'zip_code',
                DB::raw('COUNT(*) as total_attempts'),
                DB::raw('SUM(distance_mi) as total_distance'),
                DB::raw('MAX(elevation_gain_ft) as highest_elevation'),
                DB::raw('MAX(max_speed_mph) as highest_speed')
            )
            ->where('athlete_id', auth('athlete')->user()->id)
            ->groupBy('zip_code');
            return DataTables::of($query)

                ->addColumn('zipcode', function ($row) {
                    return $row->zip_code;
                })

                ->addColumn('action', function ($row) {
                    return '<a href="'.route('segments.leaderboard', $row->zip_code).'" 
                                class="btn btn-sm btn-primary">
                                View
                            </a>';
                })

                ->rawColumns(['action'])
                ->make(true);
        }
        return view('segment.passed_zip');
    }
    public function leaderboard(Request $request,Datatables $datatables)
    {
        $totalZips = ZipCode::distinct('zip_code')->count('zip_code');
        if ($request->ajax()) {

            // $query = AthleteActivityZipStat::with('athlete')
            //     ->select(
            //         'athlete_id',
            //         DB::raw('COUNT(DISTINCT zip_code) as total_zips')
            //     )
            //     ->groupBy('athlete_id');
              $zipQuery = ZipCode::query();
                if ($request->country) {
                    $zipQuery->where('country_code', $request->country);
                }

                if ($request->state) {
                    $zipQuery->where('state', $request->state);
                }

                $allCount = $zipQuery->distinct('zip_code')->count('zip_code');

            // $query = AthleteActivityZipStat::with('athlete')
            // ->join('zip_codes', 'athlete_activity_zip_stats.zip_code', '=', 'zip_codes.zip_code')
            // ->select(
            //     'athlete_activity_zip_stats.athlete_id',
            //     DB::raw('COUNT(DISTINCT athlete_activity_zip_stats.zip_code) as total_zips')
            // );
            $query = AthleteActivityZipStat::with('athlete')
            ->join('zip_codes', 'athlete_activity_zip_stats.zip_code', '=', 'zip_codes.zip_code')
            ->join('athlete_activities', 'athlete_activity_zip_stats.athlete_activity_id', '=', 'athlete_activities.id')
            ->select(
                'athlete_activity_zip_stats.athlete_id',
                DB::raw('COUNT(DISTINCT athlete_activity_zip_stats.zip_code) as total_zips')
            );
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

                ->addColumn('athlete_name', function ($row) {
                    $name = $row->athlete->first_name . ' ' . $row->athlete->last_name;

                    return '<span class="text-warning me-1"><i class="bi bi-star-fill"></i></span>' . $name;
                })

                ->addColumn('total_zips', function ($row) {
                    return $row->total_zips;
                })
                // ->addColumn('percentage', function ($row) use ($totalZips) {
                //     return $totalZips ? round(($row->total_zips / $totalZips) * 100) : 0;
                // })
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
                    return '<a href="'.route('leaderboard', $row->athlete_id).'" class="btn btn-sm btn-primary">View</a>';
                })

                ->order(function ($query) {
                    $query->orderByDesc(DB::raw('COUNT(DISTINCT zip_code)'));
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

        return view('leader-board')->with(['allCount'=>$allCount,'sportTypes' => AthleteActivity::distinct()->pluck('sport_type'),'countries'=>$countries,'states'=>$states]);
    }
    public function leaderboard_old(Request $request,Datatables $datatables)
    {
        if ($request->ajax()) {
        $subQuery = AthleteActivityZipStat::select(
                'zip_code',
                DB::raw('MAX(distance_mi) as max_distance')
            )
            ->groupBy('zip_code');

            $query = AthleteActivityZipStat::with('athlete')
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
                    return '<a href="'.route('segments.leaderboard',$row->zip_code).'" class="btn btn-sm btn-primary">View</a>';
                })

                ->order(function ($query) {
                    $query->orderByDesc('distance_mi')
                        ->orderByDesc('speed_mph');
                })
                ->rawColumns(['athlete_name','action'])
                ->make(true);
         }
        return view('segment.leader_board');
    }


    public function zip_leaderboard($zipcode,Datatables $datatables)
    {
        
        $subQuery = AthleteActivityZipStat::select(
                'athlete_id',
                DB::raw('MAX(distance_mi) as max_distance')
            )
            ->where('zip_code', $zipcode)
            ->groupBy('athlete_id');

        $query = AthleteActivityZipStat::with('athlete')
        ->where('zip_code', $zipcode)
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

        $baseQuery = AthleteActivityZipStat::where('zip_code', $zipcode);

        $segment = $baseQuery->clone()
            ->with(['athlete','activity.activity_map'])
            ->orderByDesc('distance_mi')
            ->orderByDesc('speed_mph')
            ->first();

        $totalAttempts = $baseQuery->clone()->count();

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
        $localLegend = AthleteActivityZipStat::select(
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
      
        return view('segment.zip_leader_board', [
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
            ->paginate(30); 

        if ($request->ajax()) {
            if(count($zips))
            return view('partials.zip-items', compact('zips'))->render();
             else
             return '';
        }

        $allCount = ZipCode::count();

        return view('todo-list', compact('zips', 'allCount'));
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
    //         return view('segment.partials.zip-items', compact('zips'))->render();
    //     }

    //     return view('segment.todo-list', compact('zips'));
    // }
    public function passedZips(Request $request)
    {
        $zips =   $passedZips = AthleteActivityZipStat::where('athlete_id', auth('athlete')->id())
            ->distinct()
            ->paginate(30);
       
        if ($request->ajax()) {
            return view('partials.zip-items', compact('zips'))->render();
        }

        return view('passed-list', compact('zips'));
    }
    public function exploreMap()
    {
        $passedZips = AthleteActivityZipStat::where('athlete_id', auth('athlete')->id())
            ->distinct()
            ->pluck('zip_code')
            ->toArray();
        $activity=AthleteActivity::with(['media','athlete'])
            ->orderBy('date', 'desc')
            ->first();
       $polyline =$activity->activity_map?json_decode($activity->activity_map->map)->summary_polyline:'';
        return view('explore-map', compact('passedZips','polyline'));
    }
    public function zipsInView(Request $request)
    {
        $MAX_FEATURES = 300;
        $count = 0;
        try {

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
               //  dd($polyBox);
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

        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getSingleZipview(Request $request){
         $selectedZip = $this->getSingleZipFeature($request->zipcode);
            return response()->json([
            'selectedZip' => json_decode(json_encode($selectedZip), true)
        ]);
    }
 private function bbox($coordinates)
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

    return [
        'minLng' => $minLng,
        'maxLng' => $maxLng,
        'minLat' => $minLat,
        'maxLat' => $maxLat,
    ];
}
    // private function bbox($coords)
    // {
    //     $minLng = $minLat = PHP_INT_MAX;
    //     $maxLng = $maxLat = PHP_INT_MIN;

    //     foreach ($coords as $point) {
    //         $lng = $point[0];
    //         $lat = $point[1];

    //         $minLng = min($minLng, $lng);
    //         $maxLng = max($maxLng, $lng);
    //         $minLat = min($minLat, $lat);
    //         $maxLat = max($maxLat, $lat);
    //     }

    //     return compact('minLng','maxLng','minLat','maxLat');
    // }

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
