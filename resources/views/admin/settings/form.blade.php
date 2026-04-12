@extends('admin.layouts.app')
@section('page-title', 'Settings')

@section('header-action-button')

    
@endsection

@section('content')
    
    <div class="mb-4">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-globe2 small me-2"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Systems</li>
                <li class="breadcrumb-item active" aria-current="page">Settings</li>
            </ol>
        </nav>
    </div>

    <div class="row flex-column-reverse flex-md-row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title mb-4"> Settings</h6>
                    <form id="settings-form" method="POST" action="{{ route('admin.settings.store') }}">
                        @csrf
                        <div class="row">
                          
                            @foreach ($settings as  $setting)
                                @if($setting->field_type=='hidden')
                                <input type="{{ $setting->field_type }}" value="{{ $setting->value }}" name="{{ $setting->code }}" class="form-control input">
                                 @else
                                 <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ $setting->title }}</label>
                                        @if($setting->field_type=='select')
                                            <select name="{{ $setting->code }}" class="form-select">
                                                @foreach(json_decode($setting->dataset) as $dataset)
                                                <option value="{{ $dataset->id }}" {{ ($dataset->id==$setting->value) ? 'selected' : '' }}>{{ $dataset->name }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($setting->field_type=='textarea')
                                            <textarea class="form-control" rows="4" name="{{ $setting->code }}">{{ $setting->value }}</textarea>
                                        @else
                                            <input type="{{ $setting->field_type }}" name="{{ $setting->code }}" class="form-control"
                                            value="{{ $setting->value }}" placeholder="{{ $setting->placeholder }}" id="{{$setting->code}}">
                                        @endif
                                    </div>
                                </div>
                                @endif
                            @endforeach
                            @if($platform == 'strava')
                                <!-- <div class="col-md-6">
                                    <div class="mt-4 d-flex justify-content-start gap-5">
                                        <button type="button" id="create-webhook" class="btn btn-success">
                                            Create Webhook
                                        </button>
                                        <button type="button" id="delete-webhook" class="btn btn-danger">
                                            Delete Webhook
                                        </button>
                                    </div>
                                </div> -->
                            @endif
                            <div class="col-md-12">
                               
                                <div class="mb-3">
                                        <button type="submit" class="btn btn-primary mb-3">Save</button>
                                    </div>
                               
                            </div>                            
                            
                        </div>
                    </form>
                </div>
            </div>
           
        </div>
    </div>

@endsection
@section('script')
    <script>
    @if(Session::has('success_message'))
        $(document).ready( function () {
            Swal.fire({
                icon: 'success',
                title: '{{ Session::get('success_message') }}',
                // text: 'we will notify through email once the adjustment is complete',
                footer: ''
            })
        })
    @endif
    @if(Session::has('failure_message'))
        $(document).ready( function () {
            Swal.fire({
                icon: 'error',
                title: '{{ Session::get('failure_message') }}',
                // text: 'we will notify through email once the adjustment is complete',
                footer: ''
            })
        })
    @endif

    </script>

<script>
    $(document).ready(function () {
        const isAuthorized = @json($isAuthorized ?? false);

        function toggleButtons(isAuth) {
            if (isAuth) {
                $('#authorize-strava-btn').hide();
                $('#save-btn').prop('disabled', false).removeAttr('title');
            } else {
                $('#authorize-strava-btn').show();
                $('#save-btn').prop('disabled', true).attr('title', 'Please authorize Strava first');
            }
        }

        toggleButtons(isAuthorized);

        $('#client_id, #client_secret').on('input', function () {
            toggleButtons(false);
        });
    });
   $('#authorize-strava-btn').on('click', function (e) {
        e.preventDefault();

        const clientId = $('input[name="client_id"]').val();
        const clientSecret = $('input[name="client_secret"]').val();

        $.ajax({
            url: "{{ route('admin.authorize') }}",
            method: 'POST',
            data: {
                client_id: clientId,
                client_secret: clientSecret,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.redirect_url) {
                    window.location.href = response.redirect_url;
                } else {
                    alert('No redirect URL returned.');
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Authorization failed. Check console for details.');
            }
        });
    });
    $('#create-webhook').on('click', function () {
        $.ajax({
            url: "{{ route('admin.strava.createWebhook') }}",
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                Swal.fire('Success', res.message, 'success').then(() => {
                    location.reload();
                });
            },
            error: function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message ?? 'Failed', 'error');
            }
        });
    });

    $('#delete-webhook').on('click', function () {
        $.ajax({
            url: "{{ route('admin.strava.deleteWebhook') }}",
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                Swal.fire('Deleted', res.message, 'success').then(() => {
                    location.reload();
                });
            },
            error: function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message ?? 'Failed', 'error');
            }
        });
    });

</script>    

@endsection