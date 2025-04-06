<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, String $role = null): Response
    {
        if (!Auth::check()) {
            // Check if there's a remember_token in the cookie
            if ($request->hasCookie('remember_web_token')) {
                $rememberToken = $request->cookie('remember_web_token');
                $user = User::where('remember_token', $rememberToken)->first();

                if ($user) {
                    // Authenticate the user if the token matches
                    Auth::login($user, true);  // The second parameter true ensures that the user is remembered
                    return redirect('/dashboard');
                }
            }

            // If not authenticated and no valid cookie, redirect to login
            return redirect('/login')->withError('You must log in first!');
        }

        $user = Auth::user();
        if ($user->enabled != 1) {
            Auth::logout();
            $request->session()->flush();
            $request->session()->regenerate();
            return redirect('/login')->withError('Your user account has been disabled!');
        }

        return $next($request);
    }


}
