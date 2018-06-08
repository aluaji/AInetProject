@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">List of Users</div>

                    <div class="card-body">
                        <form class="form-horizontal" method="get" action="">
                            <div class="form-inline">
                                <label for="usr">Name: </label>
                                <input type="text" class="form-control col-md-4" name="name">
                            </div>
                            <br>

                            <button type="submit" class="btn btn-success">Apply Filter</button>
                            <button type="submit" class="btn btn-danger">Reset</button>
                            <br>
                        </form>
                        <br>
                        <table class="table table-striped" style="text-align: center">
                            <thead class="thead-dark">
                            <tr>
                                <th>Name</th>
                                <th>Photo</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($users as $user)
                                        <tr>
                                            <td>
                                                {{ $user->name }}
                                                @foreach($associated_users as $associated_user)
                                                    @if($associated_user->id == $user->id)
                                                        <span>associate</span>
                                                    @endif
                                                @endforeach
                                                @foreach($associated_to as $associated_to_user)
                                                    @if($associated_to_user->id == $user->id)
                                                        <span>associate-of</span>
                                                    @endif
                                                @endforeach
                                            </td>
                                            <td> <img src="{{ Storage::url('public/profiles/' . $user->profile_photo)}}" height="42" width="42"
                                                      style="border-radius: 100px;"></td>
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