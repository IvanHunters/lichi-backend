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

Auth::routes();

use BeyondCode\LaravelWebSockets\Facades\WebSocketsRouter;
WebSocketsRouter::webSocket('/websocket', \App\Events\WebsocketHandler::class);

Route::get('/media/{id}/{file_id}', 'MediaController@get');
