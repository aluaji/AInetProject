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
Route::get('/accounts/{user}', 'AccountController@create')->name('AllAccounts');

Route::get('/list', 'UserController@listUsers')->name('users.list')->middleware('admin');



Route::get('/uploadfile','UploadFileController@index');
Route::post('/uploadfile','UploadFileController@showUploadFile');
Route::get('/form',function(){
    return view('form');
});
Route::get('/accounts', 'AccountController@listAccounts')->name('accounts.list');