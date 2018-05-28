<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    private $open = array();
    private $closed = array();
}
