<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Timetable;
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

        return view('attendance.index')->with([
            'data' => $data,
            'user' => $user,
            'week' => $week
        ]);
    }
}
