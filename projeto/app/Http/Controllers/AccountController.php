<?php

namespace App\Http\Controllers;

use App\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;


class AccountController extends Controller
{

    public function listAccounts() {
        $accounts = Account::withTrashed()->paginate(50);
        return view('accounts.list', compact('accounts'));

    }

    public function listOpenedAccounts() {
        $accounts = Account::withTrashed()->paginate(50);
        return view('accounts.openList', compact('accounts'));

    }

    public function listClosedAccounts() {

        $accounts = Account::withTrashed()->paginate(50);
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

    public function createAccount() {

        return view('accounts.createAccount');
    }

    public function storeAccount(Request $request)
    {
        $account = new Account;


        $account->account_type_id= $request->account_type_id;
        $account->owner_id = Auth::user()->id;
        $account->code = $request->code;
        $account->start_balance = $request->start_balance;
        $account->date = $request->date;
        $account->description = $request->description;

        $account->save();

        return Redirect::route('AllAccounts',
            array($account->id))
            ->with('message', 'Your account has been created!');


    }
}
