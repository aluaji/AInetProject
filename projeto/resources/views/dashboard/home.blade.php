@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="text-align:center;">{{ Auth::user()->name }}'s Dashboard</div>
                    <div class="card-body">
                        <p>TOTAL BALANCE OF ALL ACCOUNTS (USER'S GRAND TOTAL) : {{ (new App\Http\Controllers\AccountController)->getAccountsBalance() }}</p>

SUMMARY INFORMATION FOR EACH ACCOUNT (INCLUDING RELATIVE WEIGHT IN PERCENTAGE OVER THE TOTAL BALANCE)
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection