@extends("layouts.app")

@section('content')
    <div class="container">
        <div class="card-header">Editing an Account</div>
        <form method="post" action="{{ route('users.account.update', $account->id) }}">
            @csrf
            @method('put')
            <div class="form-group">
                <label for="account_type_id">Account Type:</label>
                <select class="form-control" id="account_type_id" name="account_type_id" value="{{ old($account->account_type_id) }}">
                    <option>1</option>
                    <option>2</option>
                    <option>3</option>
                    <option>4</option>
                    <option>5</option>
                </select>
            </div>
            <div class="form-group">
                <label for="code"> Account Code:</label>
                <input type="text" class="form-control" id="code" placeholder="Insert Code" name="code" value="{{ $account->code }}">
            </div>
            <div class="form-group">
                <label for="start_balance"> Start Balance:</label>
                <input type="text" class="form-control" id="start_balance" placeholder="Insert Start Balance" name="start_balance" value="{{ $account->start_balance }}">
            </div>
            <div class="form-group">
                <label for="description"> Description:</label>
                <input type="text" class="form-control" id="description" placeholder="Insert Description (Optional)" name="description" value="{{ $account->description }}">
            </div>

            <button type="submit" class="btn btn-success">
                {{ __('Submit') }}

            </button>
            <a class="btn btn-danger" href="{{ route('AllAccounts', Auth::user()->id) }}">
                {{ __('Cancel') }}</a>
        </form>
    </div>
@endsection