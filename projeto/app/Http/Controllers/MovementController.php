<?php

namespace App\Http\Controllers;

use App\Movement;
use App\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class MovementController extends Controller
{



    public function listMovements($account_id) {

        $account = Account::findOrFail($account_id);
        $movements = Movement::where('account_id', $account_id)->paginate(10);

        return view('movements.list', compact('movements', 'account'));
    }

    public function createMovement($id) {

        $account = Account::findOrFail($id);
        return view('movements.createMovement', compact('account'));
    }

    public function storeMovement(Request $request, $account_id) {

        $movement = new Movement;
        $account = Account::findOrFail($account_id);

        $movement->account_id = $account_id;


        $movement->type = $request->type;
        $movement->movement_category_id = $request->movement_category_id;
        $movement->value = $request->value;
//        $movement->document_file = $request->document_file;
//        $movement->document_description = $request->document_description;
        $movement->date = $request->date;
        $movement->description = $request->description;
        $movement->start_balance = $account->current_balance;
        $movement->end_balance = $movement->value + $movement->start_balance;
        $account->current_balance = $movement->end_balance;

        $movement->save();
        $account->save();

        return Redirect::route('movements.list',
            array($account->id))
            ->with('message', 'Your movement has been created!');

    }

}
