@extends("layouts.app")

@section('content')
    <div class="container">
        <div class="card-header">Creating a Movement</div>
        <form method="post" action="{{ route('movements.store', $account->id) }}">
            @csrf
            <div class="form-group">
                <label for="type">Movement Type:</label>
                <select class="form-control" id="type" name="type" value="{{ old('type') }}">
                    <option value="0">revenue</option>
                    <option value="1">expense</option>
                </select>
            </div>
            <div class="form-group">
                <label for="date"> Date:</label>
                <input type="date" class="form-control" id="date" placeholder="Insert Date" name="date" value="{{ old('date') }}">
            </div>
            <div class="form-group">
                <label for="movement_category_id"> Category:</label>
                <select class="form-control" id="movement_category_id" placeholder="Choose Category" name="movement_category_id" value="{{ old('movement_category_id') }}">
                    @foreach($movement_type as $type)
                        <option value="{{$type->id}}">{{$type->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="value"> Value:</label>
                <input type="text" class="form-control" id="value" placeholder="Insert Value" name="value" value="{{ old('value') }}">
            </div>
            <button type="submit" class="btn btn-success">
                {{ __('Submit') }}

            </button>
            <a class="btn btn-danger" href="{{ route('movements.list', $account->id) }}">  {{ __('Cancel') }}</a>
        </form>
    </div>
@endsection