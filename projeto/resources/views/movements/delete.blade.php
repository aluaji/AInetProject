@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Delete File</div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" action="{{ route('document.delete', $document)  }}">
                            @csrf
                            @method('DELETE')
                            This will erase the file {{ $document->original_name }}
                            <br />
                            Are you sure?
                            <input type="submit" value="Yes" href="{{ 'document.delete', $document }}"/>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection