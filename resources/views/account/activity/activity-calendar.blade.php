@extends('layouts.app') 
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
 .training-week {
    border-bottom: 1px solid #eee;
    padding: 30px 0;
}

.week-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.week-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 40px;
    text-align: center;
}

.activity-bubble {
    background: #fc4c02;
    border-radius: 50%;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: auto;
    font-size: 12px;
    font-weight: 600;
    transition: 0.2s ease;
}

.activity-bubble:hover {
    transform: scale(1.1);
}

.rest-day {
    color: #ccc;
    font-size: 13px;
}
.training-week {
    background: #fff;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
}

.week-grid {
    margin-top: 15px;
}

.day-box {
    width: 100%;
    background: #f8f9fa;
}

.day-distance {
    font-size: 14px;
}
 </style> 
 
 <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css"> 
<section id="hero" class="bg_gray py-6">
        <div class="container mt-12">
            <div class="row">
                <div class="col-lg-6 d-flex flex-column">
                    <p class="tc_primary fs-4">Activities</p>
                    <h1 class="mb-4">Activities</h1>
                </div>
            </div>
        </div>
</section>

<section id="activities" class="py-5 bg-white">
 
   <div class="container mt-4">
    <div id="weeks-container">
        <!-- Weeks will load here -->
    </div>

    <div id="loading" class="text-center my-4" >
   
    </div>

   </div>
   
</section>
<!-- Modal -->


@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>


let lastDate = null;
let loading = false;

function loadMore() {

    if (loading) return;
    loading = true;

    $.ajax({
        url: '/activity-log-more',
        type: 'GET',
        data: {
            last_date: lastDate
        },
        success: function(response) {

            if (response.trim() === '') {
                $(window).off('scroll');
                return;
            }

            $('#weeks-container').append(response);

           
            lastDate = $('.training-week:last').data('last-date');

            loading = false;
        }
    });
}

// Initial load
loadMore();

// On scroll
$(window).scroll(function () {
    if ($(window).scrollTop() + $(window).height() >= $(document).height() - 200) {
      loadMore();
    }
});
</script>

@endsection
