@extends("layouts.app")

@section("content")
    @auth
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card">
                        <div class="card-header">All Accounts</div>
                        <div class="card-body">
                            <div class="btn-group">
                                <a class="btn btn-primary" href="{{ route('OpenedAccounts', Auth::user()->id) }}">
                                    {{ __('Opened Accounts') }}
                                </a>
                                <a class="btn btn-primary" href="{{ route('ClosedAccounts', Auth::user()->id) }}">
                                    {{ __('Closed Accounts') }}
                                </a>
                                <a class="btn btn-success" href=" {{ route('users.account.create') }} ">
                                    {{ __('Create an Account') }}
                                </a>
                                <a class="btn btn-warning" href="{{ route('home') }}">
                                    {{ __('Home') }}
                                </a>
                            </div>
                            <table class="table">
                                <thead class="thead-dark">
                                <tr>
                                    <th>Owner ID</th>
                                    <th>Account Code</th>
                                    <th>#</th>
                                    <th>Account Type</th>
                                    <th>Created On</th>
                                    <th>Start Balance</th>
                                    <th>Current Balance</th>
                                    <th><center>Action</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($accounts as $account)
                                    @if($user->id == $account->owner_id)
                                        <tr>
                                            <td> {{ $account->owner_id }}</td>
                                            <td> {{ $account->code }}</td>
                                            <td> {{ $account->id }}</td>
                                            <td>
                                                {{ $account->account_type->name }}
                                            </td>
                                            <td> {{ $account->created_at }}</td>
                                            <td> {{ $account->start_balance }}</td>
                                            <td> {{ $account->current_balance }}</td>
                                            <td>
                                                <a class="btn btn-warning" href="{{ route('users.account.edit', $account->id) }}">
                                                    {{ __('Edit Account') }}
                                                </a>

                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                </tbody>
                            </table>
                            {{ $accounts->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endauth
@endsection