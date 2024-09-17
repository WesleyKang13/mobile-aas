<?php

namespace App\Models;

use App\Models\Timetable;
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

    public function timetable($id){
        // get current user's timetable

        $today = now();
        $date = new DateTime($today);
        $week = $date->format("W");
        $day = $date->format("D");

        $user = User::findOrFail($id);

        $timetables = DB::table('users_timetables')->where('user_id', $user->id)->get();

        $this_week = [];
        $details = [];

        foreach($timetables as $t){
            $this_week = Timetable::query()
                    ->where('week_number', $week)
                    ->where('day', $day)
                    ->orderBy('start_time', 'asc')
                    ->get();

            foreach($this_week as $tw){
                $details[$tw->id] = [
                    'class' => $tw->classroom->code,
                    'course_name' => $tw->course->name,
                    'time' => $tw->start_time. ' - '.$tw->end_time
                ];
            }

        }

        return $details;
    }
}
