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
        // get date from url if exist
        $search = request()->get('date');

        // get current user
        $user = User::findOrFail(Auth::user()->id);

        $today = now();

        $date = new DateTime($today);
        $week = $date->format('W');

        $data = $user->timetable($user->id, $search);

        $attendances = Attendance::query()->where('user_id', $user->id);

        if($search !== null){
            $attendances = $attendances->where('date', date('Y-m-d', strtotime($search)));
        }else{
            $attendances = $attendances->where('date', date('Y-m-d'));
        }

        $attendances = $attendances->get();

        $status = [];
        $lecturer = [];

        foreach($attendances as $a){
            $status[$a->course_id] = $a->status;

        }

        $lecturers = Attendance::query();

        if($search !== null){
            $lecturers = $lecturers->where('date', date('Y-m-d', strtotime($search)));
        }else{
            $lecturers = $lecturers->where('date', date('Y-m-d'));
        }

        $lecturers = $lecturers->get();

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
        $date = request()->get('date');

        if($date !== date('Y-m-d')){
            return back()->withError('Access Denied!');
        }

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

        // check if lecturer has open the same attendance again
        // foreach($attendances as $a){
        //     if($a->status == 'open' and Auth::user()->role == 'lecturer'){
        //         return back()->withError('The lecturer has already open for attendance');
        //     }
        // }

        // check if user has already submit attendance
        $id_arr = [];

        // if student
        if($user->role !== 'lecturer'){

            if($attendances->isEmpty()){
                return back()->withError('The lecturer has not open for attendance');

            }

            // else got entries
            foreach($attendances as $a){
                if($a->user->role == 'lecturer' and $a->status == 'Close'){
                    return back()->withError('Access Denied!');
                }

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

                if($lecturer == null and count($attendances) == 0){
                    return back()->withError('The lecturer has not open for attendance');
                }
            }
        }

        // check if already open by lecturer
        $lecturer_existed = Attendance::query()
                        ->where('course_id', $course->id)
                        ->where('date', date('Y-m-d'))
                        ->where('user_id', Auth::user()->id)
                        ->first();

        if($lecturer_existed !== null and $lecturer_existed->user->role == 'lecturer'){
            $attendance = Attendance::findOrFail($lecturer_existed->id);
            $attendance->status = 'Open';
            $attendance->timestamp = date('H:i:s');
            $attendance->lat = $latitude;
            $attendance->long = $longitude;
            $attendance->ip_address = request()->ip();
            $attendance->save();
        }else{
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
        }

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

        $date = request()->get('date');

        if(date('Y-m-d', strtotime($date)) !== date('Y-m-d')){
            return back()->withError('Access Denied!');
        }

        $attendance->status = 'Close';
        $attendance->save();

        return redirect('/attendance')->withSuccess('Attendance Close!');

    }

    public function sheet($course_id, $date){
        // if($date !== date('Y-m-d')){
        //     return back()->withError('You can only view attendance for today');
        // }

        $attendances = Attendance::query()
            ->where('course_id', $course_id)
            ->where('date', date("Y-m-d", strtotime($date)))
            ->get();

        if($attendances->isEmpty() or $attendances == null ){
            return back()->withError('There are no attendance yet');
        }

        $course = '';
        $users = [];

        foreach($attendances as $att){
            $user = User::findOrFail($att->user_id);
            $course = Course::findOrFail($att->course_id);

            if($user->role == 'lecturer'){
                $lec_lat = $att->lat;
                $lec_long = $att->long;
            }

            $users[$att->user_id] = [
                'role' => $user->role,
                'username' => $user->firstname. ' '.$user->lastname,
                'time' => $att->created_at,
                'distance' => $att->distance($att->lat, $att->long, $lec_lat, $lec_long),
                'remarks' => $att->remarks
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


            if($entries){
                break;
            }
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

    public function manual($course_id, $date){
        if($date !== date('Y-m-d')){
            return back()->withError('You can only have manual entry for today');
        }

        $timetables = Timetable::query()->where('course_id', $course_id)->get();
        $course = Course::findOrFail($course_id);

        $day = date('D', strtotime($date));

        $timetable = '';
        $entries = [];

        foreach($timetables as $t){
            $entries = TimetableEntry::query()->where('timetable_id', $t->id)
                ->where('day', lcfirst($day))
                ->first();


            if($entries){
                break;
            }
        }

        $users = [];
        $users_timetables = UserTimetable::query()->where('timetable_id', $entries->timetable_id)->get();

        foreach($users_timetables as $ut){
            if($ut->user->role == 'student'){
                $users[$ut->timetable_id] = $ut->user->firstname. ' '.$ut->user->lastname . ' - '.$ut->user->email;
            }
        }

        return view('attendance.manual')->with([
            'course' => $course,
            'timetable' => $entries,
            'users' => $users,
            'date' => $date
        ]);
    }

    public function manualEntry($course_id, $date){
        if($date > date('Y-m-d', strtotime(today()))){
            return back()->withError('You can only have manual entry for today or before');
        }

        $valid = request()->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($valid['user_id']);
        $course = Course::find($course_id);

        if($course == null){
            return back()->withError('Invalid course');
        }

        // check if this user has already submitted attendance
        $check = Attendance::query()->where('course_id', $course->id)->where('date', $date)->get();

        foreach($check as $c){
            if($c->user_id == $user->id){
                return back()->withError('This student has submitted attendance already');
            }
        }

        $attendance = new Attendance();
        $attendance->date = date('Y-m-d', strtotime($date));
        $attendance->course_id = $course->id;
        $attendance->user_id = $user->id;
        $attendance->status = 'Successful';
        $attendance->timestamp = now();
        $attendance->ip_address = request()->ip();
        $attendance->remarks = 'Manual Entry by '.Auth::user()->firstname.' '.Auth::user()->lastname;
        $attendance->save();

        return redirect('/attendance/'.$course->id.'/'.$date)->withSuccess('Manual Attendance submitted successfully');
    }
}
