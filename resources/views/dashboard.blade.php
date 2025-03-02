@extends('layouts.app')
@section('title', 'Your Dashboard')

@section('content')
<div class="container shadow mb-5">
    <div class="row">
        <div class="col-12">
            <h1 class="fs-3">Hi {{ucfirst($user->firstname). ' ' .ucfirst($user->lastname)}}</h1>
        </div>

        <div class="col-12">
            <h1 class="fs-3">Attendance(s) to submit {{date('Y-m-d')}}</h1>
        </div>

        <div class="col-12">
            <table class="table table-striped">
                <tr>
                    <th>Course Name</th>
                    <th>Classroom</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>

                @if(count($data) !== 0)
                    @foreach($data as $d)
                        <tr>
                            <td>{{$d['course_name']}}</td>
                            <td>{{$d['class_code']}}</td>
                            <td>{{$d['time']}}</td>
                            <td>
                                <div  class="{{($d['status'] == 'No') ? 'badge bg-danger' : 'badge bg-success'}}">
                                    {{$d['status']}}
                                </div>
                            </td>

                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" class="text-center"><span class="text-muted text-center">No Courses For Today :)</span></td>
                    </tr>
                @endif
            </table>
        </div>


        <div class="col-12">
            <h1 class="fs-3">Attendance Rate</h1>
        </div>

        {{-- Attendance Rate Donut Charts for Each Course --}}
        <div class="col-12">
            <table class="table table-striped">
                <tr>
                    <th style="width:80%;">Course Name</th>
                    <th style="width:20%;">Attendance Rating</th>
                </tr>

                @foreach($attendanceData as $id => $attendance)
                <tr>
                    <td style="width:80%;">{{$attendance['course_name']}}</td>
                    <td style="width:20%;">
                        <canvas id="attendanceDonutChart-{{$id}}" style="height:150px;width:150px;"></canvas>
                    </td>
                </tr>
                @endforeach

            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    @foreach($attendanceData as $id => $attendance)
    var ctx{{ $id }} = document.getElementById('attendanceDonutChart-{{$id}}').getContext('2d');
    var attendanceDonutChart{{ $id }} = new Chart(ctx{{ $id }}, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent'],
            datasets: [{
                data: [{{ $attendance['present'] }}, {{ $attendance['absent'] }}], // Attendance data for the specific course
                backgroundColor: ['#4caf50', '#f44336'], // Colors for Present (green) and Absent (red)
                hoverOffset: 4
            }]
        },
        options: {
            responsive: false, // Disable responsiveness to allow size customization
            maintainAspectRatio: false, // Allows the chart to fit the canvas size
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    enabled: true
                },
                datalabels: {
                    color: '#fff', // White text for better visibility
                    formatter: function(value, context) {
                        if (context.dataIndex === 0) {
                            // Display the present percentage in the center of the chart
                            return '{{ round($attendance["present"], 2) }}%';
                        } else {
                            return '';
                        }
                    },
                    font: {
                        size: 14,
                        weight: 'bold'
                    },
                    align: 'center',
                    anchor: 'center'
                }
            }
        },
        plugins: [ChartDataLabels]
    });
    @endforeach
</script>
@endpush
