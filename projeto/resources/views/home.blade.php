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

                    @if(Auth::user()->admin == 1)
                            <a class="btn btn-info" href="{{ route('users.list') }}">
                                {{ __('List of Users') }}
                            </a>

                            <a class="btn btn-info" href="{{ route('movements.list') }}">
                                {{ __('List of Movements') }}
                            </a>
                     @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
