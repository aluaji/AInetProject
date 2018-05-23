@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">List of Users</div>

                    <div class="card-body">

                        @if(Auth::user()->admin == 1)
                            <table class="table">
                                <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td> {{ $user->id }}</td>
                                        <td> {{ $user->name }}</td>
                                        <td> {{ $user->email }}</td>
                                        <td>
                                            @if($user->admin == 0)
                                                Normal User
                                            @else
                                                Admin
                                            @endif
                                        </td>
                                        <!-- centra os botoes na tabela -->
                                        <td style="color: white; text-align: center;">
                                            @if($user->blocked == 1)
                                                <a class="btn btn-danger">Unblock</a>
                                            @else
                                                <a class="btn btn-danger">Block</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            {{ $users->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection