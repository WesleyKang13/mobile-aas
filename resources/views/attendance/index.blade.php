@extends('layouts.app')
@section('title', 'Your Attendance On Week '.$week)

@section('content')
<div class="container">
    <div class="row">
        <?php
            $date = date('Y-m-d');
            if(request()->get('date') !== null){
                $date = request()->get('date');
            }
        ?>

        <div class="col-6">
            <h1>{{($date == null) ? date('Y-M-d D') : $date. ' '.date('D', strtotime($date))}}</h1>

        </div>


        <div class="col-6 text-end">
            <a href="/attendance?date={{date('Y-m-d', strtotime($date. '-1 day'))}}" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i></a>
            <span><b>View Ytd/Tmr</b></span>
            <a href="/attendance?date={{date('Y-m-d', strtotime($date. '+1 day'))}}" class="btn btn-secondary"><i class="fa-solid fa-arrow-right"></i></a>
        </div>
        @foreach($data as $d)
            <div class="col-md-12">
                <div class="card mb-5">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <b>Class Code: {{$d['class']}}</b>
                            </div>

                            <div class="col-6 text-end">
                                <b>Status:</b>
                                @if(isset($lecturer[$d['course_id']]) and $lecturer[$d['course_id']]['status'] == 'Open')
                                    <div class="badge bg-success">Open</div>
                                @else
                                    <div class="badge bg-danger">Close</div>
                                @endif
                            </div>
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <h5 class="card-title">Course: {{$d['course_name']}}</h5>
                            </div>

                            <div class="col-6 text-end">
                                <a href="/attendance/manual/{{$d['course_id']}}/{{$date}}" class="btn btn-primary">Manual Entry</a>
                            </div>
                        </div>
                        <p class="card-text">Time: {{$d['time']}}</p>
                        @if(Auth::user()->role == 'lecturer')
                            @if(isset($status[$d['course_id']]) and $status[$d['course_id']] == 'Open')
                                <a href="/user/{{$user->id}}/course/{{$d['course_id']}}/location?date={{$date}}" class="btn btn-success w-100 m-1" id="geolocation_{{$d['course_id']}}">Take Attendance</a>
                                <a href="/attendance/{{$lecturer[$d['course_id']]['id']}}/close?date={{$date}}" class="btn btn-danger w-100 m-1">Close</a>
                            @else
                                <a href="/user/{{$user->id}}/course/{{$d['course_id']}}/location?date={{$date}}" class="btn btn-danger w-100 m-1" id="geolocation_{{$d['course_id']}}">Take Attendance</a>
                            @endif

                            <a href="/attendance/{{$d['course_id']}}/{{$date}}" class="btn btn-primary w-100 m-1">Attendance Sheet</a>

                        @else
                            @if(isset($status[$d['course_id']]) and $status[$d['course_id']] == 'Successful')
                                <a href="/user/{{$user->id}}/course/{{$d['course_id']}}/location?date={{$date}}"
                                    class="btn btn-success w-100"
                                    id="geolocation_{{$d['course_id']}}">
                                    Submitted
                                </a>
                            @else
                                <a href="/user/{{$user->id}}/course/{{$d['course_id']}}/location?date={{$date}}"
                                    class="btn btn-danger w-100"
                                    id="geolocation_{{$d['course_id']}}">
                                    Submit
                                </a>
                            @endif
                        @endif

                    </div>
                </div>
            </div>

        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Options for geolocation
    const options = {
      enableHighAccuracy: true,
      timeout: 5000,
      maximumAge: 0,
    };

    // Success callback for geolocation
    function success(pos, courseId) {
      const crd = pos.coords;

      const lat = crd.latitude;
      const long = crd.longitude;

      // Get the geolocation button for the specific course
      const geolocationBtn = document.getElementById("geolocation_" + courseId);
      const accuracy = crd.accuracy;

      // Construct the URL with the course ID, latitude, and longitude using backticks (template literals)
      geolocationBtn.href = `/user/{{$user->id}}/course/${courseId}/location?lat=${lat}&long=${long}&accuracy=${accuracy}&date={{$date}}`;

      console.log(`Latitude: ${lat}, Longitude: ${long}`);
      console.log(`More or less ${crd.accuracy} meters.`);
    }

    // Error callback for geolocation
    function error(err) {
      console.warn(`ERROR(${err.code}): ${err.message}`);
    }

    // Event listener for each geolocation button in the loop
    document.querySelectorAll('[id^="geolocation_"]').forEach(function(button) {
      button.addEventListener("click", function(event) {
        event.preventDefault(); // Prevent the default action initially

        // Get the course ID from the button's ID
        const courseId = this.id.split("_")[1];

        // Trigger geolocation
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            success(position, courseId);

            // Once the href is updated with the lat/long, proceed with the navigation
            window.location.href = document.getElementById("geolocation_" + courseId).href;
          }, error, options);
        } else {
          console.log("Geolocation is not supported by your browser.");
        }
      });
    });
</script>
@endpush
