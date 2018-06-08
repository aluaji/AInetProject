<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', 'WelcomeController@welcomePageCounter')->name('welcome');

Auth::routes();

Route::middleware(['auth'])->group(function() {
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/me', 'UserController@showProfile')->name('users.showProfille');
    Route::put('/me/profile', 'UserController@changeUserProfile')->name('user.view.change.profile');
    Route::get('/me/changePassword', 'UserController@changeUserPasswordView')->name('user.view.change.password');
    Route::patch('/me/password', 'UserController@changeUserPassword')->name('users.changePassword');
    Route::get('/profiles', 'UserController@ViewUserProfiles')->name('users.profiles');
    Route::get('/me/associates', 'AssociateMembersController@ViewAssociatedUser')->name('MemberList');
    Route::get('/accounts/{user}', 'AccountController@listAccounts')->name('AllAccounts');
    Route::get('/account/{user}/opened', 'AccountController@listOpenedAccounts')->name('OpenedAccounts');
    Route::get('/account/{user}/closed', 'AccountController@listClosedAccounts')->name('ClosedAccounts');
    Route::delete('/account/{account}', 'AccountController@deleteAccount')->name('users.account.delete');
    Route::patch('/account/{account}/close', 'AccountController@closeAccount')->name('users.account.close');
    Route::patch('/account/{account}/reopen', 'AccountController@reopenAccount')->name('users.account.reopen');
    Route::get('/account', 'AccountController@createAccount')->name('users.account.create');
    Route::post('/account', 'AccountController@storeAccount')->name('users.account.store');
    Route::get('/account/{account}', 'AccountController@editAccount')->name('users.account.edit');
    Route::put('/account/{account}', 'AccountController@updateAccount')->name('users.account.update');
    Route::get('/movements/{account}', 'MovementController@listMovements')->name('movements.list');
    Route::get('/movements/{account}/create', 'MovementController@createMovement')->name('movements.create');
    Route::post('/movements/{account}/create', 'MovementController@storeMovement')->name('movements.store');
    Route::get('/movement/{movement}', 'MovementController@editMovement')->name('movements.edit');
    Route::put('/movement/{movement}', 'MovementController@updateMovement')->name('movements.update');
    Route::delete('/movement/{movement}', 'MovementController@deleteMovement')->name('movements.delete');

});
Route::middleware(['auth', 'admin'])->group(function() {
    Route::get('/users', 'UserController@listUsers')->name('users.list');
    Route::PATCH('/users/{user}/block', 'UserController@blockUser')->name('users.block');
    Route::PATCH('/users/{user}/unblock', 'UserController@unblockUser')->name('users.unblock');
    Route::PATCH('/users/{user}/promote', 'UserController@changeUserPermissions')->name('users.promote');
    Route::PATCH('/users/{user}/demote', 'UserController@changeUserPermissions')->name('users.demote');
});

Route::get('/me/associate-of', 'AssociateMembersController@viewMembersOtherGroups')->name('OtherMemberList');



Route::get('/uploadfile','UploadFileController@index');
Route::post('/uploadfile','UploadFileController@showUploadFile');
Route::get('/form',function(){
    return view('form');
});

Route::get('/documents/{movement}/add', 'DocumentController@uploadForm')->name('documents.add');
Route::get('/documents/{document}/read', 'DocumentController@readDocument')->name('documents.read');
Route::get('/documents/{document}/download', 'DocumentController@downloadDocument')->name('documents.download');
