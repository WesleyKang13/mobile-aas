@extends('layouts.app')
@section('title', 'Your Attendance On Week '.$week)

@section('content')
<div class="container">
    <div class="row">
        @foreach($data as $d)
            <h1>{{$d['class']}}</h1>
            <h1>{{$d['course_name']}}</h1>
            <h1>{{$d['time']}}</h1>
        @endforeach
    </div>
</div>
@endsection
