@extends('layouts.app')
@section('title', 'Pin Authentication')

@section('content')

<div class="container shadow">
    {!!FB::open('/pin', 'post')!!}
    @csrf
    <div class="row text-center" style="margin-top:20%; ">
        <div class="col-12">
            <h1>Automated Attendance System</h1>
        </div>


        <div class="col-12 pb-2 text-start mt-5">
            <span><b><i>Please check your inbox for the 6-digit pin</i></b></span>
            {!!FB::input('pin', 'Pin')!!}</br>
        </div>



        <div class="col-12 text-center mt-2 mb-2">
            {!!FB::submit('Login', [], true)!!}

        </div>

    </div>
    {!!FB::close()!!}
</div>

@endsection
