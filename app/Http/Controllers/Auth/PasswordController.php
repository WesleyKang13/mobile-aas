<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class PasswordController extends Controller{

    public function password($id){
        $user = User::findOrFail($id);
        return view('password.change_password')->with('user', $user);
    }

    public function passwordConfirm($id){
        $valid = request()->validate([
            'current' => 'required',
            'password' => 'required|confirmed',
        ]);

        $user = User::findOrFail($id);

        if (Auth::attempt(['email' => $user->email,
                'password' => $valid['current'],
                'enabled' => 1
             ])){

            // current password is valid
            $new_password = Hash::make($valid['password']);
            $user->password = $new_password;
            $user->save();

            return redirect('/dashboard')->withSuccess('Password Changed Successfully');
        }else{
            return back()->withInput()->withError('Incorrect Current Password');
        }



    }
}
