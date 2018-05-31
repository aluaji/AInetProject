<?php

namespace App\Http\Controllers;

use App\Account;
use App\Movement;
use Illuminate\Support\Facades\Storage;
use App\Document;

class DocumentController extends Controller
{
    public function getDocument($document) {
        return Storage::download($this->getOwnerId($document) . '/' .
            $this->getOwnerId($document) . explode('.', $this->getName($document))[1]);
    }

    public function getName($document) {
        $document_object = Document::findOrFail($document);
        return $document_object->original_name;
    }

    public function getOwnerId($document) {
        $movement = Movement::where('document_id', $document)->get();
        $account_id = Movement::find(1)->account_id;
        $account = Account::where('id', $account_id)->get();
        return Account::find(1)->owner_id;
    }
}
