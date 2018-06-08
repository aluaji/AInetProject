@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-20">
                <div class="card">
                    <div class="card-header">My Movements</div>
                    <div class="card-body">
                        <a  class="btn btn-success" href=" {{ route('movements.create', $account->id) }} ">
                            {{ __('Create a Movement') }}
                        </a>
                        <a class="btn btn-danger" href=" {{ route('OpenedAccounts', Auth::user()->id) }}">
                            {{ __('Back')}}

                        </a>

                        <table class="table table-striped">
                            <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Account ID</th>
                                <th>Movement Category ID</th>
                                <th>Date</th>
                                <th>Value</th>
                                <th>Start Balance</th>
                                <th>End Balance</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Associated Document</th>
                                <th>Created At</th>
                                <th></th>
                                <th><center>Actions</center></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($movements as $movement)
                                <tr>
                                    <td> {{ $movement->id }}</td>
                                    <td> {{ $movement->account_id }}</td>
                                    <td> {{ $movement->movement_category->name }}</td>
                                    <td> {{ $movement->date }}</td>
                                    <td> {{ $movement->value }}</td>
                                    <td> {{ $movement->start_balance }}</td>
                                    <td> {{ $movement->end_balance }}</td>
                                    <td> {{ $movement->description }}</td>
                                    <td> {{ $movement->type }}</td>
                                    <td>
                                        <a class="btn btn-outline-info"
                                           @if($movement->document_id != null)
                                           style="display: none;"
                                           @else
                                           href="{{ route('documents.add', ['movement'=>$movement]) }}"
                                           @endif
                                           target="_blank"
                                        >Upload Document
                                        </a>
                                        <a class="btn btn-info"
                                           @if($movement->document_id == null)
                                           style="display: none;"
                                           @else
                                           href="{{ route('documents.read', ['movement'=>$movement]) }}"
                                           @endif
                                           target="_blank"
                                        >View Document
                                        </a>
                                        <a class="btn btn-info"
                                           @if($movement->document_id == null)
                                           style="display: none;"
                                           @else
                                           href="{{ route('documents.download', ['movement'=>$movement]) }}"
                                                @endif
                                        >Download Document
                                        </a>
                                    </td>
                                    <td> {{ $movement->created_at }}</td>
                                    <td>
                                    <a class="btn btn-warning" href="{{ route('movements.edit', $movement->id) }}">
                                        {{ __('Edit Movement') }}
                                    </a>
                                    </td>
                                    <td>
                                        <form method="post" action = "{{ route('movements.delete', $movement->id) }}">
                                            @csrf
                                            @method('delete')
                                            <button class="btn btn-danger">
                                                {{ __('Delete Movement') }}

                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $movements->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection