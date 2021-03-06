@extends("layouts.app")

@section("content")

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Upload File</div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" action="{{ route('documents.add', $movement->id)  }}">
                            @csrf
                            @method('POST')
                            File:
                            <br />
                            <input type="file" name="document_file"/>
                            <br /><br />
                            Description:
                            <br />
                            <input type="text" name="document_description" />
                            <br /><br />
                            <input type="submit" value=" Upload " href="{{ 'documents.add', $movement->id }}"/>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection