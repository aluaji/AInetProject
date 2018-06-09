<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

//        Gate::define('view-account', function($user, $account_id) {
//                return $user->id == $this->accountOwnerId($account_id);
//        });
//
//        Gate::define('list_accounts', function ($user, $otherUser) {
//            return $user->userBelongTo()->get()->contains($otherUser) || $user->id == $otherUser->id;
//        });

//        Gate::define('delete_account','AccountController@deleteAccount');
    }
}
