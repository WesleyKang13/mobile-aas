<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Attendance;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Models\UserTimetable;
use Illuminate\Support\Facades\Auth;
use DateTime;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller{
    public function index(){
        // get current user
        $user = User::findOrFail(Auth::user()->id);

        $today = now();
        $date = new DateTime($today);
        $week = $date->format('W');

        $data = $user->timetable($user->id);

        $attendances = Attendance::query()->where('user_id', $user->id)->where('date', date('Y-m-d'))->get();
        $status = [];
        $lecturer = [];

        foreach($attendances as $a){
            $status[$a->course_id] = $a->status;

        }

        $lecturers = Attendance::query()->where('date', date('Y-m-d'))->get();

        foreach($lecturers as $l){
            if($l->user->role == 'lecturer'){
                $lecturer[$l->course_id] = [
                    'status' => $l->status,
                    'id' => $l->id
                ];
            }
        }

        return view('attendance.index')->with([
            'data' => $data,
            'user' => $user,
            'week' => $week,
            'status' => $status,
            'lecturer' => $lecturer
        ]);
    }

    public function location($user_id, $course_id){
        $latitude = request()->get('lat');
        $longitude = request()->get('long');
        $accuracy = request()->get('accuracy');  // Get accuracy from request

        // if ($accuracy > 100) {
        //     return back()->withError('Your location accuracy is too low, please try again.');
        // }

        $user = User::findOrFail($user_id);
        $course = Course::findOrFail($course_id);

        $attendances = Attendance::query()->where('course_id', $course->id)->where('date', date('Y-m-d'))->get();
        $lecturerCoor = '';

        // check if user has already submit attendance
        $id_arr = [];

        // if student
        if($user->role !== 'lecturer'){

            if($attendances->isEmpty()){
                return back()->withError('The lecturer has not open for attendance');

            }

            // else got entries
            foreach($attendances as $a){
                $id_arr[] = $a->user_id;
                // if user has submitted attendance
                if(in_array($user->id, $id_arr)){
                    return back()->withError('You have already submitted your attendance for '.$course->name);
                }
                // check lecturer
                $lecturer = User::query()->where('id',$a->user_id)->where('role', 'lecturer')->first();

                // if lecturer found
                if($lecturer){
                    $lecturerCoor = [
                        'lat' => $a->lat,
                        'long' => $a->long
                    ];
                }

                if($lecturer == null and $a->user->role == 'student'){
                    return back()->withError('The lecturer has not open for attendance');
                }
            }
        }

        $attendance = new Attendance();
        $attendance->date = date('Y-m-d');
        $attendance->course_id = $course->id;
        $attendance->user_id = $user->id;

        if($user->role == 'student'){
            $attendance->status = 'Successful';
        }else{
            $attendance->status = 'Open';
        }
        $attendance->timestamp = date('H:i:s');
        $attendance->lat = $latitude;
        $attendance->long = $longitude;
        $attendance->ip_address = request()->ip();
        $attendance->save();

        $distance = '';
        if($attendance->user->role == 'student'){
            $distance = $attendance->distance($lecturerCoor['lat'], $lecturerCoor['long'], $attendance->lat, $attendance->long);

            return redirect('/attendance')
                ->with('attendance', $attendance)
                ->withSuccess('Attendance Submitted! You are '.number_format($distance).' meters away from the lecturer');
        }

        return redirect('/attendance')
            ->with('attendance', $attendance)
            ->withSuccess('This class is open for attendance');


    }

    public function close($attendance_id){
        $attendance = Attendance::findOrFail($attendance_id);

        if(Auth::user()->id !== $attendance->user_id){
            return back()->withError('Access Denied!');
        }

        $attendance->status = 'Close';
        $attendance->save();

        return redirect('/attendance')->withSuccess('Attendance Close!');

    }

    public function sheet($course_id, $date){
        if($date !== date('Y-m-d')){
            return back()->withError('You can only view attendance for today');
        }

        $attendances = Attendance::query()
            ->where('course_id', $course_id)
            ->where('date', date("Y-m-d", strtotime($date)))
            ->get();

        if($attendances->isEmpty() or $attendances == null ){
            return back()->withError('You have not open for attendance yet.');
        }

        $course = '';
        $users = [];

        foreach($attendances as $att){
            $user = User::findOrFail($att->user_id);
            $course = Course::findOrFail($att->course_id);

            $users[$att->course_id] = [
                'username' => $user->firstname. ' '.$user->lastname,
                'time' => $att->created_at
            ];
        }

        return view('attendance.sheet')->with([
            'users' => $users,
            'course' => $course
        ]);
    }

    public function advanced_view($course_id, $date){
        // get the timetable id and day/date
        $timetables = Timetable::query()->where('course_id', $course_id)->get();
        $course = Course::findOrFail($course_id);

        $day = date('D', strtotime($date));

        $timetable = '';
        $entries = [];

        foreach($timetables as $t){
            $entries = TimetableEntry::query()->where('timetable_id', $t->id)
                ->where('day', lcfirst($day))
                ->first();
        }

        $users = [
            'all' => [],
            'yes' => [],
            'no' => []
        ];

        $submitted = [];

        // get how many students are supposed to be in class
        $users_timetables = UserTimetable::query()->where('timetable_id', $entries->timetable_id)->get();


        // find how many has already submitted attendance
        foreach($users_timetables as $ut){
            $submitted_attendance = Attendance::query()->where('user_id', $ut->user_id)
                ->where('course_id', $course_id)
                ->where('date', date('Y-m-d', strtotime($date)))
                ->get();


            // if yes then indicate as yes otherwise no
            foreach($submitted_attendance as $sa){
                $submitted[$sa->user_id] = $sa->user_id;
            }

            // make sure only students
            if($ut->user->role == 'student'){
                if(in_array($ut->user_id, $submitted)){
                    $users['yes'][] = $ut->user;
                }else{
                    $users['no'][] = $ut->user;
                }

                $users['all'][] = $ut->user;
            }

        }

        return view('attendance.advanced')->with([
            'users' => $users,
            'course' => $course,
            'date' => $date
        ]);
    }
}
