<?php

namespace App\Http\Controllers;

use App\Account;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index() {
        return view('dashboard.home');
    }

    public function getAccounts() {
        return $accounts = Account::withTrashed()->paginate(45);
    }

    public function returnRelativeWeight($totalBalance, $account) {
        if(!isset($totalBalance) || is_null($totalBalance))
            return "ERROR";
        return round((abs($account->current_balance) / abs($totalBalance)) * 100, 2);
    }

}
