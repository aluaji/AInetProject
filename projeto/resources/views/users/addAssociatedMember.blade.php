@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">List of Users</div>

                    <div class="card-body">
                        @if(count($users) > 0)
                            <table class="table table-striped" style="text-align: center">
                                <thead class="thead-dark">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td> {{ $user->name }} </td>
                                    <td> {{ $user->email }}</td>
                                    <td> <button class="btn btn-info"> {{ __('Add Member') }} </button></td>
                                </tr>
                                @endforeach
                            </table>
                        @else
                            <p>Missing associates</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection