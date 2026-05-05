<div class="segment-expanded-container p-4" style="background-color: #fff;">

    <div class="row">

        <!-- LEFT SIDE -->
        <div class="col-md-7">
            <div class="row text-center mb-4">
           <div class="col-6">
                <h2 class="me-4 mb-0">
                    {{ gmdate("H:i:s", $zip->moving_sec) }}
                </h2>

                <div class="text-muted">
                    <div>Moving Time</div>
                </div>
            </div>
            <div class="col-6">
                <h2 class="me-4 mb-0">
                    {{ gmdate("H:i:s", $zip->elapsed_sec) }}
                </h2>

                <div class="text-muted">
                    <div>Elapsed Time</div>
                </div>
            </div>
            </div>
            <div class="row text-center mb-4">
                <div class="col-6">
                    <h6 class="text-muted">AVG</h6>
                    <div>Speed {{ $zip->speed_mph }} mi/h</div>
                   
                </div>

                <div class="col-6">
                    <h6 class="text-muted">MAX</h6>
                    <div>Speed {{ $zip->max_speed_mph }}  mi/h</div>
                </div>
            </div>
 
        </div>


        <!-- RIGHT SIDE -->
        <div class="col-md-5 border-start" style="background-color: #f7f7fa;">
           <div class="row">
             <div class="col-12">
                <div class="d-flex align-items-center mb-2">
                    <img src="{{ $zip->mostPassed->athlete->profile_medium?$zip->mostPassed->athlete->profile_medium: asset('front/img/general/account.jpg.jpg') }}" class="profile-avatar me-2">
                    <div>
                        <strong>{{$zip->mostPassed->athlete->first_name.' '. $zip->mostPassed->athlete->last_name}}</strong><br>
                        <small class="text-muted">
                            {{$zip->mostPassed->total_passes}} efforts in the last 90 days
                        </small>
                    </div>
                    
                </div>
                <div class="d-flex align-items-center mb-2"><a class="btn btn-primary" style="background-color:#fff;border-color:#bcbcc1;color:black" >View Local Legend Stats</a></div>
             </div>
            <div class="col-12">
            <h5 class="mb-3">Top 10</h5>

            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Athlete</th>
                        <th>Distance</th>
                        <th>Speed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($zip->topRanks as $rank)
                     
                        <tr>
                            <td></td>
                            <td>{{ $rank->athlete->first_name.' '.$rank->athlete->last_name }}</td>
                            <td>{{ $rank->max_distance }} mi</td>
                            <td>{{ $rank->speed_mph }} mph</td>
                        </tr>
                        
                    @endforeach
                </tbody>
            </table>
           </div>
            <div class="col-12">
                    <div class="d-flex align-items-center mb-2"><a class="btn btn-primary" style="background-color:#fff;border-color:#bcbcc1;color:black" href="{{route('segments.leaderboard', $zip->zip_code)}}">View Full Leaderboard</a></div>
            </div>
        </div>

    </div>

</div>
