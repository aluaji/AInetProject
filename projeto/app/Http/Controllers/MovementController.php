<?php

namespace App\Http\Controllers;

use App\Movement;

class MovementController extends Controller
{

    public function listMovements() {
        $movements = Movement::paginate(10);
        return view('movements.list', compact('movements'));
    }

}
