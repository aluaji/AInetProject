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
                        <form method="POST" action="{{ route('user.view.change.profile') }}"
                              enctype="multipart/form-data">
                            @method('put')
                            @csrf
                            <div class="form-group col-md-6">
                                <label for="usr">Name:</label>
                                <input type="text" style="width:450px;" name="name"
                                       class="form-control" id="usr" value="{{ Auth::user()->name }}">
                            </div>

                            <!-- EMAIL -->
                            <div class="form-group col-md-6">
                                <label for="usr">Email:</label>
                                <input type="text" style="width:450px;" class="form-control" id="usr"
                                       value="{{Auth::user()->email}}">

                            </div>
                            <div class="form-group col-md-6">
                                <label for="usr">Phone Number:</label>
                                <input type="text" style="width:450px;" class="form-control" id="usr"
                                       value="{{Auth::user()->phone}}">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="usr">Profile Photo:</label>
                                <br>
                                <img src="{{ Storage::url('public/profiles/' . Auth::user()->profile_photo)}}"
                                     style="width:150px; height: 150px;" class="img-thumbnail">
                            </div>
                            <div class="col-md-4">
                                <input id="profile_photo" type="file" class="custom-file-input" name="profile_photo">
                                <label class="custom-file-label" for="profile_photo"
                                       class="col-md-3 col-form-label text-md-right">New Photo</label>

                                @if ($errors->has('profile_photo'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('profile_photo') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <br>
                            <div class="form-group col-md-12">
                                <button type="submit" class="btn btn-dark" href="{{route('user.view.change.profile')}}">
                                    Edit
                                    Profile
                                </button>
                                <button class="btn btn-dark" href="{{route('user.view.change.password')}}">Change
                                    Password
                                </button>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection