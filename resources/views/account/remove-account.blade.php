@extends('layouts.account-app')
@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .user-stats div {
        font-size: 14px;
        margin-bottom: 6px;
    }

    /* Suggestion */
    .info-note {
        background: rgba(255, 124, 30, 0.1);
        padding: 10px;
        border-radius: 8px;
        font-size: 13px;
    }

    .reason-options {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
    }

    .reason-card {
        border: 1px solid #d0d4d9;
        border-radius: 10px;
        padding: 12px;
        cursor: pointer;
    }

    .reason-card input {
        display: none;
    }

    .reason-card:hover {
        border-color: #fc5200;
    }

    .reason-card input:checked+div {
        color: #FFFFFF;
        font-weight: 600;
    }

    .reason-card:has(input[type="radio"]:checked) {
        background-color: #fc5200;
    }

    /* Modal */
    /* Icon */
    .modal-icon {
        font-size: 34px;
        margin-bottom: 10px;
    }

    /* Title */
    .modal-title {
        font-weight: 600;
        color: #0c2957;
    }

    /* Text */
    .modal-text {
        font-size: 14px;
        color: #666;
        margin-top: 8px;
    }

    /* Note */
    .modal-note {
        background: #f1f3f6;
        padding: 10px;
        border-radius: 8px;
        font-size: 13px;
        margin-top: 14px;
    }
</style>
<div class="card-box mb-4">
    <h5 class="mb-1">Remove Account</h5>
    <small class="text-muted">Confirm remove account</small>

    <div class="row mt-3">
        <div class="col-md-4">
            <div class="delete-info-card bg-light p-3 h-100">

                <h5 class="fw-semibold mb-4">⚠️ Before you go...</h5>


                <!-- Dynamic Message -->
                <p class="info-text" id="dynamicMessage">
                    We’re sorry to see you go. Deleting your account will permanently remove your activities, Zipcode progress, and all associated data.
                </p>

                <p class="info-subtext">
                    This action cannot be undone. If something isn’t working as expected, your feedback helps us improve.
                </p>

                <!-- Progress -->
                <div class="progress-section mt-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Your ZIP Progress</span>
                        <span id="zipPercentText">{{number_format($summary['completdPercentage'],2)}}%</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress" role="progressbar" aria-label="Success example" aria-valuenow="{{$summary['completdPercentage']}}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-success" style="width: {{$summary['completdPercentage']}}%"></div>
                        </div>
                    </div>
                </div>
                <!-- Stats -->
                <div class="user-stats mt-3">
                    <div><strong id="zipCount">{{$summary['totalPassedZips']}}</strong> ZIP codes explored</div>
                    <div><strong id="activityCount">{{$summary['totalActivities']}}</strong> activities completed</div>
                    <div><strong id="distanceCount">{{number_format($summary['totalDistanceMiles'],2)}}</strong> mi total distance</div>
                </div>

                <!-- Smart suggestion -->
                 @if(auth('athlete')->user()->status==1)
                <div id="smartSuggestion" class="info-note mt-3">
                    💡 You can pause your account instead of deleting it.
                </div>

                <!-- CTA -->
                <a class="btn btn-strava btn-sm  w-100 mt-3" data-toggle="modal" data-target="#pause-data-modal" id="pause-account">
                    Pause account instead
                </a>
                @else
                <div id="smartSuggestion" class="info-note mt-3">
                    💡 You have paused your account.
                </div>

                @endif
            </div>
        </div>
        <div class="col-md-8">

            <!-- <p>
                We're sorry to see you go. Deleting your account will permanently remove all your data,
                including your activities and ZIP code progress. This action cannot be undone.
            </p>

            <p>
                If you’re facing any issues, feel free to share your feedback below — it helps us improve.
            </p> -->
            <form action="{{ route('account.delete-account') }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete your account? This action is permanent.');" id="delete-data-form">
                @csrf

                <!-- Reason -->
                <div class="mb-3">
                    <label class="form-label">Reason for leaving</label>
                    <div class="reason-options mt-0">
                        <label class="reason-card">
                            <input type="radio" name="reason" value="not_useful">
                            <div>🤔 Not useful</div>
                        </label>

                        <label class="reason-card">
                            <input type="radio" name="reason" value="technical_issues">
                            <div>⚠️ Facing technical issues</div>
                        </label>

                        <label class="reason-card">
                            <input type="radio" name="reason" value="privacy_concerns">
                            <div>🔒 Privacy concerns</div>
                        </label>

                        <label class="reason-card">
                            <input type="radio" name="reason" value="found_alternative">
                            <div>🔁 Found an alternative</div>
                        </label>

                        <label class="reason-card">
                            <input type="radio" name="reason" value="temporary_break">
                            <div>😌 Taking a break</div>
                        </label>

                        <label class="reason-card">
                            <input type="radio" name="reason" value="other">
                            <div>✍️ Other</div>
                        </label>

                    </div>
                    <div id="reason-error" class="text-danger mt-2" style="display:none;"></div>
                    <!-- <select name="reason" id="reason" class="form-control" required>
                        <option value="">-- Select a reason --</option>
                        <option value="not_useful">Not useful</option>
                        <option value="technical_issues">Facing technical issues</option>
                        <option value="privacy_concerns">Privacy concerns</option>
                        <option value="found_alternative">Found an alternative</option>
                        <option value="temporary_break">Taking a break</option>
                        <option value="other">Other</option>
                    </select> -->
                </div>

                <!-- Other Reason -->
                <div class="mb-3 d-none" id="other-box">
                    <label class="form-label">Enter your reason</label>
                    <input type="text" name="other_reason" class="form-control">
                </div>

                <!-- Comments -->
                <div class="mb-3">
                    <label class="form-label">Comments</label>
                    <textarea name="comments" rows="3" class="form-control"></textarea>
                </div>

                <!-- Feedback -->
                <div class="mb-3">
                    <label class="form-label">Help us improve</label>
                    <textarea name="feedback" rows="4" class="form-control"
                        placeholder="Tell us what we could do better..."></textarea>
                </div>

                <div class="">
                    <div class="row">
                        <div class="col-12 text-end">
                            <a class="btn btn-strava btn-sm" data-toggle="modal" data-target="#delete-data-modal" id="remove-data-modal">
                                Delete My Account
                            </a>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
<div id="pause-data-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form action="{{ route('account.pause-account') }}" method="POST" id="pause-data-form">
        @csrf
        <input type="hidden" name="_method" value="post">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="pause_confirm" value="pause_confirm">
       

        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">Pause Athlete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body text-center">
                <!-- Icon -->
                <div class="modal-icon">⚠️</div>

                <p class="modal-text">
                    Are you sure you want to pause your account?
                </p>

                <small class="text-muted">
                    You can reactivate your account anytime by contacting our support team.
                </small>
               
            </div>
            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" id="confirm-pause-btn" class="btn btn-danger">
                    Pause
                </button>
            </div>

        </div>
      </form>
    </div>
</div>
<div id="delete-data-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">

        <input type="hidden" name="_method" value="post">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">Remove Athlete Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body text-center">
                <!-- Icon -->
                <div class="modal-icon">⚠️</div>

                <!-- Title -->
                <h5 class="modal-title">Remove your data?</h5>

                <!-- Message -->
                <p class="modal-text">
                    This will permanently remove your activities, ZIP progress, and all associated data.
                </p>

                <!-- Retention Note -->
                <div class="modal-note">
                    Your data will be retained for up to <strong>30 days</strong> before permanent deletion.
                </div>

                <!-- Confirmation input -->
                <input
                    id="confirmDeleteInput"
                    class="form-control mt-3"
                    name="delete_confirm"
                    placeholder="Type DELETE to confirm">
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" id="confirm-delete-btn" class="btn btn-danger">
                    Remove
                </button>
            </div>

        </div>

    </div>
</div>

@endsection

@section('script')
<script src="{{ url("front/js/sweet-alert.js")}}"></script>
<script>
    $(document).ready(function() {
        $('input[name="reason"]').on('change', function() {
            if ($(this).val() === 'other') {
                $('#other-box').removeClass('d-none');
            } else {
                $('#other-box').addClass('d-none');
            }
             $('#reason-error').hide();
        });
    });
    $(function() {

        var deleteLogModal = $('#delete-data-modal'),
            deleteLogForm = $('#delete-data-form'),
            submitBtn = deleteLogForm.find('button[type=submit]');

        // Open modal
        $("#remove-data-modal").on('click', function(event) {
            console.log('here');
            event.preventDefault();
            $('.error-text').remove();
            $('input').removeClass('is-invalid');

            let isValid = true;
            let reason = $('input[name="reason"]:checked').val();
            let otherReason = $('input[name="other_reason"]').val() || '';
            let comments = $('textarea[name="comments"]').val() || '';
           
            if (!reason) {
                isValid = false;
                if (!reason) {
                        $('#reason-error').text('Please select a reason for leaving.').show();
                        return false;
                    } else {
                        $('#reason-error').hide();
                }
            }

            if (reason === 'other' && otherReason.trim() === '') {
                isValid = false;
                $('input[name="other_reason"]').addClass('is-invalid')
                    .after('<small class="text-danger error-text">Please specify other reason</small>');
            }

            if (!comments) {
                isValid = false;
                $('textarea[name="comments"]').addClass('is-invalid')
                    .after('<small class="text-danger error-text">Please add comments</small>');
            }
            
       
            if (!isValid) {
                deleteLogModal.modal('hide');
                return;
            } else {
                deleteLogModal.modal('show');
            }

        });

        $('#confirm-delete-btn').on('click', function() {

            // Validate
            $('.error-text').remove();
            $('select, input').removeClass('is-invalid');
            let comments = $('textarea[name="comments"]').val() || '';

            let isValid = true;
            let reason = $('input[name="reason"]:checked').val();
            let otherReason = $('input[name="other_reason"]').val() || '';
            let input = $('#confirmDeleteInput').val().trim();
            if (!reason) {
                isValid = false;
               if (!reason) {
                        $('#reason-error').text('Please select a reason for leaving.').show();
                        return false;
                    } else {
                        $('#reason-error').hide();
                }
            }

            if (reason === 'other' && otherReason.trim() === '') {
                isValid = false;
                $('input[name="other_reason"]').addClass('is-invalid')
                    .after('<small class="text-danger error-text">Please specify other reason</small>');
            }
            if (!comments) {
                isValid = false;
                $('textarea[name="comments"]').addClass('is-invalid')
                    .after('<small class="text-danger error-text">Please add comments</small>');
            }
             if (input !== 'DELETE') {
                isValid = false;
                $('input[name="delete_confirm"]').addClass('is-invalid')
                    .after('<small class="text-danger error-text">Type DELETE to confirm</small>');
                return;
            }
            if (!isValid) {
                deleteLogModal.modal('hide');
                return;
            }
                  
           
            // Submit AJAX
            submitBtn.prop('disabled', true);
            let formData = deleteLogForm.serialize();
            let deleteConfirm = $('#confirmDeleteInput').val();
            formData += '&delete_confirm=' + encodeURIComponent(deleteConfirm);
            $.ajax({
                url: deleteLogForm.attr('action'),
                type: deleteLogForm.attr('method'),
                data: formData,
                
                success: function(data) {
                    submitBtn.prop('disabled', false);

                    if (data.result === 'success') {
                        deleteLogModal.modal('hide');
                        swal("Success", "Account deleted successfully", "success");
                        location.reload();
                    } else {
                        deleteLogModal.modal('hide');

                    }
                    deleteLogModal.modal('hide');
                },

                error: function(xhr) {
                    submitBtn.prop('disabled', false);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;

                        $.each(errors, function(field, messages) {
                            let input = $('[name="' + field + '"]');

                            input.addClass('is-invalid');

                            input.next('.error-text').remove();

                            input.after(
                                '<small class="text-danger error-text">' + messages[0] + '</small>'
                            );
                        });
                        deleteLogModal.modal('hide');

                    } else {
                        console.error(xhr.responseText);
                    }

                }
            });

        });

        $('#pause-account').on('click', function(event) {
            event.preventDefault();
               $('#pause-data-modal').modal('show');
        });
        submitPuseBtn = $('#pause-data-form').find('button[type=submit]');
         $('#confirm-pause-btn').on('click', function() {
            
             $.ajax({
                url: $('#pause-data-form').attr('action'),
                type:$('#pause-data-form').attr('method'),
                data:$('#pause-data-form').serialize(),
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                success: function(data) {
                    submitPuseBtn.prop('disabled', false);
                    if (data.result === 'success') {
                         $('#pause-data-modal').modal('hide');
                        swal("Success", "Account paused successfully", "success");
                        location.reload();
                    } else {
                        $('#pause-data-modal').modal('hide');

                    }
                     $('#pause-data-modal').modal('hide');
                },

                error: function(xhr) {
                    submitPuseBtn.prop('disabled', false);
                }
            });

          });
    });
</script>

@endsection