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

    public function returnDocument($movement) {
        return Document::findOrFail($movement->document_id);
    }

    private function getDocumentPath($document) {
        return $document->movement->account_id . '/' . $document->movement->id . '.' . $document->type;
    }

    public function uploadForm($id) {
        $movement = $this->getMovement($id);
        return view('movements.upload', compact('movement'));
    }
    public function addDocument(Request $request, $id) {
        $request->validate([
            'document_file'         => 'required|mimes:jpeg,png,pdf',
            'document_description'  => 'required|string',
        ]);

        $movement = $this->getMovement($id);
        $path = null;
        if($request->hasFile('document_file')){
            if($request->file('document_file')->isValid()) {
               $request->file('document_file')->storeAs("documents/"  . $movement->account_id, $movement->id . '.' . $request->file('document_file')->extension());
            }
        }

        $document = new Document;

        $document->type = $request->document_file->extension();
        $document->original_name = $request->document_file->getClientOriginalName();
        $document->description = $request->document_description;

        $document->save();
        $movement->document_id = $document->id;

        $movement->save();

        return redirect()->route('movements.list', $movement->account_id);
    }

    public function deleteDocument($movement_id) {

//        $movement = $this->getMovement($document->movement->id);

        $document = $this->returnDocument($this->getMovement($movement_id));
        $movement = $this->getMovement($movement_id);

        unlink(storage_path('app/documents/' . $this->getDocumentPath($document)));

        $movement->document_id = null;

        $movement->save();

        return redirect()->route('movements.list', $movement->account_id);
    }

    public function getDocument($document_id) {
        $document = Document::findOrFail($document_id);
        return response()->download(storage_path('app/documents/' . $this->getDocumentPath($document)));
//        return response()->file(storage_path('app/documents/' . $this->getDocumentPath($document)));
    }
}
