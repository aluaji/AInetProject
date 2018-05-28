@extends("layouts.app")

@section("content")
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">All Accounts</div>

                    <div class="card-body">
                        <table class="table">
                            <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Account Type</th>
                                <th>Created On</th>
                                <th>Start Balance</th>
                                <th>Current Balance</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($accounts as $account)
                                <tr>
                                    <td> {{ $account->id }}</td>
                                    <td>
                                        @if($account->account_type_id == 1)
                                            Bank Account
                                        @elseif($account->account_type_id == 2)
                                            Pocket Money
                                        @elseif($account->account_type_id == 3)
                                            PayPal Account
                                        @elseif($account->account_type_id == 4)
                                            Credit Card
                                        @elseif($account->account_type_id == 5)
                                            Meal Card
                                        @endif
                                    </td>
                                    <td> {{ $account->created_at }}</td>
                                    <td> {{ $account->start_balance }}</td>
                                    <td> {{ $account->current_balance }}</td>
                                    <td> <button type="button">Add File</button> </td>
                                </tr>
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