@extends('admin.layouts.app')
@section('page-title', 'Users')
@section('head')
<link rel="stylesheet" href="{{ url('libs/dataTable/datatables.min.css') }}" type="text/css">

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
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                </ol>
        </nav>
    </div>
    <div class="content">
        <div class="">
                <div class="card">
                    <div class="card-body">
                        <div class="d-md-flex gap-4 align-items-center">
                            <div class="d-none d-md-flex">All Users</div>
                            <div class="d-md-flex gap-4 align-items-center">
                                <form class="mb-3 mb-md-0">
                                    <div class="row g-3">
                                        <div class="row g-3">
                                            <div class="col-md-7">
                                                <select class="form-select" id="sort">
                                                    <option>Sort by</option>
                                                    <option data-sort="asc" data-column="1" value="">Name A-z</option>
                                                    <option data-sort="desc" data-column="1" value=""> Name Z-a
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <select class="form-select" id="pageLength">
                                                    <option value="10">10</option>
                                                    <option value="20">20</option>
                                                    <option value="30">30</option>
                                                    <option value="40">40</option>
                                                    <option value="50">50</option>
                                                </select>
                                            </div>
                                           
                                        </div>
                                    </div>
                                </form>
                            </div>
                         
                            <div class="dropdown ms-auto">
                                <a href="{{route('admin.user.create')}}">
                                    <button class="btn btn-primary btn-icon">
                                        <i class="bi bi-plus-circle"></i> Add Users
                                    </button>
                                </a>
                            </div>
                           
                        </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom table-lg mb-0 " id="userstb">
                                <thead>
                                    <tr>
                                        <th>User Name</th>  
                                        <th>Email</th>    
                                        <th>Status</th> 
                                       
                                        <th>Action</th>
                                     
                                    </tr>
                                </thead>
                        </table>
                    </div>
                </div>
            </div>
        <div class="modal fade" id="delete-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form>
                <div class="modal-body">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" id="deleteId" name="deleteId">
                            <p>Are you sure you want to delete this User</p>
                            <div class="modal-footer">
                            
                                <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-sm btn-danger btn_delete_user" data-loading-text="">Delete</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>   
    </div> 
@endsection
@section('script')
<!-- Range slider -->
<script src="{{ url('libs/dataTable/datatables.min.js') }}"></script>
<script>
@if(Session::has('success_message'))
$(document).ready( function () {
    Swal.fire({
        icon: 'success',
        title: '{{ Session::get('success_message') }}',
      
        footer: ''
    })
})
@endif
    $(document).ready(function() {
    var $column = $('#sort').find(':selected').data('column');
    var $sort = $('#sort').find(':selected').data('sort');
    $userstable= $('#userstb').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.user.index') }}',
            data: function(d) {
                
            }
        },

        columns: [
           
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'email',
                name: 'email'
            },
         
            {
                data: 'status',
                name: 'status'
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
    $("#userstb_filter").css({
        "display": "none"
    });
    $("#userstb_length").css({
        "display": "none"
    });
    $('#sort').on('change', function() {
        console.log('sort change');
        $column = $(this).find(':selected').data('column');
        $sort = $(this).find(':selected').data('sort');

        $userstable.order([$column, $sort]).draw();
    });

    $('#pageLength').on('change', function() {
        $userstable.page.len($(this).val()).draw();
    });
   $('#pageLength').val($userstable.page.len());

   $(document).on("keyup", ".searchInput", function(e) {
        $userstable.search($(this).val()).draw();
    });

});
$('table').off('click').on('click','.delete-user',function(){
    var href=$(this).data('href');
    $('.btn_delete_user').click(function(){
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}, 
            type: 'DELETE',
            dataType : 'JSON',
            url : href,
            success:function(response){
                $('#delete-modal').modal('hide');
                $('#userstb').DataTable().ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'User deleted successfully',
                    footer: ''
                })
            }  
        })
    })

})
</script>
@endsection