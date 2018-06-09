@extends("layouts.app")

@section('content')
    <div class="container">
        <div class="card-header">Creating an Account</div>
        <form method="post" action="{{ route('users.account.store') }}">
            @csrf
            <div class="form-group">
                <label for="account_type_id">Account Type:</label>
                <select class="form-control" id="account_type_id" name="account_type_id" value="{{ old('account_type_id') }}">
                    @foreach($account_type as $type)
                        <option value="{{$type->id}}">{{$type->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="date"> Date:</label>
                <input type="date" class="form-control" id="date" placeholder="Insert Date" name="date" value="{{ old('date') }}">
                @if($errors->has('date'))
                    <div class="alert alert-danger">
                        <strong>{{$errors->first('date')}}</strong>
                    </div>
                @endif
            </div>
            <div class="form-group">
                <label for="code"> Account Code:</label>
                <input type="text" class="form-control" id="code" placeholder="Insert Code" name="code" value="{{ old('code') }}">
                @if($errors->has('code'))
                    <div class="alert alert-danger">
                        <strong>{{$errors->first('code')}}</strong>
                    </div>
                @endif
            </div>
            <div class="form-group">
                <label for="start_balance"> Start Balance:</label>
                <input type="text" class="form-control" id="start_balance" placeholder="Insert Start Balance" name="start_balance" value="{{ old('start_balance') }}">
                @if($errors->has('start_balance'))
                    <div class="alert alert-danger">
                        <strong>{{$errors->first('start_balance')}}</strong>
                    </div>
                @endif
            </div>
            <div class="form-group">
                <label for="description"> Description:</label>
                <input type="text" class="form-control" id="description" placeholder="Insert Description (Optional)" name="description">
                @if($errors->has('description'))
                    <div class="alert alert-danger">
                        <strong>{{$errors->first('description')}}</strong>
                    </div>
                @endif
            </div>
            <button type="submit" class="btn btn-success">
                {{ __('Submit') }}

            </button>
            <a class="btn btn-danger" href="{{ route('AllAccounts', Auth::user()->id) }}">  {{ __('Cancel') }}</a>
        </form>
    </div>
@endsection