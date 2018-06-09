@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">List of Users</div>

                    <div class="card-body">
                        @if(count($associate_to) > 0)
                        <table class="table table-striped" style="text-align: center">
                            <thead class="thead-dark">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Accounts</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($associate_to as $user)
                                <tr>
                                    <td>
                                        {{ $user->name }}
                                    </td>
                                    <td>
                                        {{ $user->email }}
                                    </td>
                                    <td>
                                        <a class="btn btn-info" href="{{ route('AllAccounts', $user->id) }}">
                                            {{ __('List of Accounts') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $associate_to->links() }}
                            @else
                                <p>Missing Groups</p>
                            @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection