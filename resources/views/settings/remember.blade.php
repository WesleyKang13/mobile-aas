@extends('layouts.app')
@section('title', 'Remember Me')
@section('content')
<div class="container bg-white">
    {!!FB::open('/settings/remember', 'POST')!!}
    <?php $remember = Auth::user()->remember_token;?>

    @if($remember !== null)
        {!!FB::setInput([
            'remember' => 'Yes'
        ])!!}
    @else
        {!!FB::setInput([
            'remember' => 'No'
        ])!!}
    @endif

    {!!FB::setErrors($errors)!!}
    @csrf
    <div class="row mt-4">
        <div class="col-12">
            <h1>Remember Me <span title="No login required once you have enabled remember me. Do not logout because it will disabled this feature."><i class="fa-solid fa-circle-info"></i></span></h1>
        </div>

        <div class="col-12">
            {!!FB::select('remember','Remember Me',['Yes' => 'Yes','No' => 'No'])!!}
        </div>

        <div class="col-12 text-end mt-2 pb-2">
            {!!FB::submit('Save',[],true)!!}
        </div>
    </div>
    {!!FB::close()!!}
</div>
@endsection
