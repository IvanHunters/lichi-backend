<?php
namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JWTAuth;
use Hash;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\StorageUser;
use App\CustomLibs\Beget;

class StorageController extends Controller
{

    /**
    *
    * @OA\Get(
    *     path="/api/methods/storages",
    *     summary="Вывести все хранилища пользователя",
    *     tags={"Storage"},
    *     @OA\Response(response="200", description="Выводит хранилища у пользователя"),
    *     security={{"apiAuth": {}}}
    * ),
    * @OA\Get(
    *     path="/api/methods/storage/{id}",
    *     summary="Вывести информацию о хранилище пользователя",
    *     tags={"Storage"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="id бота",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64"
    *         )
    *     ),
    *     @OA\Response(response="200", description="Выводит информацию о хранилище пользователя"),
    *     security={{"apiAuth": {}}}
    * ),
    * @OA\Post(
    *     path="/api/methods/storage",
    *     summary="Создать сервисное хранилище пользователя",
    *     tags={"Storage"},
    *     @OA\Parameter(
    *         name="name",
    *         in="query",
    *         description="Имя хранилища",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             format="string32"
    *         )
    *     ),
    *     @OA\Response(response="200", description="Создает хранилище сервиса"),
    *     security={{"apiAuth": {}}}
    * ),
    * @OA\Post(
    *     path="/api/methods/storage-custom",
    *     summary="Создать пользовательское хранилище пользователя",
    *     tags={"Storage"},
    *     @OA\Parameter(
    *         name="name",
    *         in="query",
    *         description="Имя хранилища",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             format="string32"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="host",
    *         in="query",
    *         description="Хост хранилища",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             format="string32"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="port",
    *         in="query",
    *         description="Порт хранилища",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="database",
    *         in="query",
    *         description="Имя базы данных хранилища",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             format="string32"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="username",
    *         in="query",
    *         description="Имя пользователя хранилища",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             format="string32"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="password",
    *         in="query",
    *         description="Пароль от хранилища",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             format="string32"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="base",
    *         in="query",
    *         description="Вид базы данных ['pgsql', 'mysql', 'sqlite']",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             format="string32"
    *         )
    *     ),
    *     @OA\Response(response="200", description="Создает пользовательское хранилище"),
    *     security={{"apiAuth": {}}}
    * )
    * @OA\Put(
    *     path="/api/methods/storage/{id}",
    *     summary="Изменить данные пользовательского хранилища пользователя",
    *     tags={"Storage"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="id бота",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64"
    *         )
    *     ),
    *     @OA\Response(response="200", description="Изменяет хранилище пользователя (только пользовательское)"),
    *     security={{"apiAuth": {}}}
    * ),
    * @OA\Delete(
    *     path="/api/methods/storage/{id}",
    *     summary="Удалить хранилище пользователя",
    *     tags={"Storage"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="id бота",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64"
    *         )
    *     ),
    *     @OA\Response(response="200", description="Удаляет хранилище пользователя"),
    *     security={{"apiAuth": {}}}
    * ),
    */
    public function __construct()
    {
        $this->data = [
          'status' => false,
          'code' => 401,
          'data' => null,
          'err' => [
            'code' => 1,
            'message' => 'Unauthorized'
          ]
        ];
        $this->user = auth()->user();
        $this->beget = new Beget('w999623p','WDN8aWyAt9');
    }

    public function get($id)
    {
        $storage = StorageUser::where('id',$id)->where('creator_id',$this->user->id);
        if($storage->count() < 1)
        {
          $response = ['status'=>'error', 'code'=>'STORAGE_NOT_EXIST', 'message'=>['en'=>'Storage not exist', 'ru'=>'Хранилище не существует']];
          return response()->json($response, 500);
        }
        $storage = $storage->first();
        $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$storage];
        return response()->json($storage);
    }

    public function getList()
    {
      $storage = StorageUser::where('creator_id',$this->user->id);
      if($storage->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'STORAGE_NOT_EXIST', 'message'=>['en'=>'Storage not exist', 'ru'=>'Хранилище не существует']];
        return response()->json($response, 500);
      }
      $storage = $storage->get(['id', 'name']);
      $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$storage];
      return response()->json($response);
    }

    public function createCustomStorage(Request $req)
    {
      foreach($req->only(['name', 'host', 'port', 'database', 'username', 'base']) as $value){
        if(is_null($value))
        {
          $response = ['status'=>'error', 'code'=>'PARAMS_CANT_BE_NULL', 'message'=>['en'=>'Params can not be null', 'ru'=>'Переданные параметры не должны быть пустыми']];
          return response()->json($response, 500);
        }
      }

        $aprove_base = ['pgsql', 'mysql', 'sqlite'];

        $storage = new StorageUser;
        $storage->name = $req->name;
        $storage->creator_id = $this->user->id;

        $storage->host = $req->host;
        $storage->port = $req->port;
        $storage->type = 2;
        $storage->database = $req->database;
        $storage->username = $req->username;
        $storage->password = $req->password;

        $storage->base = $req->base;

        if(!in_array($req->base, $aprove_base)){
          $response = ['status'=>'error', 'code'=>'BASE_NOT_APROVE', 'message'=>['en'=>'Incorrect type base', 'ru'=>'Вид базы данных не корректен']];
          return response()->json($response, 500);
        }

        try
        {
          $dbcon = new \PDO($this->base.':host='.$storage->host.":".$storage->port.';dbname='.$storage->database, $storage->username, $storage->password);
          $dbcon->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }catch(\ErrorException $e){
            $response = ['status'=>'error', 'code'=>'CONNECTION_FOR_DATABASE_REFUSED', 'message'=>['en'=>'Connection for database was refused', 'ru'=>'Соединение с базой данных не удалось']];
            return response()->json($response, 500);
        }
        $dbcon->exec("CREATE TABLE `lichi_users` ( `user_id` VARCHAR(100) NOT NULL ,  `platform` VARCHAR(100) NOT NULL ,  `status` INT NOT NULL DEFAULT '0' ,    UNIQUE  (`id`));");
        $dbcon->exec("CREATE TABLE `lichi_requests` ( `user_id` VARCHAR(100) NOT NULL ,  `platform` VARCHAR(100) NOT NULL ,  `event` VARCHAR(10000) NOT NULL DEFAULT 'message_new' ,  `description` VARCHAR(10000) NOT NULL ,  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP );");
        $dbcon->exec("CREATE TABLE `lichi_authorization` ( `user_id` VARCHAR(100) NOT NULL , `platform` VARCHAR(100) NOT NULL ,  `hash` VARCHAR(1000) NOT NULL ,    UNIQUE  (`user_id`));");

        $storage->save();

        $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>['id'=>$storage->id,'name'=>$storage->name], 'message'=>['en'=>'The storage was successfully created', 'ru'=>'Хранилище было успешно создано']];
        return response()->json($response);
    }

    public function create(Request $req)
    {
      foreach($req->only(['name']) as $value){
        if(is_null($value))
        {
          $response = ['status'=>'error', 'code'=>'PARAMS_CANT_BE_NULL', 'message'=>['en'=>'Params can not be null', 'ru'=>'Переданные параметры не должны быть пустыми']];
          return response()->json($response, 500);
        }
      }

        $storage = new StorageUser;

        $storage->name = $req->name;
        $storage->base = 'mysql';
        $storage->creator_id = $this->user->id;

        $this->begetResponse = $this->beget->addStorage();
        if($this->begetResponse){
          $storage->database = $this->begetResponse['login'];
          $storage->username = $this->begetResponse['login'];
          $storage->password = $this->begetResponse['password'];
          sleep(1);
          $mysqli = new \mysqli("w999623p.beget.tech", $storage->username, $storage->password, $storage->database);
          $mysqli->query("CREATE TABLE `lichi_users` ( `user_id` VARCHAR(100) NOT NULL ,  `platform` VARCHAR(100) NOT NULL ,  `status` INT NOT NULL DEFAULT '0' ,    UNIQUE  (`user_id`)) ENGINE = InnoDB;");
          $mysqli->query("CREATE TABLE `lichi_requests` ( `user_id` VARCHAR(100) NOT NULL ,  `platform` VARCHAR(100) NOT NULL ,  `event` VARCHAR(10000) NOT NULL DEFAULT 'message_new' ,  `description` VARCHAR(10000) NOT NULL ,  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ) ENGINE = InnoDB;");
          $mysqli->query("CREATE TABLE `lichi_authorization` ( `user_id` VARCHAR(100) NOT NULL , `platform` VARCHAR(100) NOT NULL ,  `hash` VARCHAR(1000) NOT NULL ,    UNIQUE  (`user_id`));");

          $storage->save();
          $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>['id'=>$storage->id,'name'=>$storage->name], 'message'=>['en'=>'The storage was successfully created', 'ru'=>'Хранилище было успешно создано']];
          return response()->json($response);
        }else{
          $response = ['status'=>'error', 'code'=>'TRY_LATER_CREATE_STORAGE', 'message'=>['en'=>'Try later create storage', 'ru'=>'Повторите создание хранилища позже']];
          return response()->json($response, 500);
        }
    }

    public function update($id, Request $req)
    {
      foreach($req->only(['name', 'host', 'port', 'database', 'username', 'base']) as $value){
        if(is_null($value))
        {
          $response = ['status'=>'error', 'code'=>'PARAMS_CANT_BE_NULL', 'message'=>['en'=>'Params can not be null', 'ru'=>'Переданные параметры не должны быть пустыми']];
          return response()->json($response, 500);
        }
      }

      $storage = StorageUser::where('id',$id)->where('creator_id',$this->user->id);
      if($storage->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'STORAGE_NOT_EXIST', 'message'=>['en'=>'Storage not exist', 'ru'=>'Хранилище не существует']];
        return response()->json($response, 500);
      }

      $storage = $storage->first();
      foreach ($req->only(['name', 'host', 'port', 'database', 'username', 'base']) as $key => $value)
      {

        $storage->{$key} = $value;
      }
      $storage->save();

      $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$storage, 'message'=>['en'=>'The storage was successfully updated', 'ru'=>'Хранилище было успешно обновлено']];
      return response()->json($response);
    }

    public function delete($id)
    {
      $storage = StorageUser::where('id', $id)->where('creator_id',$this->user->id);
      if($storage->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'STORAGES_NOT_EXISTS', 'message'=>['en'=>'Storage not exist', 'ru'=>'Хранилище не существует']];
        return response()->json($response, 500);
      }
      $storage = $storage->first();
      $suffix = explode("_",$storage->database)[1];
      $this->beget->dropStorage($suffix);
      $storage->delete();
      $response = ['status'=>'ok', 'code'=>'SUCCESS', 'message'=>['en'=>'The storage was successfully deleted', 'ru'=>'Хранилище было успешно удалено']];
      return response()->json($response);
    }
}
