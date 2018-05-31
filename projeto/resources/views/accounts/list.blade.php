@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">All Accounts</div>
                    <div class="card-body">
                        <a class="btn btn-primary" href="{{ route('OpenedAccounts', Auth::user()->id) }}">
                            {{ __('Opened Accounts') }}
                        </a>
                        <br><a class="btn btn-primary" href="{{ route('ClosedAccounts', Auth::user()->id) }}">
                            {{ __('Closed Accounts') }}
                        </a>
                        </br>
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
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($accounts as $account)
                                @if(Auth::user()->id == $account->owner_id)
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
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>

                        @if(count($accounts) > 10)
                        {{ $accounts->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection