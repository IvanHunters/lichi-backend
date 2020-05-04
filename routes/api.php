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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group([ 'middleware' => ['api'],
'prefix' => 'auth'], static function ($router) {
Route::post('login', 'AuthController@login');
Route::post('register', 'AuthController@register');
Route::group(['middleware' => 'jwt.verify'], static function($router){
Route::post('logout', 'AuthController@logout');
Route::post('refresh', 'AuthController@refresh');
Route::post('me', 'AuthController@me'); });
    /*  События с ботами  */
    Route::get('bots', 'BotController@getList');
    Route::get('bot/{id}', 'BotController@get');

    Route::post('bot', 'BotController@create');

    Route::put('bot/{id}', 'BotController@update');
    Route::delete('bot/{id}', 'BotController@delete');

    /*  События с хранилищами  */
    Route::get('storages', 'StorageController@getList');
    Route::get('storage/{id}', 'StorageController@get');

    Route::post('storage', 'StorageController@create');
    Route::post('storage-custom', 'StorageController@createCustomStorage');

    Route::put('storage/{id}', 'StorageController@update');
    Route::delete('storage/{id}', 'StorageController@delete');
});
