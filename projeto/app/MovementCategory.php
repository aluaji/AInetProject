<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MovementCategory extends Model
{
    public function movement() {

        return $this->belongsTo('App\Movement');
    }
}