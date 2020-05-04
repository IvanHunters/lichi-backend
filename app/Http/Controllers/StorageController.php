<?php
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Models\StorageUser;
use App\CustomLibs\Beget;
class StorageController extends Controller
{

    /**
    *
    * @OA\Get(
    *     path="/api/auth/method/storages",
    *     tags={"Storage"},
    *     @OA\Response(response="200", description="Выводит хранилища у пользователя")
    * ),
    * @OA\Get(
    *     path="/api/auth/method/storage",
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
    *     @OA\Response(response="200", description="Выводит информацию о хранилище пользователя")
    * ),
    * @OA\Post(
    *     path="/api/auth/method/storage",
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
    *     @OA\Response(response="200", description="Создает хранилище сервиса")
    * ),
    * @OA\Post(
    *     path="/api/auth/method/storage-custom",
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
    *     @OA\Response(response="200", description="Создает пользовательское хранилище")
    * )
    * @OA\Put(
    *     path="/api/auth/method/storage",
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
    *     @OA\Response(response="200", description="Изменяет хранилище пользователя (только пользовательское)")
    * ),
    * @OA\Delete(
    *     path="/api/auth/method/storage",
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
    *     @OA\Response(response="200", description="Удаляет хранилище пользователя")
    * ),
    */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'registration']]);
        $this->user = auth()->user();
        $this->beget = new Beget('w999623p','WDN8aWyAt9');
    }

    public function get($id)
    {
        $storage = StorageUser::where('id',$id)->where('creator_id',$this->user->id);
        if($storage->count() < 1)
        {
          $response = ['status'=>'error', 'code'=>'STORAGE_NOT_EXIST'];
          return response()->json($response);
        }
        $storage = $storage->first();
        return response()->json($storage);
    }

    public function getList()
    {
      $storage = StorageUser::where('creator_id',$this->user->id);
      if($storage->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'STORAGES_NOT_EXISTS'];
        return response()->json($response);
      }
      $storage = $storage->all();
      return response()->json($storage);
    }

    public function createCustomStorage(Request $req)
    {
        $storage = new StorageUser;
        $storage->name = $req->name;
        $storage->host = $req->host;
        $storage->port = $req->port;
        $storage->type = 2;
        $storage->database = $req->database;
        $storage->username = $req->username;
        $storage->save();

        $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>['id'=>$storage->id,'name'=>$storage->name]];
        return response()->json($response);
    }

    public function create(Request $req)
    {
        $storage = new StorageUser;

        $storage->name = $req->name;
        $storage->creator_id = $this->user->id;

        $this->begetResponse = $this->beget->addStorage();
        if($this->begetResponse){
          $storage->database = $this->begetResponse['login'];
          $storage->username = $this->begetResponse['login'];
          $storage->password = $this->begetResponse['password'];
          $storage->save();
          $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>['id'=>$storage->id,'name'=>$storage->name]];
        }else{
          $response = ['status'=>'error', 'code'=>'TRY_LATER_CREATE_STORAGE'];
        }
        return response()->json($response);
    }

    public function update($id, Request $req)
    {
      $storage = StorageUser::where('id',$id)->where('creator_id',$this->user->id)->where('type', '2');
      if($storage->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'STORAGE_NOT_EXIST', 'items'=>$storage];
        return response()->json($response);
      }

      $storage = $storage->first();
      foreach ($req->input() as $key => $value)  $storage->{$key} = $value;
      $storage->save();

      $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$storage];
      return response()->json($response);
    }

    public function delete($id)
    {
      $storage = StorageUser::where('id', $id)->where('creator_id',$this->user->id);
      if($storage->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'STORAGES_NOT_EXISTS'];
        return response()->json($response);
      }
      $storage = $storage->first();
      $suffix = explode("_",$storage->database)[1];
      $this->beget->dropStorage($suffix);
      $storage->delete();
      $response = ['status'=>'ok', 'code'=>'SUCCESS'];
      return response()->json($response);
    }
}
