<?php

use Illuminate\Http\Request;
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



// forumApi
Route::get('/forums{forum_id}', 'ForumController@get_forum')->name('forums.get_forum');
Route::get('/forums', 'ForumController@list')->name('forums.list');
Route::post('/forums', 'ForumController@create')->name('forums.create');
Route::patch('/forums', 'ForumController@update')->name('forums.update');
Route::delete('/forums{forum_id}', 'ForumController@delete')->name('forums.delete');