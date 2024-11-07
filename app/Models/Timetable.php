<?php

namespace App\Models;

use App\Models\Course;
use App\Models\Classroom;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    use HasFactory;

    public function classroom(){
        return $this->belongsTo(Classroom::class,'class_id');
    }

    public function course(){
        return $this->belongsTo(Course::class);
    }

    public function duration($from, $to, $created_at){
        $total = 0;
        $supposed = 0;

        $timetable = TimetableEntry::query()->where('timetable_id', $this->id)->count();

        if(strtotime($created_at) >= strtotime($from) and strtotime($created_at) <= strtotime($to)){
            // total classes we have in a sem for one timetable
            $difference = strtotime($to) - strtotime($created_at);

            $days = abs(round($difference/86400));

            if(strtotime(date('Y-m-d H:i:s')) >= strtotime($created_at)){
                // get the numbers of attendance that should be taken by the date now
                $period = strtotime(date('Y-m-d H:i:s')) - strtotime($created_at);

                $period_day = abs(round($period/86400));

                // get weeks
                $weeks = $period_day / 7;

                $supposed = round($weeks * $timetable);
            }

            // get weeks
            $weeks = $days / 7;

            $total = round($weeks * $timetable);
        }

        if($supposed == 0){
            $supposed = 1;
        }

        $data = [
            'total' => $total,
            'supposed' => $supposed
        ];

        return $data;
    }
}
