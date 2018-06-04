<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    public function movement() {
        return $this->belongsTo('App\Movement');
    }
}
