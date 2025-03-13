@extends('layouts.app')
@section('title', 'Manual Entry')

@section('content')
<div class="container shadow">
    {!!FB::open('/attendance/manual/'.$course->id.'/'.$date, 'POST')!!}
    {!!FB::setErrors($errors)!!}

    @csrf
    <div class="row mt-4">
        <div class="col-6">
            <h1>Manual Entry</h1>
            <span class="text-muted">It will only show the students that are taking this course</span>
        </div>

        <div class="col-6 text-end mt-2">
            <a href="/attendance/{{$course->id}}/{{$date}}" class="btn btn-secondary">Back</a>
            {!!FB::submit('Save', [], true)!!}
        </div>

        <div class="col-12">
            {!!FB::select('user_id', 'User', $users)!!}</br>
        </div>
    </div>
    {!!FB::close()!!}
</div>
@endsection
