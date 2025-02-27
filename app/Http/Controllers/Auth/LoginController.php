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

        return view('login');
    }

    // Process Login
    public function authenticate() {
        // Already Auth - Redirect (avoid middleware)
        if (request()->session()->get('auth_status', false)) {
            return redirect('/dashboard')->withSuccess('You are already logged in.');
        }

        // Rules for validation
        $valid = request()->validate([
            'email' => 'required|email|string|min:3|max:100',
            'password' => 'required|string|min:3|max:100',
        ]);

        // Find user
        $user = User::query()
            ->where( 'email', $valid['email']) // By email
            ->first();

        // Not found?
        if ($user == null) {
            // For security reasons do a password_verify to prevent timing detection (i.e. attacker measures response times to see if a user exists or not).
            password_verify('securitytest-always fail', '$2y$12$luVxhnjXVdCD7d1zX.u9Jehpa0saogVXeIz/5IcFTY1XBp3UaLiWi');

            // Auth Failure
            return back()->withInput()->withError('Authentication Failed.');
        }else{
            if (!$user or !Hash::check($valid['password'], $user->password) or $user->enabled != 1) {
                return back()->withInput()->withError('Authentication Failed.');
            }

            $this->mailPin($valid['email']);

            // Redirect
            return redirect('/pin')->withSuccess('Please check your inbox for the pin');
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

    public function pin() {
        // Already auth?
        if (request()->session()->get('auth_status', false)) {
            return redirect('/dashboard')->withSuccess('You are already logged in.');
        }


        // POST
        if (request()->post()) {
            // Validate
            $valid = request()->validate([
                'pin' => 'required|min:6|max:10'
            ]);

            // Get details from session
            $pin = request()->session()->get('auth_pin', null);
            $expires = request()->session()->get('auth_expires', null);
            $email = request()->session()->get('auth_email', null);

            // Null
            if($pin === null or $expires === null or $email === null) {
                return redirect('/login')->withError('Missing require data. Please try again.');
            }

            // Too late?
            if ($expires <= time()) {
                return redirect('/login')->withError('The PIN code has expired. Please try again.');
            }

            if ($pin == $valid['pin']) {
                //Attempt Auth - username/email, password, enabled


                    $user = User::query()->where('email' ,$email)->first();

                    Auth::login($user);

                    // Login is successful - set last login details
                    $user->timestamps = false; // Disalbe updated_at
                    $user->lastlogin_at = Carbon::now();
                    $user->lastlogin_ip = request()->ip();
                    $user->save();
                    $user->timestamps = true; // Re-enable timestamps


                    request()->session()->put('auth_pin', null);
                    request()->session()->put('auth_expires', null);
                    request()->session()->put('auth_email', $email);
                    request()->session()->put('auth_status', true);

                    return redirect('/')->withSuccess('You have been authenticated');


            }

            // Fail
            return redirect('/pin')->withError('Invalid PIN Code.');

        }

        // Just display form
        return view('pin');
    }

    private function mailPin($email) {
        // Generate a pin, store in sessin along with expires

        // Generate PIN & expiration
        $pin = mt_rand(100000,999999);
        $expires = time() + 600;

        // Store in session
        request()->session()->put('auth_pin', $pin);
        request()->session()->put('auth_expires', $expires);
        request()->session()->put('auth_email', $email);
        request()->session()->put('auth_status', false);

        // Send email
        Mail::to($email)->send(new AuthMail(
            [
                'email' => $email,
                'pin' => $pin
            ]
        ));

    }
}
