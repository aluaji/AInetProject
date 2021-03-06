<?php

namespace App\Http\Controllers;


use App\Account;
use App\AccountType;
use App\User;
use Illuminate\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;


class AccountController extends Controller
{

    public function listAccounts($userId)
    {

        $user = User::findOrFail($userId);
        $accounts = Account::withTrashed()->where('owner_id', $user->id)->paginate(10);

        return view('accounts.list', compact('accounts', 'user'));
    }

    public function listOpenedAccounts($userId)
    {

        $user = User::findOrFail($userId);
        $accounts = Account::withTrashed()->where('owner_id', $user->id)->paginate(10);
        return view('accounts.openList', compact('accounts', 'user'));

    }

    public function listClosedAccounts($userId)
    {

        $user = User::findOrFail($userId);
        $accounts = Account::withTrashed()->where('owner_id', $user->id)->paginate(10);
        return view('accounts.closeList', compact('accounts', 'user'));

    }


    public function closeAccount($id)
    {
        $account = Account::findOrFail($id);

        if ($account->owner_id == Auth::user()->id) {
            if ($account->deleted_at == null) {
                $account->delete();
            }
        }
        return back();

    }

    public function deleteAccount($id)
    {

        $account = Account::findOrFail($id);
        if ($account ->movement()->count() == 0) {
                if ($account->deleted_at == null) {
                    $account->forceDelete();
                }
        }
        return back();
    }

    public function reopenAccount($id)
    {

        $account = Account::withTrashed()->findOrFail($id);

        if (Auth::user()->id == $account->owner_id) {
            if ($account->trashed()) {
                $account->restore();
            }

        }
        return redirect()->back();
    }

    public function createAccount()
    {

        $account_type = AccountType::all();
        return view('accounts.createAccount', compact('account_type'));
    }

    public function storeAccount(Request $request)
    {

        $request->validate([
            'account_type_id' => 'required',
            'date' => 'date',
            'code' => 'required|unique:accounts',
            'start_balance' => 'required|numeric',
            'description' => 'nullable|string',
        ], [
            'account_type_id.required' => 'An account type is required to proceed.',
            'code.required' => 'A code is required to proceed',
            'code.unique' => 'This code already exists',
            'start_balance.required' => 'A start balance is required to proceed'
        ]);


        $account = new Account();
        $account->account_type_id = $request->account_type_id;
        $account->owner_id = Auth::user()->id;
        $account->code = $request->code;
        $account->start_balance = $request->start_balance;
        $account->date = $request->date;
        $account->description = $request->description;

        $account_types = AccountType::all();  //vai buscar todos os tipos

        $account_type = true;
        foreach ($account_types as $type) {
            if ($type->id == $request->account_type_id) {
                $account_type = false;
            }
        }

        if ($account_type == true) {
            $errors['account_type_id'] = 'Account Type is not valid';
            $request->session()->put('errors', 'Account type is not');
            return redirect()->back()->withErrors($errors);
        }


        $account->save();


        return Redirect::route('AllAccounts',
            array($account->owner_id))
            ->with('message', 'Your account has been created!');


    }

    public function editAccount($id)
    {

        $account = Account::findOrFail($id);
        return view('accounts.editAccount', compact('account'));

    }

    public function updateAccount(Request $request, $id)
    {

        $request->validate([
            'account_type_id' => 'required',
            'code' => 'required|unique:accounts',
            'start_balance' => 'required|numeric',
            'description' => 'nullable|string',
        ], [
            'account_type_id.required' => 'An account type is required to proceed.',
            'code.required' => 'A code is required to proceed',
            'code.unique' => 'This code already exists',
            'start_balance.required' => 'A start balance is required to proceed'
        ]);

        $account = Account::findOrFail($id);
        $movements = $account->movement;




        $account->account_type_id = $request->account_type_id;
        $account->code = $request->code;
        $account->start_balance = $request->start_balance;
        $account->description = $request->description;


        if ($request->start_balance != $account->start_balance) {

            foreach ($movements as $movement) {
                $movement->start_balance += $request->start_balance - $account->start_balance;
                $movement->end_balance = $movement->start_balance + $movement->value;
                $account->current_balance = $movement->end_balance;

                $movement->save();
            }

        }

        $account->save();
        return Redirect::route('AllAccounts',
            array(Auth::user()->id))
            ->with('message', 'Your account has been edited!');

    }

    public function getUserAccountsBalance($user)
    {
        $total_balance = 0;
        foreach (Account::where('owner_id', $user)->get() as $account) {
            $total_balance += $account->current_balance;
        }
        return $total_balance;
    }
}
