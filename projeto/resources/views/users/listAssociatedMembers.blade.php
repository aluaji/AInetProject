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
                                    <td> {{ $user->email }}</td>
                                    {{--<td><a class="btn btn-outline-info btn-block" href="{{ route('DeleteMember', $user) }}">Remove Member</a></td>--}}
                                    <td>
                                        <form method="post" action = "{{ route('DeleteMember', $user) }}">
                                            @csrf
                                            @method('delete')
                                            <button class="btn btn-danger">
                                                {{ __('Remove Member') }}

                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $associated_users->links() }}
                            @else
                                <p>Missing associates</p>
                            @endif
                            <form method="post" action = "{{ route('AddMember') }}">
                                @csrf
                                @method('post')
                                <button class="btn btn-outline-info btn-block">
                                    {{ __('Add Member') }}
                                </button>
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection