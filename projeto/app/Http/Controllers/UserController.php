<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{

    public function welcomePage() {
        $number_of_rows = User::get()->count();
        return view('welcome', compact('number_of_rows'));
    }
}
