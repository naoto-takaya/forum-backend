<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// auth
Route::post('/register', 'Auth\RegisterController@register')->name('register');
Route::post('/login', 'Auth\LoginController@login')->name('login');
Route::get('/logout', 'Auth\LoginController@logout')->middleware('auth')->name('logout');

// forumApi
Route::get('/forums{forum_id}', 'ForumController@get_forum')->name('forums.get_forum');
Route::get('/forums', 'ForumController@list')->name('forums.list');
Route::post('/forums', 'ForumController@create')->name('forums.create');
Route::patch('/forums', 'ForumController@update')->name('forums.update');
Route::delete('/forums{forum_id}', 'ForumController@delete')->name('forums.delete');

// responseApi
Route::get('/responses{response_id}', 'ResponseController@get_response')->name('responses.get_response');
Route::get('/responses', 'ResponseController@list')->name('responses.list');
Route::post('/responses', 'ResponseController@create')->name('responses.create');
Route::patch('/responses', 'ResponseController@update')->name('responses.update');
Route::delete('/responses{response_id}', 'ResponseController@delete')->name('responses.delete');
