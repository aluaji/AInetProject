<?php

namespace App\Http\Controllers;

use App\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{

    public function listAccounts() {
        $accounts = Account::paginate(45);

        return view('accounts.list', compact('accounts'));

    }

    public function listOpenedAccounts() {
        $accounts = Account::paginate(45);
        return view('accounts.openList', compact('accounts'));

    }



    public function openAccount() {
    }
    public function closeAccount() {
    }
    public function reopenAccount() {
    }
}
