<?php

namespace App\Http\Controllers;

use App\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{

    public function listAccounts() {
        $accounts = Account::paginate(10);
        return view('accounts.list', compact('accounts'));
    }
}
