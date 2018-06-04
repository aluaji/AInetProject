@extends("layouts.app")

@section('content')
    <div class="container">
        <div class="card-header">Creating a Movement</div>
        <form method="post" action="{{ route('movements.store', $account->id) }}">
            @csrf
            <div class="form-group">
                <label for="type">Movement Type:</label>
                <select class="form-control" id="type" name="type" value="{{ old('type') }}">
                    <option>revenue</option>
                    <option>expense</option>
                </select>
            </div>
            <div class="form-group">
                <label for="date"> Date:</label>
                <input type="date" class="form-control" id="date" placeholder="Insert Date" name="date" value="{{ old('date') }}">
            </div>
            <div class="form-group">
                <label for="movement_category_id"> Category:</label>
                <select class="form-control" id="movement_category_id" placeholder="Choose Category" name="movement_category_id" value="{{ old('movement_category_id') }}">
                    <option>1</option>
                    <option>2</option>
                    <option>3</option>
                    <option>4</option>
                    <option>5</option>
                    <option>6</option>
                    <option>7</option>
                    <option>8</option>
                    <option>9</option>
                    <option>10</option>
                    <option>11</option>
                    <option>12</option>
                    <option>13</option>
                    <option>14</option>
                    <option>15</option>
                    <option>16</option>
                    <option>17</option>
                    <option>18</option>
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