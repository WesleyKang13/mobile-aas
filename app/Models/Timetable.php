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
}
