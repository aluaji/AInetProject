<?php

namespace App\Http\Controllers;

use App\Document;
use App\Movement;
use App\Account;
use App\MovementCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class MovementController extends Controller
{



    public function listMovements($account_id) {

        $account = Account::findOrFail($account_id);
        $movements = Movement::where('account_id', $account_id)->orderBy('date', 'desc')->paginate(10);

        return view('movements.list', compact('movements', 'account'));
    }

    public function createMovement($id) {

        $account = Account::findOrFail($id);
        $movement_type = MovementCategory::all();
        return view('movements.createMovement', compact('account', 'movement_type'));
    }

    public function storeMovement(Request $request, $account_id) {

        $movement = new Movement;
        $account = Account::findOrFail($account_id);

        $request->validate([
            'type' => 'required',
            'date' => 'date',
            'movement_category_id' => 'required',
            'value' => 'required|numeric',
            'description' => 'nullable|string',

        ], [
            'type.required' => 'A type is required to proceed.',
            'movement_category_id.required' => 'A movement category id is required to proceed',
            'value.required' => 'A value needs to be written',
        ]);


        $movement->account_id = $account_id;


        $movement->type = $request->type;
        $movement->movement_category_id = $request->movement_category_id;
        $movement->value = $request->value;
        $movement->date = $request->date;
        $movement->description = $request->description;
        $movement->start_balance = $account->current_balance;


        if($request->type == 0){

            $movement->type = "revenue";
            $value= $movement->value;
            $movement->end_balance = $value + $movement->start_balance;

        } else {
            $movement->type = "expense";
            $value = $movement->value;
            $movement->end_balance = -$value + $movement->start_balance;
        }

        $account->current_balance = $movement->end_balance;

        $movement->save();
        $account->save();

        return Redirect::route('movements.list',
            array($account->id))
            ->with('message', 'Your movement has been created!');


    }

    public function editMovement($id) {

        $movement = Movement::findOrFail($id);
        $movement_category = MovementCategory::all();
        return view('movements.editMovement', compact('movement', 'movement_category'));
    }

    public function updateMovement(Request $request, $id) {

        $movement = Movement::findOrFail($id);
        $account = $movement->account;

        $request->validate([
            'date' => 'required|date',
            'movement_category_id' => 'required',
            'value' => 'required|numeric',
            'description' => 'nullable|string',

        ], [
            'movement_category_id.required' => 'A movement category id is required to proceed',
            'date.required' => 'A date needs to be choosen',
            'value.required' => 'A value needs to be written',
        ]);

        $movement->movement_category_id = $request->movement_category_id;
        $movement->date = $request->date;
        $movement->value = $request->value;
        $movement->description = $request->description;

        $movement->start_balance = $account->current_balance;

        if($request->type == 0){

            $movement->type = "revenue";
            $value= $movement->value;
            $movement->end_balance = $value + $movement->start_balance;

        } else {
            $movement->type = "expense";
            $value = $movement->value;
            $movement->end_balance = -$value + $movement->start_balance;
        }
        $account->current_balance = $movement->end_balance;


        $movement->save();
        $account->save();

        return Redirect::route('movements.list',
            array($account->id))
            ->with('message', 'Your account has been edited!');


    }

    public function deleteMovement(Request $request, $id) {

        $movement = Movement::findOrFail($id);
        $account = $movement->account;

        $movement->forceDelete();
        $this->recalculate($request);
        $account->current_balance = $movement->start_balance;
        $account->save();

        return Redirect::route('movements.list',
            array($account->id))
            ->with('message', 'Your account has been edited!');

    }

    public function recalculate(Request $request) {

        $movements = Movement::all();


        foreach($movements as $movement) {

            $nextDate = $movement->date->next();
            if($movement->date < $nextDate) {
                if($request->type == 0){

                    $movement->next()->type = "revenue";
                    $value= $movement->next()->value;
                    $movement->next()->end_balance = $value + $movement->next()->start_balance;

                } else {
                    $movement->next()->type = "expense";
                    $value = $movement->next()->value;
                    $movement->next()->end_balance = -$value + $movement->next()->start_balance;
                }

            }
        }

    }

}
