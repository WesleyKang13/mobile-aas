<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Carbon;
use App\Mail\AuthMail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    // Custom Auth Controller

    // Index/Login Form
    public function index() {
        // Already Auth - Redirect (avoid middleware)
        if (Auth::check()) {
            return redirect('/')->withSuccess('You are already logged in.');
        }

        $rememberToken = request()->cookie('remember_web_token');
        if ($rememberToken) {
            $user = User::where('remember_token', $rememberToken)->first();
            if ($user) {
                Auth::login($user, true);
                request()->session()->put('auth_status', true);
                return redirect('/dashboard')->withSuccess('Welcome back! You are logged in.');
            }
        }

        return view('login');
    }

    // Process Login
    public function authenticate() {
        // Already Authenticated? Redirect to Dashboard
        if (request()->session()->get('auth_status', false)) {
            return redirect('/dashboard')->withSuccess('You are already logged in.');
        }

        // Validate User Input
        $valid = request()->validate([
            'email' => 'required|email|string|min:3|max:100',
            'password' => 'required|string|min:3|max:100',
        ]);

        // Find User by Email
        $user = User::where('email', $valid['email'])->first();

        if ($user == null) {
            password_verify('securitytest-always fail', '$2y$12$luVxhnjXVdCD7d1zX.u9Jehpa0saogVXeIz/5IcFTY1XBp3UaLiWi');
            return back()->withInput()->withError('Authentication Failed.');
        }

        if (!Hash::check($valid['password'], $user->password) or $user->enabled != 1) {
            return back()->withInput()->withError('Authentication Failed.');
        }

        $rememberMe = request()->has('remember_me') ? true : false;

        if ($rememberMe) {
            $rememberToken = Hash::make(uniqid('', true));

            $user->remember_token = $rememberToken;
            $user->save();

            cookie()->queue('remember_web_token', $rememberToken, 43200); // 30 days
        }

        if ($rememberMe) {
            Auth::login($user, true);
            request()->session()->put('auth_status', true);
            return redirect('/dashboard')->withSuccess('Welcome back! You are logged in.');
        }

        $this->mailPin($valid['email']);

        return redirect('/pin')->withSuccess('Please check your inbox for the PIN.');
    }




    // Logout/Destroy Session
    public function logout() {
        $request = request();
        $user_id = Auth::user()->id;
        $user = Auth::user();

        if ($user) {
            $user->remember_token = null;
            $user->save();
        }

        // Log out from session
        Auth::logout();
        // Flush session info
        $request->session()->flush();
        // Regenerate session id
        $request->session()->regenerate();

        cookie()->queue(cookie()->forget('remember_web_token'));

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

    public function forgotPassword(){
        return view('password.forgot_password');
    }

    public function email(){
        $valid = request()->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'The user email you have entered is not found'
        ]);

        $user = User::query()->where('email', $valid['email'])->first();

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $pin = '';

        for ($i = 0; $i < 12; $i++) {
            $pin .= $characters[random_int(0, $charactersLength - 1)];
        }

        $expires = time() + 600;

        // Store in session
        request()->session()->put('auth_pin', $pin);
        request()->session()->put('auth_expires', $expires);
        request()->session()->put('auth_email', $user->email);

        $data = [
            'user' => $user,
            'pin' => $pin
        ];

        Mail::send('password.mail', $data, function($message) use ($user) {
            $message->to($user->email);
            $message->subject('Password Reset Request');
        });

        return redirect('/reset_password')->withSuccess('Reset Password Mail has sent to the account, please check your inbox.')->with('user', $user);
    }

    public function reset(){
        return view('password.reset');
    }

    public function resetPassword(){
        $valid = request()->validate([
            'pin' => 'required',
            'password' => 'required|confirmed',
        ]);

        $pin = request()->session()->get('auth_pin', null);
        $expires = request()->session()->get('auth_expires', null);
        $email = request()->session()->get('auth_email', null);

        if($valid['pin'] !== $pin){
            return back()->withError('Invalid Pin Code')->withInput();
        }

        if($expires <= time()){
            return back()->withError('Pin Code has expired')->withInput();
        }

        // here is fine
        $user = User::query()->where('email' ,$email)->first();

        $new_password = Hash::make($valid['password']);
        $user->password = $new_password;
        $user->save();

        return redirect('/login')->withSuccess('Password reset Successfully');

    }

    public function pin()
    {
        if (request()->session()->get('auth_status', false)) {
            return redirect('/dashboard')->withSuccess('You are already logged in.');
        }

        if (request()->post()) {
            $valid = request()->validate([
                'pin' => 'required|min:6|max:10'
            ]);

            $pin = request()->session()->get('auth_pin', null);
            $expires = request()->session()->get('auth_expires', null);
            $email = request()->session()->get('auth_email', null);

            if ($pin === null || $expires === null || $email === null) {
                return redirect('/login')->withError('Missing required data. Please try again.');
            }

            if ($expires <= time()) {
                return redirect('/login')->withError('The PIN code has expired. Please try again.');
            }

            if ($pin == $valid['pin']) {
                $user = User::where('email', $email)->first();

                $remember = request()->session()->get('auth_remember', false);
                Auth::login($user, $remember);

                $user->timestamps = false;
                $user->lastlogin_at = Carbon::now();
                $user->lastlogin_ip = request()->ip();
                $user->save();
                $user->timestamps = true;

                request()->session()->put('auth_pin', null);
                request()->session()->put('auth_expires', null);
                request()->session()->put('auth_email', $email);
                request()->session()->put('auth_status', true);

                return redirect('/')->withSuccess('You have been authenticated.');
            }

            return redirect('/pin')->withError('Invalid PIN Code.');
        }

        return view('pin');
    }


    private function mailPin($email)
    {
        $pin = mt_rand(100000, 999999);
        $expires = time() + 600;

        request()->session()->put('auth_pin', $pin);
        request()->session()->put('auth_expires', $expires);
        request()->session()->put('auth_email', $email);
        request()->session()->put('auth_status', false);

        // Store "Remember Me" option
        request()->session()->put('auth_remember', request()->has('remember'));

        Mail::to($email)->send(new AuthMail([
            'email' => $email,
            'pin' => $pin
        ]));
    }

}
