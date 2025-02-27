@extends('layouts.app')
@section('title', 'Change Password Form')

@section('content')
<div class="container shadow col-8">
    {!!FB::open('/reset_password', 'post')!!}
    {!!FB::setErrors($errors)!!}
    @csrf
    <div class="row text-center" style="margin-top:20%; ">
        <div class="col-12">
            <h1>Reset Password</h1>
        </div>


        <div class="col-12 pb-2 text-start mt-5">
            {!!FB::input('pin', 'Pin')!!}</br>
            {!!FB::password('password', 'New Password')!!}</br>
            {!!FB::password('password_confirmation', 'New Password Confirmation')!!}</br>
        </div>


        <div class="col-12 text-center mt-2 mb-5">
            {!!FB::submit('Submit', [], true)!!}</br>

        </div>

    </div>
    {!!FB::close()!!}
</div>

@endsection
