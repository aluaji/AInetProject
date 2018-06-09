<?php

namespace App\Http\Controllers;

use App\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;


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

    public function editAccount($id) {

        $account = Account::findOrFail($id);
        return view('accounts.editAccount', compact('account'));

    }

    public function updateAccount(Request $request, $id) {

        $account = Account::findOrFail($id);

        $account->account_type_id = $request->input('account_type_id');
        $account->code = $request->input('code');
        $account->start_balance = $request->input('start_balance');
        $account->description = $request->input('description');


        $account->save();
        return Redirect::route('AllAccounts',
            array(Auth::user()->id))
            ->with('message', 'Your account has been edited!');

    }

    public function getUserAccountsBalance($user) {
        $total_balance = 0;
        foreach (Account::where('owner_id', $user)->get() as $account) {
            $total_balance += $account->current_balance;
        }
        return $total_balance;
    }
}
