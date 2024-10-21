@extends('layouts.app')
@section('title', 'Forgot Password Form')

@section('content')
<div class="container shadow col-8">
    {!!FB::open('/email', 'get')!!}
    {!!FB::setErrors($errors)!!}
    @csrf
    <div class="row text-center" style="margin-top:20%; ">
        <div class="col-12">
            <h1>Reset Password</h1>
            <p>Please enter your email and we will send you a form</p>
        </div>


        <div class="col-12 pb-2 text-start mt-5">
            {!!FB::input('email', 'Email')!!}</br>
        </div>


        <div class="col-12 text-center mt-2 mb-5">
            {!!FB::submit('Submit', [], true)!!}</br>

        </div>

    </div>
    {!!FB::close()!!}
</div>

@endsection
