<div class="menu">
    <div class="menu-header">
        <a href="{{ url('/') }}" class="menu-header-logo">
            <img src="{{ url('assets/images/logo.png') }}" alt="logo">
            {{-- @if($isSimulationMode)
            <p style="font-size: 11px;
            padding-left: 15px;
            padding-top: 20px;
            color: #faae42;">Simulation<br>Mode</p>
            @endif --}}
        </a>
        <a href="{{ url('/') }}" class="btn btn-sm menu-close-btn">
            <i class="bi bi-x"></i>
        </a>
    </div>
    <div class="menu-body">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center" data-bs-toggle="dropdown">
                <div class="avatar me-3">
                    <div class="avatar avatar-primary me-1">
                        <span class="avatar-text rounded-circle">{{strtoupper(substr(auth()->user()->name, 0, 1))}}</span>
                    </div>
                </div>
                <div style="width: 90%;">
                    <div class="fw-bold">{{ auth()->user()->name }}</div>
                    <small class="text-muted">{{ auth()->user()->email }}</small>
                </div>
                <div class="">
                    <i class="bi bi-gear"></i>
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
                <a href="{{route('admin.account.index')}}" class="dropdown-item d-flex align-items-center">
                    <i class="bi bi-person dropdown-item-icon"></i> Profile
                </a>
                <!-- <a href="#" class="dropdown-item d-flex align-items-center">
                    <i class="bi bi-envelope dropdown-item-icon"></i> Inbox
                </a> -->
                <a href="javascript:;" onclick="event.preventDefault();document.getElementById('logout-form').submit();" class="dropdown-item d-flex align-items-center text-danger">
                    <i class="bi bi-box-arrow-right dropdown-item-icon"></i> Logout
                </a>
                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
        <ul>
            <li>
                <a @if(request()->fullUrl() == route('admin.dashboard')) class="active" @endif href="{{ route('admin.dashboard') }}">
                    <span class="nav-link-icon">
                        <i class="bi bi-bar-chart"></i>
                    </span>
                    <span>Dashboard</span>
                </a>
            </li>
             <li>
                <a @if(request()->fullUrl() == route('admin.athlete.index')) class="active" @endif href="{{ route('admin.athlete.index') }}">
                    <span class="nav-link-icon">
                        <i class="bi bi-person" aria-hidden="true"></i></span>
                    <span>Athletes</span>
                </a>
            </li>

           
            <li>
                <a @if(request()->fullUrl() == route('admin.athlete-activity.index')) class="active" @endif href="{{ route('admin.athlete-activity.index') }}">
                    <span class="nav-link-icon">
                        <i class="bi bi-card-checklist" aria-hidden="true"></i></span>
                    <span>Athlete Activities</span>
                </a>
            </li>

         
            <li>
                <a href="javascript:;">
                    <span class="nav-link-icon">
                        <i class="bi bi-sliders"></i>
                    </span>
                    <span>Systems</span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.settings.index', ['platform'=>'strava']) }}" @if(request()->fullUrl() == route('admin.settings.index',['platform'=>'strava'])) class="active" @endif>Strava</a></li>
                </ul>
            </li>

        </ul>
    </div>
</div>