@extends('layouts.app')
@section('title', $notification->subject)

@section('content')
<div class="container shadow">
    <div class="row bg-white mt-5">
        <div class="col-8">
            <h1>Notification from {{$notification->sender}}
            </h1>
        </div>

        {{-- Do proper redirection --}}
        <div class="col-4 text-end mt-2">
            <a href="/notifications" class="btn btn-secondary">Back</a>
        </div>

        <div class="col-12 pb-4">
            {{-- If there are replies on this notification --}}
            <?php
                // $existing_subject = '';
                // $count = 0;
            ?>
            @if($notifications !== [])
                @foreach($notifications as $n)
                    <div class="card" style="width: 100%;">
                        <div class="card-body">
                            <h5 class="card-title"><b>Subject:</b> {{$n->subject}}</br>
                                {{-- @if($count > 0 && $existing_subject !== '')
                                    <b>Replying To Subject: {{$existing_subject}}</b>
                                @endif --}}
                            </h5>
                            <h6 class="card-subtitle mb-2 text-muted">{{$n->sender}} - Sent on {{$n->datetime}}</h6>
                            <p class="card-text"><hr>{!!nl2br($n->details)!!}</p>
                            @if($n->attachment !== null)
                                <p><b>Attachment:</b> <a href="/notifications/{{$n->id}}/download_attachment"><b>Download</b></a></p>
                            @endif
                            @if($n->status !== 'read' and $n->receiver == Auth::user()->email)
                                <a href="/notifications/status/{{$n->id}}" class="btn btn-danger">Mark As Read</a>
                            @endif
                            <a href="/notifications/{{$n->id}}/reply" class="btn btn-primary" id="reply_{{$n->id}}">Reply</a>
                        </div>
                    </div>
                    <hr>
                    <?php
                        // $existing_subject = $n->subject;
                        // $count++;
                    ?>
                @endforeach
                <div class="col-12 text-center text-muted">
                    <span>Latest message is at the top</span>
                </div>
            @else
                <div class="card" style="width: 100%;">
                    <div class="card-body">
                    <h5 class="card-title"><b>Subject:</b> {{$notification->subject}}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">{{$notification->sender}} - Sent on {{$notification->datetime}}</h6>
                    <p class="card-text"><hr>{!!nl2br($notification->details)!!}</p>
                    @if($notification->attachment !== null)
                        <p><b>Attachment:</b> <a href="/notifications/{{$notification->id}}/download_attachment"><b>Download</b></a></p>
                    @endif
                    <p><b>Attachment:</b> <a href="/notifications/{{$n->id}}/download_attachment"><b>Download</b></a></p>
                    @if($notification->receiver == Auth::user()->email)
                        @if($notification->status !== 'read')
                                <a href="/notifications/status/{{$notification->id}}" class="btn btn-danger">Mark As Read</a>

                        @endif
                            <a href="/notifications/{{$notification->id}}/reply" class="btn btn-primary" id="reply_{{$notification->id}}">Reply</a>
                    @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="reply_form">
            {!!FB::open('/notifications/'.$notification->id.'/reply', 'post', ['enctype' => 'multipart/form-data'])!!}
            {!!FB::setErrors($errors)!!}
            @csrf

            <div class="col-12">
                {!!FB::input('subject', 'Subject')!!}</br>
                {!!FB::textarea('details', 'Details')!!}</br>
                {!!FB::file('attachment', 'Attach File *(If Needed)*')!!}</br>
            </div>

            <div class="col-12 text-end pb-2">
                {!!FB::submit('Send', [], true)!!}
            </div>

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // hide the reply form
        const replyForm = document.querySelector('.reply_form');
        replyForm.style.display = 'none';

        // event listeners to all reply buttons
        const replyButtons = document.querySelectorAll('[id^="reply_"]');
        replyButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();

                // the id from the button's ID attribute
                const notificationId = button.id.split('_')[1];

                // the form's action URL dynamically
                const form = document.querySelector('.reply_form form');
                form.action = `/notifications/${notificationId}/reply`;

                // show the reply form
                replyForm.style.display = 'block';
            });
        });
    });

</script>
@endpush
