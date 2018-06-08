@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">List of Associates</div>
                    <div class="card-body">
                        <table class="table table-striped" style="text-align: center">
                            <thead class="thead-dark">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($users as $user)
                                @if(Auth::user()->id = $user->associate_member->)
                                    <tr>
                                        <td> {{ $user->name }}</td>
                                        <td> {{ $user->email }}</td>
                                    </tr>
                                @endif
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