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


Route::get('/users', 'UserController@listUsers')->name('users.list')->middleware('admin');

Route::PATCH('/users/{user}/block', 'UserController@changeUserStatus')->name('users.block')->middleware('admin');
Route::PATCH('/users/{user}/unblock', 'UserController@changeUserStatus')->name('users.unblock')->middleware('admin');
Route::PATCH('/users/{user}/promote', 'UserController@changeUserPermissions')->name('users.promote')->middleware('admin');
Route::PATCH('/users/{user}/demote', 'UserController@changeUserPermissions')->name('users.demote')->middleware('admin');

Route::get('/me', function () {
    return view('views.auth.passwords.changePasswordView');
})->name('users.changePasswordView');

