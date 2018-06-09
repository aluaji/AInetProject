@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">List of Users</div>

                    <div class="card-body">
                        @if(count($associated_users) > 0)
                        <table class="table table-striped" style="text-align: center">
                            <thead class="thead-dark">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($associated_users as $user)
                                <tr>
                                    <td>
                                        {{ $user->name }}
                                    </td>
                                    <td> <img src="{{ Storage::url('public/profiles/' . $user->profile_photo)}}" height="42" width="42"
                                              style="border-radius: 100px;"></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $associated_users->links() }}
                            @else
                                <p>Missing associates</p>
                            @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection