<?php

namespace App\Http\Controllers;

use App\Movement;
use Illuminate\Support\Facades\Storage;
use App\Document;

class DocumentController extends Controller
{

    public function getMovement($movement) {
        return Movement::findOrFail($movement);
    }
    private function getDocumentPath($movement) {
        $movement = $this->getMovement($movement);
        $document_id = $movement->document_id;
        $document = Document::findOrFail($document_id);
        return 'documents/' . $movement->account_id . '/' . $movement->id . '.' . $document->type;
    }

    public function downloadDocument($movement) {
        return Storage::download($this->getDocumentPath($movement));
    }

    public function readDocument($movement) {
        return response()->file(storage_path('app/' . $this->getDocumentPath($movement)));
    }
//
    public function addDocument($movement) {

//        $path = $request->file('avatar')->store('avatars');
    }

    public function removeDocument($movement) {

    }

    public function uploadForm() {
        return view('movements.upload', compact('movements'));
//        Form::file('thefile');
//        return view('movements.upload', compact('movements'));
    }
}
