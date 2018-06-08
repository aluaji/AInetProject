<?php

namespace App\Http\Controllers;

use App\Account;
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

    private function getDocumentPath($document) {
        return $document->movement->account_id . '/' . $document->movement->id . '.' . $document->type;
    }

    public function uploadForm($id) {
        $movement = $this->getMovement($id);
        return view('movements.upload', compact('movement'));
    }
    public function addDocument(Request $request, $id) {
        $movement = $this->getMovement($id);
        $path = null;
        if(Input::hasFile('document_file')){
            if(Input::file('document_file')->isValid()){
                $path = $request->file('document_file')->storeAs(("documents/"  . $movement->account_id), $movement->id . '.' . $request->file('document_file')->extension());
            }
        }

        $document = new Document;

        $document->type = $request->document_file->extension();
        $document->original_name = $movement->id . '.' . $request->document_file->extension();
        $document->description = $request->document_description;

        $document->save();

        $movement->document_id = $document->id;

        $movement->save();

        return redirect()->route('movements.list', $movement->account_id);
    }

    public function deleteForm($document) {

    }

    public function deleteDocument($document) {
        $movement = $this->getMovement($document->movement->id);

        $movement->document_id = null;

        $movement->save();

        unlink('app/documents/' . $this->getDocumentPath($document));

        $document->delete();

        return redirect()->route('movements.list', $movement->account_id);
    }

//    public function downloadDocument($document_id) {
//        return Storage::download($this->getDocumentPath($document_id));
//    }

    public function getDocument($document_id) {
        $document = Document::findOrFail($document_id);
        return response()->file(storage_path('app/documents/' . $this->getDocumentPath($document)));

    }
}
