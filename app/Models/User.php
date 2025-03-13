<?php

namespace App\Models;

use App\Models\Timetable;
use App\Models\TimetableEntry;
use Illuminate\Support\Facades\DB;
use DateTime;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public function timetable($id, $filter = null){
        $today = now();
        $user = User::findOrFail($id);

        if($filter !== null){
            $today = $filter;
        }


        $date = new DateTime($today);

        $day = $date->format("D");

        $details = [];

        $timetables = DB::table('users_timetables')->where('user_id', $user->id)->get();

        foreach($timetables as $t){
            $timetable = Timetable::findOrFail($t->timetable_id);

            // get timetable from and to
            if($today >= $timetable->from and $today <= $timetable->to){
                // get timetable entries
                $entries = TimetableEntry::query()
                        ->where('timetable_id', $timetable->id)
                        ->where('day', lcfirst($day))
                        ->get();
                // loop through the entries
                foreach($entries as $e){
                    if(ucfirst($e->day) == $day){
                        $details[$t->id] = [
                            'course_id' => $timetable->course->id,
                            'class' => $timetable->classroom->code,
                            'course_name' => $timetable->course->name,
                            'time' => $e->starttime. ' - '.$e->endtime
                        ];
                    }
                }
            }
        }
        
        return $details;
    }
}
