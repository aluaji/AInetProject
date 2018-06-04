<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Movement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'movement_category_id', 'value', 'document_file', 'date', 'description', 'document_description'
    ];


    public function movement_category() {

        return $this->hasOne('App\MovementCategory', 'id', 'movement_category_id');
    }

    public function account() {

        return $this->belongsTo('App\Account');
    }

}

