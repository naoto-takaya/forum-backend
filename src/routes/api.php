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
// health check用エンドポイント
Route::get('/health_check', 'Controller@health_check')->name('health_check');

// auth
Route::post('/register', 'Auth\RegisterController@register')->name('register');
Route::post('/login', 'Auth\LoginController@login')->name('login');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
Route::post('/password/reset_mail', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('reset.reset_mail');
Route::put('password/reset/{token}', 'Auth\ResetPasswordController@reset')->name('password.reset');

// forumApi
Route::get('/forums/{id}', 'ForumController@get_forum')->name('forums.get_forum');
Route::get('/forums', 'ForumController@list')->name('forums.list');
Route::post('/forums', 'ForumController@create')->middleware('auth')->name('forums.create');
Route::patch('/forums{id}', 'ForumController@update')->middleware('auth')->name('forums.update');
Route::delete('/forums/{id}', 'ForumController@remove')->middleware('auth')->name('forums.remove');

// responseApi
Route::get('/responses/{id}', 'ResponseController@get_response')->name('responses.get_response');
Route::get('/responses/{id}/replies', 'ResponseController@get_replies')->name('responses.get_replies');
Route::get('/responses', 'ResponseController@list')->name('responses.list');
Route::post('/responses', 'ResponseController@create')->middleware('auth')->name('responses.create');
Route::patch('/responses/{id}', 'ResponseController@update')->middleware('auth')->name('responses.update');
Route::delete('/responses{id}', 'ResponseController@remove')->middleware('auth')->name('responses.remove');

// notificationApi
Route::get('/notifications', 'NotificationController@list')->middleware('auth')->name('notifications.list');
Route::put('/notifications', 'NotificationController@checked_notifications')->middleware('auth')->name('notifications.checked');
