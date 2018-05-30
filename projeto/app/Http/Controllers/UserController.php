<?php

namespace App\Http\Controllers;

use App\User;


class UserController extends Controller
{

    public function listUsers() {
        $users = User::paginate(10);
        return view('users.list', compact('users'));
    }

    public function changeUserStatus($userId) {

        $user = User::findOrFail($userId);

        if($user->blocked == 0) {
            $user->blocked = 1;
            $user->save();
        } else {
            $user->blocked = 0;
            $user->save();
        }

        return back();
    }

    public function changeUserPermissions($userId) {

        $user = User::findOrFail($userId);

        if($user->admin == 0) {
            $user->admin = 1;
            $user->save();
        } else {
            $user->admin = 0;
            $user->save();
        }

        return back();
    }


}
