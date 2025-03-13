@extends('layouts.app')
@section('title', 'Attenddance Sheet')

@section('content')
<div class="container shadow">
    <div class="row">
        <div class="col-8">
            <h1>{{$course->name}} - {{date('Y-m-d')}}</h1>
        </div>

        <div class="col-4 text-end">
            
            <a href="/attendance/{{$course->id}}/{{date('Y-m-d')}}/advanced" class="btn btn-primary">Advanced View</a>
            <a href="/attendance" class="btn btn-secondary">Back</a>
        </div>

        <div class="col-12">
            <table class="table table-striped">
                <tr>
                    <th>Names</th>
                    <th>Time Checked In</th>
                    <th>Distance</th>
                    <th>Remarks</th>
                </tr>

                @foreach($users as $u)
                    <tr>
                        <td>{{$u['username']}}</td>
                        <td>{{$u['time']}}</td>

                        @if($u['role'] == 'student' and $u['remarks'] == null)
                            <td>{{number_format($u['distance'])}} meters</td>

                        @elseif($u['role'] == 'lecturer')
                            <td>Lecturer</td>
                        @else
                            <td>

                            </td>
                        @endif

                        <td>{{$u['remarks']}}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection
