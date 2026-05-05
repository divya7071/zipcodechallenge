    @foreach($weeks as $weekStart => $activities)
 
    <div class="training-week" data-last-date="{{ $activities->last()->date }}">
 
        <div class="week-header">
            <h5>
                {{ \Carbon\Carbon::parse($weekStart)->format('M d') }}
                –
                  {{ \Carbon\Carbon::parse($weekStart)->format('Y') }}
                to
                {{ \Carbon\Carbon::parse($weekStart)->addDays(6)->format('M d') }}
                –
                {{ \Carbon\Carbon::parse($weekStart)->addDays(6)->format('Y') }}
            </h5>

           <div class="week-distance badge bg-dark text-white px-3 py-2 fs-6">
                {{ number_format($activities->sum('distance') / 1609.34, 2) }} mi
           </div>
        </div>

        <div class="week-grid">
       @for($i = 6; $i >= 0; $i--)
            @php
                $dayDate = \Carbon\Carbon::parse($weekStart)->addDays($i);
                $day = $dayDate->format('Y-m-d');

                $dayActivities = $activities->filter(function ($activity) use ($day) {
                    return $activity->date->format('Y-m-d') === $day;
                });

                $distance = $dayActivities->sum('distance');
            @endphp
                <div class="day-box text-center p-2 border rounded">
                    <div class="day-name">
                        {{ $dayDate->format('D') }}
                    </div>

                    <div class="day-date small text-muted">
                        {{ $dayDate->format('M d') }}
                    </div>

                    <div class="day-distance fw-bold 
                        {{ $distance > 0 ? 'text-success badge bg-light rounded px-2 py-1 d-inline-block' : 'text-muted' }}">
                        
                        {{ $distance > 0 
                            ? number_format($distance / 1609.34, 2) . ' mi' 
                            : ' ' 
                        }}
                    </div>
                    
                    <div class="day-date small text-muted">
                        @if($dayActivities->count())
                        Passed Zips: {{ $dayActivities->first()->passedZips->count() }}
                        <span class="small text-secondary" id="zipList">
                                       
                        </span>
                        @endif
                    </div>
                </div>
                
            @endfor
        </div>

    </div>

    @endforeach