<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;

class AssociateMembersController extends Controller
{

    public function ViewAssociatedUser()
    {
        $associated_users = Auth::user()->associateMembers()->paginate(10); //paginate query builder
        return view('users.listAssociatedMembers', compact('associated_users'));
    }

    public function viewMembersOtherGroups()
    {
        $associate_to = Auth::user()->userBelongTo()->paginate(10); //paginate query builder
        return view('users.listAssociateTo', compact('associate_to'));
    }

    public function addAssociatedMember() {
        $users = User::all();
        return view('users.addAssociatedMember', compact('users'));
    }

    public function deleteAssociatedMember($userId) {
//        dd($associated_member);

        if(!Auth::user()->associateMembers()->detach($userId))
            abort(404);

        $associated_users = Auth::user()->associateMembers()->paginate(10); //paginate query builder
        return view('users.listAssociatedMembers', compact('associated_users'));
    }
}
