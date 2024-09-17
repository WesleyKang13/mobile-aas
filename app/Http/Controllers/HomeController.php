<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller{
    public function dashboard(){
        $user = User::findOrFail(Auth::user()->id);

        $user_course = DB::table('users_courses')->where('user_id', $user->id)->get();

        // store all courses of the user
        $courses = [];

        foreach($user_course as $uc){
            $course = Course::find($uc->course_id);

            if($course == null){
                $courses[$uc->course_id] = $uc->course_id. ' not found';
            }else{
                $courses[$user->id] = $course;
            }
        }

        return view('dashboard')->with([
            'user' => $user,
            'courses' => $courses
        ]);
    }
}
