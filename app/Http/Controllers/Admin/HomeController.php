<?php

namespace App\Http\Controllers\Admin;

use App\Models\CareerApplication;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\AthleteActivity;
use App\Models\Timeline;
use Illuminate\Http\Request;
use Session;

class HomeController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
       $athleteCount=Athlete::get()->count();
       $activityCount=AthleteActivity::get()->count();
        return view('admin.home')->with(['athleteCount' =>$athleteCount ,'activityCount' => $activityCount]);
    }
}
