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
Route::get('/home', 'HomeController@index')->name('home');

Route::get('/me/associates', 'AssociateMembersController@viewMembersMyGroup')->name('MemberList');
Route::get('/me/associate-of', 'AssociateMembersController@viewMembersOtherGroups')->name('OtherMemberList');

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

Route::get('/list', 'UserController@listUsers')->name('users.list')->middleware('admin');

Route::get('/users', 'UserController@listUsers')->name('users.list')->middleware('admin');

Route::PATCH('/users/{user}/block', 'UserController@changeUserStatus')->name('users.block')->middleware('admin');
Route::PATCH('/users/{user}/unblock', 'UserController@changeUserStatus')->name('users.unblock')->middleware('admin');
Route::PATCH('/users/{user}/promote', 'UserController@changeUserPermissions')->name('users.promote')->middleware('admin');
Route::PATCH('/users/{user}/demote', 'UserController@changeUserPermissions')->name('users.demote')->middleware('admin');

Route::get('/me', function () {
    return view('views.auth.passwords.changePasswordView');
})->name('users.changePasswordView');

Route::get('/uploadfile','UploadFileController@index');
Route::post('/uploadfile','UploadFileController@showUploadFile');
Route::get('/form',function(){
    return view('form');
});

Route::get('/documents/{movement}/upload', 'DocumentController@uploadForm')->name('documents.upload');
Route::post('/documents/{movement}/add', 'DocumentController@addDocument')->name('documents.add');
Route::get('/documents/{document}/read', 'DocumentController@readDocument')->name('documents.read');
Route::get('/documents/{document}/download', 'DocumentController@downloadDocument')->name('documents.download');

Route::get('/dashboard/{user}', 'DashboardController@index')->name('dashboard');
