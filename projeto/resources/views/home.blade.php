@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Welcome {{Auth::user()->name}}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                        <img src="{{ Storage::url(Auth::user()->profile_photo)}}" height="80" width="80"
                             style="border-radius: 100px;">

                    @if(Auth::user()->admin == 1)
                            <a class="btn btn-link" href="{{ route('users.list') }}">
                                {{ __('List of Users') }}
                            </a>
                     @endif

                    You are logged in!
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
