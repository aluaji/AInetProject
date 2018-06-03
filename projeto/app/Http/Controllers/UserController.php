<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{

    public function listUsers(Request $request) {

        $users = new User();
        if(!empty($request->type))
        {
            $admin = $request->type == 'admin' ? 1 : ($request->type == 'normal' ? 0 : null);
            $users = $users->where('admin', $admin);

        }
        if(!empty($request->status)) {
            $blocked = $request->status == 'blocked' ? 1 : ($request->status == 'unblocked' ? 0 : null);
            $users = $users->where('blocked', $blocked);
        }
        if(!empty($request->name)) {
            $users = $users->where('name', 'LIKE', "%{$request->name}%");
        }

        $users = $users->paginate(10);

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

    public function changeUserPasswords() {

    }

}
