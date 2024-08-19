@if(session('success') or session('error'))
<div class="container py-2">
@if (session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        <div>
            {!! session('success') !!}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        <div>
            {!! session('error') !!}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif


</div>
@endif
