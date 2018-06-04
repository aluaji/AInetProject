@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        My Profile
                    </div>
                        <div class="card-body">
                            <div class="form-group col-md-6">
                                <label for="usr">Name:</label>
                                <input type="text" style="width:450px;"
                                       class="form-control" id="usr" disabled value="{{Auth::user()->name}}">

                                <span class="input-group-btn">

                                </span>
                            </div>

                            <!-- EMAIL -->
                            <div class="form-group col-md-6">
                                <label for="usr">Email:</label>
                                <input type="text" style="width:450px;" class="form-control" id="usr" disabled value="{{Auth::user()->email}}">

                            @if(Auth::user()->phone != null)
                                    <label for="usr">Name:</label>
                                    <input type="text" style="width:450px;" class="form-control" id="usr" disabled value="{{Auth::user()->phone}}">

                            @else
                                    <label for="usr">Name:</label>
                                    <input type="text" style="width:450px;" class="form-control" id="usr" disabled value="No Phone Number">

                            @endif
                                <label for="usr">Profile Photo:</label>
                                <br>
                                <img src="{{ Storage::url('public/profiles/' . Auth::user()->profile_photo)}}"
                                     style="width:150px; height: 150px;" class="img-thumbnail">
                            </div>
                            <div class="form-group col-md-6">
                                <a type="button" class="btn btn-warning" href="{{route('user.view.change.password')}}">Change Password</a>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection