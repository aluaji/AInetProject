@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md10">
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
                        @endif

                        <a class="btn btn-info" href="{{ route('AllAccounts', Auth::user()->id) }}">
                            {{ __('List of Accounts') }}
                        </a>
                        <a class="btn btn-info" href="{{ route('dashboard', Auth::user()->id) }}">
                            {{ __('User Dashboard') }}
                        </a>
                        <a class="btn btn-info" href="{{ route('MemberList') }}">
                            {{ __('List of Associated Members') }}
                        </a>
                        <a class="btn btn-info" href="{{ route('OtherMemberList') }}">
                            {{ __('List of Associates-of') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
