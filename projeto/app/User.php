<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'profile_photo'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    public function associateMembers()
    {
        return $this->belongsToMany('App\User', 'associate_members', 'main_user_id', 'associated_user_id');
    }

    public function userBelongTo()
    {
        return $this->belongsToMany('App\User', 'associate_members', 'associated_user_id', 'main_user_id');
    }

}
