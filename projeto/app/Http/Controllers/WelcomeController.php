<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Movement;
use App\Account;
class WelcomeController extends Controller
{

    public function welcomePageCounter() {
        $number_of_users = User::get()->count();
        $number_of_movements = Movement::get()->count();
        $number_of_accounts = Account::get()->count();

        return view('welcome', compact('number_of_users', 'number_of_movements', 'number_of_accounts'));
    }
}
