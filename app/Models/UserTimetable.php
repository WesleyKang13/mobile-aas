<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTimetable extends Model
{
    use HasFactory;
    protected $table = 'users_timetables';

    public function user(){
        return $this->belongsTo(User::class);
    }
}
