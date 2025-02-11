@extends('layouts.app')
@section('title', 'Advanced Attendance Sheet')

@section('content')
<div class="container shadow">
    <div class="row">
        <div class="col-8">
            <h1>{{$course->name}} - {{$date}} | Advanced View</h1>
        </div>

        <div class="col-4 text-end mt-2">
            <a href="/attendance/{{$course->id}}/{{$date}}" class="btn btn-secondary">Back</a>
        </div>

        <div class="col-12 d-flex">
            @if(isset($users))
                @foreach($users['all'] as $u)
                    @if(in_array($u, $users['yes']))

                        <div class="col text-center">
                            <h1 style="font-size:200px;"><i class="fa-solid fa-user text-success"></i></h1>
                        </br>
                            <h6>{{$u->firstname}}</h6>
                        </div>
                    @else
                        <div class="col text-center">
                            <h1 style="font-size:200px;"><i class="fa-solid fa-user text-secondary"></i></h1>
                        </br>
                            <h6>{{$u->firstname}}</h6>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</div>

@endsection
