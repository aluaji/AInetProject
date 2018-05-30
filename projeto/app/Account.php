<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    private $accountOpened = array('Opened');
    private $accountClosed = array('Closed');
}
