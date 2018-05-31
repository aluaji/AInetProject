<?php

namespace App\Http\Controllers;

use App\Account;

class AccountController extends Controller
{

    public function listAccounts() {
        $accounts = Account::withTrashed()->paginate(45);
        return view('accounts.list', compact('accounts'));

    }

    public function listOpenedAccounts() {
        $accounts = Account::withTrashed()->paginate(45);
        return view('accounts.openList', compact('accounts'));

    }

    public function listClosedAccounts() {

        $accounts = Account::withTrashed()->paginate(45);
        return view('accounts.closeList', compact('accounts'));

    }


    public function closeAccount($id) {
        $account = Account::findOrFail($id);

        if($account->deleted_at == null) {
            $account->delete();
        }
        return back();

    }
    public function deleteAccount($id) {

        $account = Account::findOrFail($id);


        if($account->deleted_at == null) {
            $account->forceDelete();
        }

        return back();
    }
    public function reopenAccount($id) {

        $account = Account::withTrashed()->findOrFail($id);

        if($account->trashed()) {

            $account->restore();
        }
        return back();

    }
}
