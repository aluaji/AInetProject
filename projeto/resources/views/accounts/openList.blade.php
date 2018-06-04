@extends("layouts.app")

@section("content")
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-15">
                <div class="card">
                    <div class="card-header">Opened Accounts</div>
                    <div class="card-body">
                        <div class="btn-group">
                            <a class="btn btn-primary" href="{{ route('AllAccounts', Auth::user()->id) }}">
                                {{ __('All Accounts') }}
                            </a>
                            <a class="btn btn-primary" href="{{ route('ClosedAccounts', Auth::user()->id) }}">
                                {{ __('Closed Accounts') }}
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
                                <th></th>
                                <th><center>Actions</center></th>
                                <th></th>

                            </tr>
                            </thead>
                            <tbody>
                            @foreach($accounts as $account)
                                @if(Auth::user()->id == $account->owner_id)
                                    @if(!isset($account->deleted_at))
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
                                                <a class="btn btn-warning" href="{{ route('movements.list', $account->id) }}">
                                                    {{ __('List Movements') }}
                                                </a>
                                            </td>
                                            <td>@if($account->current_balance != 0.00)
                                                    <a class = "btn btn-danger disabled">Close Account</a>
                                                @else
                                                    <form method="post" action = "{{ route('users.account.close', $account->id) }}">
                                                        @csrf
                                                        @method('patch')
                                                        <button class="btn btn-danger">
                                                            {{ __('Close Account') }}

                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                            <td>@if(isset($account->last_movement_date))
                                                    <a class="btn btn-danger disabled">Delete Account</a>

                                                @elseif($account->current_balance != 0.00)
                                                    <a class="btn btn-danger disabled">Delete Account</a>
                                                @else
                                                    <form method="post" action = "{{ route('users.account.delete', $account->id) }}">
                                                        @csrf
                                                        @method('delete')
                                                        <button class="btn btn-danger">
                                                            {{ __('Delete Account') }}

                                                        </button>
                                                    </form>
                                                @endif
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