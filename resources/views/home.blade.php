@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Queue') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div id="queue_container" class="d-flex flex-column">
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button id="btn_next" class="btn btn-success" onclick="nextQueue(this)">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var APP_URL = {!! json_encode(url('/')) !!}

    $(function () {
        getQueue()
    })

    // show queue list
    function getQueue() {
        $.get(APP_URL + '/api/admin/get-queue', function (data, status) {
            $('#queue_container').html('')
            if (data.status == 'success' && data.data.length > 0) {
                $.each(data.data, function(index, value) {
                    $('#queue_container').append(`
                        <button class="btn ${index == 0 ? 'btn-info current' : 'btn-secondary'} mt-2" data-id="${value['id']}">${value['no']}</button>
                    `)
                })

                nextButton()
            }
        })
    }

    function nextButton() {
        let id = $('.current').data('id')
        $('#btn_next').attr('data-id', id)
    }

    function nextQueue(element) {
        var id = element.getAttribute('data-id')

        $.ajaxSetup({   
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        $.ajax({
            url: APP_URL + '/api/admin/queue-complete',
            type: 'POST',
            data: {id:id},
            success: function(response) {
                if (response.status == 'success') {
                    toastr.success(response.message)
                    getQueue(response)
                } else {
                    toastr.error('Ralat! Hubungi Sistem Admin')
                }
            },
            error: function(error) {
                toastr.error('Ralat! Hubungi Sistem Admin')
            }
        });
    }

</script>
@endpush