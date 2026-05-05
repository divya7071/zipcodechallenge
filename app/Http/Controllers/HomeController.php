<?php

namespace App\Http\Controllers;

use App\Models\AthleteActivity;
use App\Models\AthleteActivityZipStat;
use App\Models\Contact;
use Illuminate\Http\Request;
//use Endlessmiles\Polyline\Polyline;
use Illuminate\Support\Facades\DB;
use App\Traits\ZipIntersectionTrait;
use Illuminate\Support\Facades\Auth;
use Polyline;

class HomeController extends Controller
{
    use ZipIntersectionTrait;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
 

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index()
    {
     
        $achievement = AthleteActivityZipStat::whereHas('activity')->select(
        'zip_code',
                DB::raw('SUM(distance_mi) as total_distance')
            )
            ->groupBy('zip_code')
            ->orderByDesc('total_distance')
            ->first();
        $totalZipCodes = AthleteActivityZipStat::whereHas('activity')->distinct('zip_code')
        ->count('zip_code');
        $topState = AthleteActivityZipStat::whereHas('activity')->join('zip_codes', 'athlete_activity_zip_stats.zip_code', '=', 'zip_codes.zip_code')
            ->select('zip_codes.state', DB::raw('COUNT(*) as total'))
            ->groupBy('zip_codes.state')
            ->orderByDesc('total')
            ->value('zip_codes.state');

            $totalActivity = AthleteActivity::distinct('activity_id')
        ->count('activity_id');
        $result = DB::table('athlete_activity_zip_stats')
            ->selectRaw('
                COUNT(DISTINCT zip_code) as total_zipcodes,
                MAX(elevation_gain_ft) as highest_elevation,
                COUNT(*) as activity_count,
                MAX(max_speed_mph) as top_speed,

                (
                    SELECT COUNT(*) 
                    FROM athlete_activity_zip_stats 
                    WHERE YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)
                ) as weekly_activity_count,

                (
                    SELECT zip_code 
                    FROM athlete_activity_zip_stats 
                    GROUP BY zip_code 
                    ORDER BY SUM(distance_mi) DESC 
                    LIMIT 1
                ) as favourite_zipcode
            ')
            ->first();
    

      $zipCodes = AthleteActivityZipStat::whereHas('activity')->select(
        'zip_code',
        DB::raw('COUNT(*) as total_attempts'),
        DB::raw('SUM(distance_mi) as total_distance'),
        DB::raw('MAX(elevation_gain_ft) as highest_elevation'),
        DB::raw('MAX(max_speed_mph) as highest_speed'),
        DB::raw('MAX(date) as last_activity_date')
    )
    ->when(auth('athlete')->check(), function ($query) {
        $query->where('athlete_id', auth('athlete')->id());
    })
    ->groupBy('zip_code')
    ->orderByDesc('total_attempts')
    ->paginate(5);
 
        return view('home')->with(['achievement'=>$achievement,'totalZipCodes'=>$totalZipCodes,'totalActivity'=>$totalActivity,'result'=>$result,'zipCodes'=>$zipCodes,'topState'=>$topState]);
    }
    public function contact(){
        return view('information.contact');
    }
    public function contact_submit(Request $request){

            $contact=Contact::create(['name'=>$request->name,'email'=>$request->email,
                                    'phone'=>$request->phone,'subject'=>$request->subject,
                                    'message'=>$request->message]);
            if($contact){
                $data['error']=0;
                $data['message']="Contact us submit successfully";
            }   
            else{
                $data['error']=1;
                $data['message']="Something went wrong";
            }                   
                echo json_encode($data);
    }
    public function privacyPolicy(){
        return view('information.privacy-policy');
    }
    public function termsAndConditions(){
        return view('information.terms-and-conditions');
    }
    public function legal(){
        return view('information.legal');
    }
    public function howItWork(){
        return view('how-it-works');
    }
    public function dashboard(){
        return view('account.index');
    }
}
