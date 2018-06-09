@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="text-align:center;">{{ Auth::user()->name }}'s Dashboard</div>
                    <td class="card-body">
                        <h1>USER'S GRAND TOTAL: {{ $totalBalance = (new App\Http\Controllers\AccountController)->getUserAccountsBalance(Auth::user()->id) }}</h1>
                        <br/>
                        <br/>
                        <p>SUMMARY ACCOUNT INFORMATION:</p>
                        {{ $accounts = (new \App\Http\Controllers\DashboardController)->getAccounts() }}
                        <table class="table">
                            <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Account Type</th>
                                <th>Created On</th>
                                <th>Current Balance</th>
                                <th>Relative Weight</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($accounts as $account)
                                @if(Auth::user()->id == $account->owner_id)
                                    <tr>
                                        <td> {{ $account->id }}</td>
                                        <td> {{ $account->account_type->name }} </td>
                                        <td> {{ $account->created_at }}</td>
                                        <td> {{ $account->current_balance }}</td>
                                        <td> {{ $relativeWeight = (new \App\Http\Controllers\DashboardController)->returnRelativeWeight($totalBalance, $account) }}%</td>
                                        <td><canvas id="relativeWeightCanvas" width="{{ abs($relativeWeight) > 100 ? 100 : abs($relativeWeight) . "%" }}" height="25%"
                                        style="border-radius: 5px;border-style:inset;background-color: @if($account->current_balance > 0) #009926 @else #c9302c @endif"></canvas></td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection