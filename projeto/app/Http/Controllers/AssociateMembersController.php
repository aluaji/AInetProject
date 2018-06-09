<?php

namespace App\Http\Controllers;

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

}
