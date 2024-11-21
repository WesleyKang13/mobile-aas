<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TimetableEntry;
use App\Models\Timetable;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller{
    public function dashboard(){
        $user = User::findOrFail(Auth::user()->id);

        $user_timetable = DB::table('users_timetables')->where('user_id', $user->id)->get();

        $data = [];
        $attendanceData = [];

        foreach($user_timetable as $ut){
            $entries = TimetableEntry::query()
                    ->where('timetable_id', $ut->timetable_id)
                    ->where('day', lcfirst(date('D')))
                    ->get();

            // get all entry instead of just today
            $attendace_rate = TimetableEntry::query()
                    ->where('timetable_id', $ut->timetable_id)
                    ->get();



            $timetable = Timetable::findOrFail($ut->timetable_id);

            // get attendance check
            $attendance = Attendance::query()
                ->where('course_id', $timetable->course_id)
                ->where('user_id', $user->id)
                ->where('date', date('Y-m-d'))
                ->first();

            foreach($attendace_rate as $ar){
                 // this is for checking the attendance rate
                 $totalAttendance = Attendance::query()
                 ->where('course_id', $timetable->course_id)
                 ->where('user_id', $user->id)
                 ->where('date', '>=', $timetable->from)
                 ->where('date', '<=', $timetable->to)
                 ->count();

                $classes_calculation = $timetable->duration($timetable->from, $timetable->to, $ar->created_at);
                $present = $totalAttendance / $classes_calculation['supposed'] ;
                $present *= 100;
                $absent = 100 - $present;

                $attendanceData[$timetable->id] = [
                    'course_name' => $timetable->course->name,
                    'present' => $present,
                    'absent' => $absent
                ];
            }


            foreach($entries as $e){

                if($attendance !== null and $attendance->status == 'Successful'){
                    $data[$e->timetable_id] = [
                        'status' => 'Yes',
                        'class_code' => $timetable->classroom->code,
                        'course_name' => $timetable->course->name,
                        'time' => $e->starttime. ' - '.$e->endtime
                    ];
                }else{
                    $data[$e->timetable_id]  = [
                        'status' => 'No',
                        'class_code' => $timetable->classroom->code,
                        'course_name' => $timetable->course->name,
                        'time' => $e->starttime. ' - '.$e->endtime
                    ];
                }

            }
        }

        return view('dashboard')->with([
            'data' => $data,
            'user' => $user,
            'attendanceData' => $attendanceData
        ]);
    }
}
