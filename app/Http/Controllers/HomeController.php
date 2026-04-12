<?php

namespace App\Http\Controllers;

use App\Models\AthleteActivity;
use Illuminate\Http\Request;
//use Endlessmiles\Polyline\Polyline;
use Illuminate\Support\Facades\DB;
use App\Traits\ZipIntersectionTrait;
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
        return view('home');
    }
    public function contact(){
        return view('contact');
    }
     public function howItWork(){
        return view('how-it-works');
    }
}
