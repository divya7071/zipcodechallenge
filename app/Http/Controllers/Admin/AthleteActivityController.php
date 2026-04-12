<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Athlete;
use Illuminate\Http\Request;
use App\Models\StravaActivity;
use App\Traits\StravaHelper;
use Yajra\Datatables\Datatables;
use App\Models\AthleteActivity;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;

class AthleteActivityController extends Controller
{
     public function index(Request $request, Datatables $datatables)
    {
      
        if ($request->ajax()) {
            if($request->athlete){
                 $stravaActivities = AthleteActivity::where('athelete_id',$request->athlete_id)->with('athlete')->select('athlete_activities.*')->orderBy('date', 'DESC');
            }
            else{
                $stravaActivities = AthleteActivity::with('athlete')->select('athlete_activities.*')->orderBy('date', 'DESC');
            }
           
    
            return $datatables->eloquent($stravaActivities)
                ->addColumn('athlete', function ($row) {
                    return $row->athlete->first_name?$row->athlete->first_name.' '.$row->athlete->last_name:'-' ; 
                })
                ->editColumn('distance', function ($row) {
                    return $row->distance . ' m'; 
                })
                ->editColumn('moving_time', function ($row) {
                    return gmdate("H:i:s", $row->moving_time); 
                })
                ->editColumn('elapsed_time', function ($row) {
                    return gmdate("H:i:s", $row->elapsed_time);
                })
                 ->editColumn('passed_zips', function ($row) {
                if($row->passed_zips){
                    $zips = is_array($row->passed_zips) ? $row->passed_zips : json_decode($row->passed_zips, true);
                 $div='';
                foreach($zips as $zip){
                        $div.='<span class="badge bg-light text-dark me-1 mb-1">'. $zip .'</span>';
                }
                    return $div;
                }
               
                })
                ->editColumn('date', function ($row) {
                    return \Carbon\Carbon::parse($row->date)->format('Y-m-d H:i');
                })
                ->rawColumns(['passed_zips','distance'])
                ->make(true);
        }

        $athletes = Athlete::get();

        return view('admin.athlete_activity.list', ['athletes' => $athletes,'athelete_id'=>$request->athlete]);
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
        //
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

    public function getActivities()
    { 
         if (!auth('athlete')->check()) {
            return redirect()->route('home');
        } 
        Artisan::call('app:sync-strava-activities');
    }
}
