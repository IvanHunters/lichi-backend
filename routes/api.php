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
      Route::post('vk', 'AuthController@login_vk');
      Route::post('login', 'AuthController@login');
      Route::post('registration', 'AuthController@registration');

      Route::group(['middleware' => 'jwt.verify'], static function($router){
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::get('me', 'AuthController@me');
        Route::post('change_password', 'AuthController@change_password');
      });

    });

    Route::group([ 'middleware' => ['api'],
    'prefix' => 'methods'], static function ($router) {
      Route::group(['middleware' => 'jwt.verify'], static function($router){
        /*  События с ботами  */
        Route::get('actions', 'ActionsController@index');

        Route::get('bots', 'BotController@getList');
        Route::get('bot/{id}', 'BotController@get');

        Route::post('bot', 'BotController@create');

        Route::put('bot/{id}', 'BotController@update');
        Route::delete('bot/{id}', 'BotController@delete');

        /*Обработчики бота*/
        Route::get('bot/{id}/handler', 'BotController@getHandler');
        Route::post('bot/{id}/handler', 'BotController@setHandler');

        Route::post('bot/{id}/connect', 'BotController@connect');


        /*  События с хранилищами  */
        Route::get('storages', 'StorageController@getList');
        Route::get('storage/{id}', 'StorageController@get');

        Route::post('storage', 'StorageController@create');
        Route::post('storage-custom', 'StorageController@createCustomStorage');

        Route::put('storage/{id}', 'StorageController@update');
        Route::delete('storage/{id}', 'StorageController@delete');

        /*  События с медиабиблиотекой  */
        Route::get('media', 'MediaController@getList');

        Route::post('media', 'MediaController@create');

        Route::put('media/{id}', 'MediaController@update');
        Route::delete('media/{id}', 'MediaController@delete');

        /*  События с рассылкой  */
        Route::get('mailing', 'MailingController@getList');
        Route::get('mailing/{id}', 'MailingController@get');
        Route::get('mailing/bot/{id}', 'MailingController@getForBot');

        Route::post('mailing', 'MailingController@create');

        Route::put('mailing/{id}', 'MailingController@update');
        Route::delete('mailing/{id}', 'MailingController@delete');

      });
    });
    Route::group([ 'middleware' => ['api'],
    'prefix' => 'webhook'], static function ($router) {
      Route::post('{hash_name}/{platform}', 'ActionsController@bot_handler');
    });
