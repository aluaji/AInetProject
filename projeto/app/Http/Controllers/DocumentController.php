<?php

namespace App\Http\Controllers;

use App\Movement;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use App\Document;
use Illuminate\Http\Request;

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

    private function createDocumentPath($movement) {
        $movement = $this->getMovement($movement);
        return 'documents/' . $movement->account_id;
    }

    public function downloadDocument($movement) {
        return Storage::download($this->getDocumentPath($movement));
    }

    public function readDocument($movement) {
        return response()->file(storage_path('app/' . $this->getDocumentPath($movement)));
    }

    public function addDocument(Request $request, $id) {

        $path = null;
        if(Input::hasFile('document_file')){
            if(Input::file('document_file')->isValid()){
                $request->file('document_file')->storeAs($this->createDocumentPath($id)."",
                    $id . '.' . $request->document_file->extension());
            }
        }

        $document = new Document;

        $document->type = $request->document_file->extension();
        $document->original_name = $id;
        $document->description = $request->document_description;

        $document->save();

        $movement = $this->getMovement($id);

        $movement->document_id = $document->id;

        $movement->save();

        return redirect()->route('movements.list', $this->getMovement($id)->account_id);
    }

    public function removeDocument($movement) {

    }

    public function uploadForm($id) {
        $movement = $this->getMovement($id);
        return view('movements.upload', compact('movement'));
    }
}
