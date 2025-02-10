@extends('layouts.app')
@section('title', 'Notification')

@section('content')
<div class="container-fluid">
    <div class="row mt-4 m-auto">
        <div class="col-6">
            @if(request()->get('status') !== null)
                <h1>{{ucfirst(request()->get('status'))}}</h1>
            @else
                <h1>Inbox</h1>
            @endif
            </div>

        <?php
            $status = request()->get('status');
        ?>

        <div class="col-6 text-end mt-2">
            <a href="/notifications/compose/{{$user->id}}/{{'draft'}}" class="btn btn-warning">Make Draft</a>
            <a href="/notifications/compose/{{$user->id}}/{{'new'}}" class="btn btn-primary">Compose</a>

            @if($status == 'unread' and $status !== null or $status == null)
                <a href="/notifications/readall" class="btn btn-danger">Mark All As Read</a>
            @endif
        </div>

        <div class="col-12">
            <table class="table table-hover table-striped w-100" id="notificationDT">
                <thead>
                    <tr>
                        @if(request()->get('status') !== 'sent')
                            <th>Sender</th>
                        @else
                            <th>Receiver</th>
                        @endif
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Subject</th>
                        <th>Attachment</th>
                        <th>Replies</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function(){
        $('#notificationDT').DataTable({
                // dom: "<'row'<'col-sm-12 col-md-6'><'col-sm-12 col-md-6'>>" +
                //     "<'row'<'col-sm-12'tr>>" +
                //     "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>",
                order: [[1, 'desc']],
                processing: true,
                serverSide: true,
                ajax: '{{Request::fullUrl()}}',
                pageLength: 50,
                columnDefs: [
                    // {className: 'dt-center', targets: [1, 3]},
                ],
                columns: [
                    {data: 'sender',},
                    {data: 'datetime',},
                    {data: 'status',},
                    {data: 'subject',},
                    {data: 'attachment',},
                    {data: 'replies', orderable: false, searchable: false,},
                    {data: 'action', orderable: false, searchable: false,},
                ],
            });
    });
</script>
@endpush
