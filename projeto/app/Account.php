<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    public $timestamps = false;


    public function account_type() {



        return $this->hasOne('App\AccountType', 'id', 'account_type_id');


    }
}
