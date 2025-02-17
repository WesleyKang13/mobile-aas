@extends('layouts.app')
@section('title', 'Login')

@section('content')

<div class="container shadow">
    {!!FB::open('/login', 'post')!!}
    {!!FB::setErrors($errors)!!}
    @csrf
    <div class="row text-center" style="margin-top:20%; ">
        <div class="col-12">
            <h1>Automated Attendance System</h1>
        </div>


        <div class="col-12 pb-2 text-start mt-5">
            {!!FB::input('email', 'Email')!!}</br>
            {!!FB::password('password', 'Password')!!}
        </div>



        <div class="col-12 text-center mt-2">
            {!!FB::submit('Login', [], true)!!}

        </div>

        <div class="col-12 text-center">
            <a href="/forgot_password">Forgot Password ? </a>
        </div>

    </div>
    {!!FB::close()!!}
</div>

@endsection
