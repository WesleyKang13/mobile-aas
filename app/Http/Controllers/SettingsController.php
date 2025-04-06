<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class SettingsController extends Controller{
    public function index(){
        $user = Auth::user();

        return view('settings.remember')->with([
            'user' => $user
        ]);
    }

    public function updateRememberMe() {
        $valid = request()->validate([
            'remember' => 'required|in:Yes,No'
        ]);

        $user = Auth::user();

        if ($valid['remember'] == 'Yes') {
            $rememberToken = Str::random(60);
            $user->remember_token = $rememberToken;
            $user->save();

            return redirect('/settings')
                ->withSuccess('Your preferences have been updated.')
                ->cookie('remember_web_token', $rememberToken, 60 * 24 * 30); // Store for 30 days
        } else {
            $user->remember_token = null;
            $user->save();

            return redirect('/settings')
                ->withSuccess('Remember Me has been disabled.')
                ->withCookie(cookie()->forget('remember_web_token')); // Remove the cookie
        }
    }


}
