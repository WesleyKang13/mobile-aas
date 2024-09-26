@extends('layouts.app')
@section('title', 'Your Dashboard')

@section('content')
<div class="container shadow">
    <div class="row">
        <div class="col-12">
            <h1>{{$user->firstname. ' ' .Auth::user()->lastname}}</h1>
        </div>

        @foreach($courses as $course)
            <div class="card">
                <div class="card-title">
                    Course Name{{$course->name}}
                </div>

                <div class="content">
                    <h1>Course Year:{{$course->year}}</h1></b>
                    Course Code:{{$course->code}}
                </div>

            </div>
        @endforeach
    </div>
</div>
@endsection
