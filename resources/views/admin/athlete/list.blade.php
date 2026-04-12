@extends('admin.layouts.app')

@section('page-title', 'Athlete')

@section('head')
<link rel="stylesheet" href="{{ url('libs/dataTable/datatables.min.css') }}" type="text/css">
<link rel="stylesheet" href="{{ url('libs/range-slider/css/ion.rangeSlider.min.css') }}" type="text/css">
<style>
    .table-responsive {
        overflow-x: scroll !important;
    }

    .table-responsive::-webkit-scrollbar {
        margin-top: 20px;
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #e3e3e3;
        border-radius: 20px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background-color: #d02027;
        border-radius: 20px;
    }
</style>
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
            <li class="breadcrumb-item active" aria-current="page">Strava Athletes</li>
        </ol>
    </nav>
</div>

<div class="content">
    <div class="">
        <div class="card">
            <div class="card-body">
                <div class="d-md-flex gap-4 align-items-center">
                    <div class="d-none d-md-flex">All Athletes</div>
                    <div class="d-md-flex gap-4 align-items-center">
                        <form class="mb-3 mb-md-0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <select class="form-select" id="sort">
                                        <option>Sort by</option>
                                        <option data-sort="asc" data-column="1" value="">Name A-z</option>
                                        <option data-sort="desc" data-column="1" value=""> Name Z-a
                                        </option>
                                    </select>
                                </div>
                              
                            </div>
                        </form>
                    </div>
                    
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-custom table-lg mb-0" id="registrations">
                <thead>
                    <tr>
                       <th></th>
                       <th>Name</th>
                       <th>Sex</th>
                       <th>City</th>
                       <th>State</th>
                       <th>Country</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Delete Events</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form>
                <div class="modal-body">
                    <!-- <input type="hidden" name="_method" value="DELETE"> -->
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" id="deleteId" name="deleteId">
                    <p>Are you sure you want to delete this Events</p>
                    <div class="modal-footer">

                        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-sm btn-danger btn_delete_evant" data-loading-text="">Delete</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="add-event-images-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <input type="hidden" id="eventId" name="eventId">
            <div class="modal-body">
                <pre id="addeventimages"></pre>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ url('libs/dataTable/datatables.min.js') }}"></script>
<script src="{{ url('libs/range-slider/js/ion.rangeSlider.min.js') }}"></script>

<script type="text/javascript">
    @if(Session::has('success_message'))
    $(document).ready(function() {
        Swal.fire({
            icon: 'success',
            title: '{{ Session::get("success_message") }}',
            footer: ''
        })
    })
    @endif
        @if(Session::has('failure_message'))
    $(document).ready(function() {
        Swal.fire({
            icon: 'error',
            title: '{{ Session::get("failure_message") }}',
            footer: ''
        })
    })
    @endif
    $(document).ready(function() {
        var $column = $('#sort').find(':selected').data('column');
        var $sort = $('#sort').find(':selected').data('sort');
        $registrationTable = $('#registrations').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.athlete.index") }}',
                data: function(d) {

                }
            },

            columns: [
                {
                    data: 'profile',
                    name: 'profile'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'sex',
                    name: 'sex'
                },
                {
                    data: 'city',
                    name: 'city'
                },
                        {
                    data: 'state',
                    name: 'state'
                },
                        {
                    data: 'country',
                    name: 'country'
                },
                {
                    data: 'action',
                    name: 'action'
                }
            ],
            columnDefs: [{
                'defaultContent': '--',
                "targets": "_all"
            }],
        });

        $(document).on("keyup", ".searchInput", function(e) {
            $registrationTable.search($(this).val()).draw();
        });
        $('#pageLength').on('change', function() {
            $registrationTable.page.len($(this).val()).draw();
        });
        $('#pageLength').val($registrationTable.page.len());

        $("#registrations_filter").css({
            "display": "none"
        });
        $("#registrations_length").css({
            "display": "none"
        });

        $('.filter').change(function() {
            $registrationTable.draw();
        });

        $('#sort').on('change', function() {
            $column = $(this).find(':selected').data('column');
            $sort = $(this).find(':selected').data('sort');
            $registrationTable.order([$column, $sort]).draw();
        })


    });
    $('table').off('click').on('click', '.delete-event', function(e) {
        var href = $(this).data('href');
        $('.btn_delete_evant').click(function() {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'DELETE',
                dataType: 'JSON',
                url: href,
                success: function(response) {
                    $('#delete-modal').modal('hide');
                    $('#registrations').DataTable().ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Event deleted successfully',
                        footer: ''
                    })
                }
            });
        });
    });

    $(document).on('click', '.view-details', function() {
        var id = $(this).data('id');
        var url = $(this).data('href');
        $.ajax({
            type: 'GET',
            data: {
            event_id: id
            },
            dataType: 'html',
            url:url,
            success: function (response) {
                $('#add-event-images-modal .modal-body').html(response);
                $('#add-event-images-modal').modal('show');
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });
</script>
@endsection