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

Route::get('/', function () {
   // broadcast(new \App\Events\DemoMessageSent('somedata'));
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/userlist', 'MessageController@users')->name('user.list');
Route::get('/usermessages/{id}', 'MessageController@userMessages')->name('user.messages');
Route::post('/sendmessage', 'MessageController@send_message')->name('user.message.send');
Route::post('/sendimage/{id}', 'MessageController@send_image');
Route::get('/deletemessage/{id}', 'MessageController@delete_message')->name('message.delete');
Route::get ('deleteallmessages/{id}', 'MessageController@delete_all_message')->name('message.delete.all');
