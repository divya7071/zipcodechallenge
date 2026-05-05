@foreach ($activities as $activity)
    <div class="activity-card">

        <!-- Header -->
            <a href="{{route('athletes.show',$activity->athlete->athlete_id)}}">
        <div class="d-flex align-items-center mb-2">
            <img src="{{ auth('athlete')->user()->profile_medium?auth('athlete')->user()->profile_medium: asset('front/img/general/account.jpg.jpg') }}" class="profile-avatar me-2" style="width: 60px;height: 40px;">
            <div>
                <strong>{{$activity->athlete->first_name.' '. $activity->athlete->last_name}}</strong><br>
                <small class="text-muted">
                        @php
                        $date = $activity->date;
                        @endphp

                        @if ($date->isToday())
                            Today at {{ $date->format('g:i A') }}
                        @elseif ($date->isYesterday())
                            Yesterday at {{ $date->format('g:i A') }}
                        @else
                            {{ $date->format('F j, Y \a\t g:i A') }}
                        @endif
                    · {{$activity->start_location}}
                </small>
            </div>
        </div>
            </a>
        <!-- Title -->
        <a href="{{route('activity.show',$activity->id)}}">
        <h5 class="mt-3 mb-3">
            {{$activity->name}}
        </h5>
        </a>

        <!-- Stats -->
        <div class="row text-center border-top border-bottom py-3 mb-3">
            <div class="col">
                <div class="stat-value">{{number_format($activity->distance / 1609.344, 2).' mi'}}</div>
                <div class="stat-label">Distance</div>
            </div>
            <div class="col">
                @php
                $seconds = $activity->moving_time;
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $secs = $seconds % 60;
                @endphp

                <div class="stat-value">{{trim(($hours ? "{$hours} hr " : '') .($minutes ? "{$minutes} min " : '') ."{$secs} s");}}</div>
                <div class="stat-label">Time</div>
            </div>
            <div class="col">
                
                <div class="stat-value">
                    <i class="bi bi-trophy"></i> {{$activity->passedZips->count()}}
                </div>
                <div class="stat-label">Passed Zips</div>
            </div>
        </div>

        <!-- Map -->
        <div class="map-box mb-3">
                @if ($activity->map_image)
                    <img
                        src="{{ asset('storage/maps/'.$activity->map_image) }}"
                        class="w-100 rounded"
                        loading="lazy"
                        alt="Activity map"
                    >
                @else
                    <div
                        class="map-placeholder"
                        data-activity-id="{{ $activity->id }}"
                        data-polyline="{{ $activity->activity_map?->map
                            ? json_decode($activity->activity_map->map)->summary_polyline
                            : '' }}"
                        data-loaded="false"
                    ></div>
                @endif
        </div>

            <!-- Photos -->
            @if(!empty($activity->media))
            @php
            $allMedia = json_decode($activity->media->media);
            $totalMedia = count($allMedia);
            $visibleMedia = array_slice($allMedia, 0, 4);
            @endphp

            <div class="row g-2 mb-3">

            @foreach($visibleMedia as $index => $media)
                @if($media->type == 1 && !empty($media->url))
                <div class="col-3">
                    <div class="media-preview position-relative"
                        data-activity="{{ $activity->id }}"
                        style="cursor:pointer">

                        
                        <img src="{{ $media->url }}"
                            class="w-100 rounded"
                            style="height:150px; object-fit:cover;">
                        {{-- +More Overlay --}}
                        @if($loop->last && $totalMedia > 4)
                            <div class="position-absolute top-0 start-0 w-100 h-100
                                        d-flex align-items-center justify-content-center
                                        bg-dark bg-opacity-50 text-white fw-bold fs-4 rounded">
                                +{{ $totalMedia - 4 }}
                            </div>
                        @endif
                    </div>
                </div>
                @elseif($media->type == 2 && !empty($media->video_url))
                <div class="col-3">
                <div class="media-preview position-relative"
                        data-activity="{{ $activity->id }}"
                        style="cursor:pointer">
                        
                            <video class="w-100 rounded"
                                style="height:150px; object-fit:cover;">
                                <source src="{{ $media->video_url }}" type="video/mp4">
                            </video>
                            {{-- +More Overlay --}}
                        @if($loop->last && $totalMedia > 4)
                            <div class="position-absolute top-0 start-0 w-100 h-100
                                        d-flex align-items-center justify-content-center
                                        bg-dark bg-opacity-50 text-white fw-bold fs-4 rounded">
                                +{{ $totalMedia - 4 }}
                            </div>
                        @endif

                    </div>
                </div>
                @else
                $totalMedia--;
                @endif
            @endforeach
            
            </div>
            @endif
        
    </div>
    @endforeach

       