<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;


class Movement extends Model
{
    public function document() {
        return $this->hasMany('App\Document');
    }

    public function account() {
        return $this->belongsTo('App\Account');
    }
}

