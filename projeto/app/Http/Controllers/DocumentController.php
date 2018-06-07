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

    private function getDocumentPath($document_id) {
        $document = Document::findOrFail($document_id);

        $movement = Movement::findOrFail($document->original_name);
        return 'documents/' . $movement->account_id . '/' . $movement->id . '.' . $document->type;
    }

    private function createDocumentPath($movement) {
        $movement = $this->getMovement($movement);
        return 'documents/' . $movement->account_id;
    }

    public function uploadForm($id) {
        $movement = $this->getMovement($id);
        return view('movements.upload', compact('movement'));
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

    public function deleteDocument($movement) {

    }



    public function downloadDocument($document_id) {
        return Storage::download($this->getDocumentPath($document_id));
    }

    public function readDocument($document_id) {
        return response()->file(storage_path('app/' . $this->getDocumentPath($document_id)));
    }

    public function getDocument($document_id) {


        Storage::download($this->getDocumentPath($document_id));
        return response()->file(storage_path('app/' . $this->getDocumentPath($document_id)));
    }
}
