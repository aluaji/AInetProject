@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">List of Users</div>

                    <div class="card-body">
                        <form class="form-horizontal" method="get"action="">
                            Type:
                            <label for="usr">Admin: </label>
                            <input type="radio" name="type" value="admin">
                            <label for="usr">Normal: </label>
                            <input type="radio" name="type" value="normal">
                            <br>
                            Status:
                            <label for="usr">Blocked: </label>
                            <input type="radio" name="status" value="blocked">
                            <label for="usr">Unblocked: </label>
                            <input type="radio" name="status" value="unblocked">
                            <br>
                            <div class="form-inline">
                            <label for="usr">Name: </label>
                            <input type="text" class="form-control col-md-4" name="name">
                            </div><br>

                        <button type="submit" class="btn btn-success">Apply Filter</button>
                        <button type="submit" class="btn btn-danger">Reset</button>
                            <br>
                        </form>
                        <br>
                        <table class="table table-striped" style="text-align: center">
                            <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Permissions</th>
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
                                            Admin User
                                        @endif
                                    </td>
                                    <!-- centra os botoes na tabela -->
                                    <td style="color: white; text-align: center;">
                                        @if($user == Auth::user())
                                            <a class="btn btn-secondary disabled">Unavailable</a>
                                        @else
                                            <form method="post" action="{{$user->blocked == 0 ?
                                                 route('users.block', $user->id) : route('users.unblock', $user->id) }}">
                                                @csrf
                                                @method('patch')
                                                @if($user->blocked == 1)
                                                    <button class="btn btn-danger">Unblock</button>
                                                @else
                                                    <button class="btn btn-danger">Block</button>
                                                @endif
                                            </form>
                                        @endif
                                    </td>
                                    <td style="text-align: center">
                                        @if($user == Auth::user())
                                            <a class="btn btn-secondary disabled">Unavailable</a>
                                        @else
                                            <form method="post" action="{{$user->admin == 0 ?
                                                 route('users.promote', $user->id) : route('users.demote', $user->id) }}">
                                                @csrf
                                                @method('patch')
                                                @if($user->admin == 1)
                                                    <button class="btn btn-warning">Demote</button>
                                                @else
                                                    <button class="btn btn-warning">Promote</button>
                                                @endif
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection