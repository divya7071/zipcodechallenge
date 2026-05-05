<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Athlete;
class AthleteController extends Controller
{
      public function index(Request $request, Datatables $datatables)
    {
        if ($request->ajax()) {
            $athlets = Athlete::select('athletes.*');
    
            return $datatables->eloquent($athlets)
                ->addColumn('profile', function (Athlete $athlete) {
                    return '<img src="'.$athlete->profile_medium.'" style="width:50px;height:50px"></img>' ; 
                })
                ->editColumn('sex', function (Athlete $athlete) {
                    return  $athlete->sex=='F'?'Female':($athlete->sex=='M'?'Male':'Other'); 
                })
                ->addColumn('name', function (Athlete $athlete) {
                    return $athlete->first_name?$athlete->first_name.' '.$athlete->last_name:'-' ; 
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
                // ->addColumn('action', function (Athlete $athlete) {
                //     $actions = '<div class="d-flex"><div class="dropdown">';
                //     $actions .= '<a href="' . route('admin.athlete-activity.index',['athlete'=> $athlete->id]). '" class="btn btn-sm btn-clean btn-icon text-end" title="Athlete Activities"><i class="bi bi-card-checklist"></i></a>';
                //     $actions .= '</div></div>';
                //     return $actions;
                // })
                ->rawColumns(['action','profile'])
                ->make(true);
        }

        $strava_status = Setting::where('code','strava_status')->value('value');

        return view('admin.athlete.list', ['strava_status' => $strava_status]);
    }
}
