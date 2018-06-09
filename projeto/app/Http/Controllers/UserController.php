<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{

    public function listUsers(Request $request)
    {
        $users = new User();
        if (!empty($request->type)) {
            $admin = $request->type === 'admin' ? 1 : ($request->type === 'normal' ? 0 : '');
            if ($admin !== '') {
                $users = $users->where('admin', $admin);
            }

        }
        if (!empty($request->status)) {
            $blocked = $request->status == 'blocked' ? 1 : ($request->status == 'unblocked' ? 0 : '');
            if ($blocked !== '') {
                $users = $users->where('blocked', $blocked);

            }
        }
        if (!empty($request->name)) {
            $users = $users->where('name', 'LIKE', "%{$request->name}%");
        }
        $users = $users->paginate(10);

        return view('users.list', compact('users'));
    }

    public function blockUser($userId)
    {
        $user = User::findOrFail($userId);

        if (!Auth::user() == $user) {
            $user->blocked = 1;
            $user->save();
        }
        return redirect(route('users.list'));
    }

    public function unblockUser($userId)
    {
        $user = User::findOrFail($userId);
        if (!Auth::user() == $user) {
            $user->blocked = 0;
            $user->save();
            return redirect(route('users.list'));
        }
        return back()->withErrors(["error", "User is already Unblocked"]);
    }

//    public function changeUserStatus($userId)
//    {
//
//        $user = User::findOrFail($userId);
//        if (Auth::user()->id != $userId) {
//            if ($user->blocked == 0) {
//                $user->blocked = 1;
//
//            } else {
//                $user->blocked = 0;
//
//            }
//
//            $user->save();
//            return redirect(route('users.list'));
//        }
//        abort(404);
//    }

    public function changeUserPermissions($userId)
    {

        $user = User::findOrFail($userId);

        if (!$user == Auth::user()) {
            if ($user->admin == 0) {
                $user->admin = 1;
            } else {
                $user->admin = 0;
            }
            $user->save();
        }

        return redirect(route('users.list'));
    }

    public function showProfile()
    {
        return view('users.userProfile');
    }

    public function changeUserPasswordView()
    {
        return view('auth.passwords.changePasswordView');
    }

    public function changeUserPassword(Request $request)
    {

        $request->validate([
            'old_password' => 'required|string|min:3',
            'password' => 'required|string|min:3|confirmed|different:old_password',
        ]);

        $user = User::findOrFail(Auth::id());

        if (Hash::check($request->old_password, $user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();
        } else {
            return redirect()->back()->withErrors(array("old_password" => "old password errada"));
        }

        return view('users.userProfile')->with('success');
    }

    public function changeUserProfile(Request $request)
    {

        if (!$request->has('phone')) {
            $request->request->add(['phone' => null]);   //caso não exista põe phone a null
        }

        $userId = Auth::id();
        $userAux = User::findOrFail($userId);


        if (!$request->email === $userAux->email) { //=== para verificar se tem mesmo tipo
            $userAux = $request->validate([
                'name' => 'required|regex:/^[\pL\s]+$/u',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'nullable|regex:/^[0-9+\s]+$/',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg'
            ]);
        } else {
            $userAux = $request->validate([
                'name' => 'required|regex:/^[\pL\s]+$/u',
                'phone' => 'nullable|regex:/^[0-9+\s]+$/',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg'
            ]);

        }


        if ($request->hasFile('profile_photo')) {
            $img = $request->file('profile_photo');
            $imgName = $img->hashName();
            $userAux['profile_photo'] = $imgName;
            Storage::disk('public')->putFile('profiles', $img);
        }


        //Criação de Modelo
        $userModel = User::findOrFail($userId);
        $userModel->fill($userAux);
        $userModel->save();

        return view('users.userProfile')->with('success');

    }

    public function ViewUserProfiles(Request $request)
    {
        $users = new User();

        if (!empty($request->name)) {
            $users = $users->where('name', 'LIKE', "%{$request->name}%");
        }
        $associated_users = Auth::user()->associateMembers;
        $associated_to = Auth::user()->userBelongTo;
        $users = $users->paginate(10);
        return view('users.listUserProfiles', compact('users', 'associated_to', 'associated_users'));
    }


}
