@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Upload File</div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            File:
                            <br />
                            <input type="file" name="file" />
                            <br /><br />
                            Description::
                            <br />
                            <input type="text" name="description" />
                            <br /><br />
                            <input type="submit" value=" Save " />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection