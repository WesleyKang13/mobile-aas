<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Custom Auth Controller

    // Index/Login Form
    public function index() {
        // Already Auth - Redirect (avoid middleware)
        if (Auth::check()) {
            return redirect('/')->withSuccess('You are already logged in.');
        }

        return view('login');
    }

    // Process Login
    public function authenticate() {
        // Already Auth - Redirect (avoid middleware)
        if (Auth::check()) {
            return redirect('/')->withSuccess('You are already logged in.');
        }

        // Rules for validation
        $valid = request()->validate([
            'email' => 'required|email|string|min:3|max:100',
            'password' => 'required|string|min:3|max:100',
        ]);

        // Find User
        $user = User::query()
            ->where( 'email', $valid['email']) // By email or usrename
            ->first();

        // Not found?
        if ($user == null) {
            // For security reasons do a password_verify to prevent timing detection (i.e. attacker measures response times to see if a user exists or not).
            password_verify('securitytest-always fail', '$2y$12$luVxhnjXVdCD7d1zX.u9Jehpa0saogVXeIz/5IcFTY1XBp3UaLiWi');
            // TODO IP FAILCOUNT
            // Auth Failure
            return back()->withInput()->withError('Authentication Failed.');
        }else{
            // Attempt Auth - username/email, password, enabled
            if (Auth::attempt(['email' => $valid['email'],
                'password' => $valid['password'],
                'enabled' => 1
             ])) {

                // Login is successful - set last login details
                $user->timestamps = false; // Disalbe updated_at
                $user->lastlogin_at = Carbon::now();
                $user->lastlogin_ip = request()->ip();
                $user->save();
                $user->timestamps = true; // Re-enable timestamps

                // Authenticated
                // TODO FAILCOUNT FOR IP AND USER
                // TODO MFA/FORCE MFA/FORCE CHAPASS?

                // Regenerate session id
                request()->session()->regenerate();

                // Redirect
                return redirect('/')->withSuccess('Login Successful!');

            }

            // Failed
            // TODO FAILCOUNT
            return back()->withInput()->withError('Authentication Failed.');
        }
    }

    // Logout/Destroy Session
    public function logout() {
        $request = request();
        $user_id = Auth::user()->id;

        // Log out from session
        Auth::logout();
        // Flush session info
        $request->session()->flush();
        // Regenerate session id
        $request->session()->regenerate();

        // Destroy All User Sessions?
        if (request()->get('all', false) !== false) {
            // Delete all sessions belong to that user
            DB::table('sessions')
                ->where('user_id', $user_id)
                ->delete();

            // redirect
            return redirect('/login')->withSuccess('You have been logged out of all sessions successfully!');

        }
        // redirect
        return redirect('/login')->withSuccess('You have been logged out successfully!');
    }


}
