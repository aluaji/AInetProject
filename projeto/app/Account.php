<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Account extends Model
{
    use SoftDeletes;
    use Notifiable;

    public $timestamps = false;

    protected $fillable = [
        'account_type_id', 'code', 'start_balance', 'date', 'description'
    ];


    public function account_type() {

        return $this->hasOne('App\AccountType', 'id', 'account_type_id');
    }

}
