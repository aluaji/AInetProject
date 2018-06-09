@extends("layouts.app")

@section("content")
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Closed Accounts</div>
                    <div class="card-body">
                        <div class="btn-group">
                            <a class="btn btn-primary" href="{{ route('AllAccounts', Auth::user()->id) }}">
                                {{ __('All Accounts') }}
                            </a>
                            <a class="btn btn-primary" href="{{ route('OpenedAccounts', Auth::user()->id) }}">
                                {{ __('Opened Accounts') }}
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
                                    @if(isset($account->deleted_at))
                                        <tr>
                                            <td> {{ $account->owner_id }}</td>
                                            <td> {{ $account->code }}</td>
                                            <td> {{ $account->id }}</td>
                                            <td> {{ $account->account_type->name }}</td>
                                            <td> {{ $account->created_at }}</td>
                                            <td> {{ $account->start_balance }}</td>
                                            <td> {{ $account->current_balance }}</td>
                                            <td> <form method="post" action = "{{ route('users.account.reopen', $account->id) }}">
                                                    @csrf
                                                    @method('patch')
                                                    <button class="btn btn-success">
                                                        {{ __('Reopen Account') }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endif
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
@endsection