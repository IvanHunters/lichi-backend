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
use App\Models\Bot;
use App\Models\StorageUser;
use App\CustomLibs\JsonParseCode;
use Illuminate\Support\Facades\Storage;
class BotController extends Controller
{
  /**
  *
  * @OA\Get(
  *     path="/api/methods/bots",
  *     summary="Вывести всех ботов пользователя",
  *     tags={"Bots"},
  *     @OA\Response(response="200", description="Выводит ботов у пользователя"),
  *     security={{"apiAuth": {}}}
  * )
  *
  * @OA\Get(
  *     path="/api/methods/bot/{id}",
  *     summary="Вывести информацию о боте пользователя",
  *     tags={"Bots"},
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
  *     @OA\Response(response="200", description="Выводит информацию о боте пользователя"),
  *     security={{"apiAuth": {}}}
  * )
  *
  * @OA\Post(
  *     path="/api/methods/bot",
  *     summary="Создать бота пользователя",
  *     tags={"Bots"},
  *     @OA\Parameter(
  *         name="name",
  *         in="query",
  *         description="Имя бота",
  *         required=true,
  *         @OA\Schema(
  *             type="string",
  *             @OA\Items(type="string"),
  *         ),
  *         style="form"
  *     ),
  *     @OA\Parameter(
  *         name="storage_id",
  *         in="query",
  *         description="id хранилища пользователя",
  *         required=true,
  *         @OA\Schema(
  *             type="integer",
  *             format="int64"
  *         ),
  *         style="form"
  *     ),
  *     @OA\Response(response="200", description="Создает бота пользователя"),
  *     security={{"apiAuth": {}}}
  * )
  *
  * @OA\Put(
  *     path="/api/methods/bot/{id}",
  *     summary="Изменить бота пользователя",
  *     tags={"Bots"},
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
  *     @OA\Parameter(
  *         name="storage_id",
  *         in="query",
  *         description="id хранилища",
  *         required=false,
  *         @OA\Schema(
  *             type="integer",
  *             format="int32"
  *         )
  *     ),
  *     @OA\Parameter(
  *         name="name",
  *         in="query",
  *         description="Имя бота",
  *         required=false,
  *         @OA\Schema(
  *             type="string",
  *             format="string32"
  *         )
  *     ),
  *     @OA\Parameter(
  *         name="vk_status",
  *         in="query",
  *         description="Включение (1) или выключение (0) бота Вконтакте",
  *         required=false,
  *         @OA\Schema(
  *             type="integer",
  *             format="int1"
  *         )
  *     ),
  *     @OA\Parameter(
  *         name="tg_status",
  *         in="query",
  *         description="Включение (1) или выключение (0) бота Телеграмм",
  *         required=false,
  *         @OA\Schema(
  *             type="integer",
  *             format="int1"
  *         )
  *     ),
  *     @OA\Parameter(
  *         name="vb_status",
  *         in="query",
  *         description="Включение (1) или выключение (0) бота Viber",
  *         required=false,
  *         @OA\Schema(
  *             type="integer",
  *             format="int1"
  *         )
  *     ),
  *     @OA\Parameter(
  *         name="vk_token_group",
  *         in="query",
  *         description="Токен группы Вконтакте",
  *         required=false,
  *         @OA\Schema(
  *             type="string",
  *             format="string32"
  *         )
  *     ),
  *     @OA\Parameter(
  *         name="tg_token",
  *         in="query",
  *         description="Токен Телеграмм",
  *         required=false,
  *         @OA\Schema(
  *             type="string",
  *             format="string32"
  *         )
  *     ),
  *     @OA\Parameter(
  *         name="tg_proxy",
  *         in="query",
  *         description="Телеграмм-прокси",
  *         required=false,
  *         @OA\Schema(
  *             type="string",
  *             format="string32"
  *         )
  *     ),
  *     @OA\Parameter(
  *         name="vb_token",
  *         in="query",
  *         description="Токен Viber",
  *         required=false,
  *         @OA\Schema(
  *             type="string",
  *             format="string32"
  *         )
  *     ),
  *     @OA\Response(response="200", description="Обновляет информацию о боте пользователя"),
  *     security={{"apiAuth": {}}}
  * )
  *
  * @OA\Delete(
  *     tags={"Bots"},
  *     summary="Удалить бота пользователя",
  *     path="/api/methods/bot/{id}",
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
  *     @OA\Response(response="200", description="Удаляет бота пользователя"),
  *     security={{"apiAuth": {}}}
  * )
  * @OA\Post(
  *     path="/api/methods/bot/{id}/handler",
  *     summary="Добавляет или изменяет обработчик бота пользователя",
  *     tags={"Bots"},
  *     @OA\Parameter(
  *         name="id",
  *         in="path",
  *         description="id бота",
  *         required=true,
  *         @OA\Schema(
  *             type="integer",
  *             @OA\Items(type="integer"),
  *         ),
  *         style="form"
  *     ),
  *     @OA\Parameter(
  *         name="handler",
  *         in="query",
  *         description="обработчик бота",
  *         required=true,
  *         @OA\Schema(
  *             type="string",
  *             @OA\Items(type="json"),
  *         ),
  *         style="form"
  *     ),
  *     @OA\Response(response="200", description=""),
  *     security={{"apiAuth": {}}}
  * )
  * @OA\Get(
  *     path="/api/methods/bot/{id}/handler",
  *     summary="Получает обработчик в виде JSON для бота пользователя",
  *     tags={"Bots"},
  *     @OA\Parameter(
  *         name="id",
  *         in="path",
  *         description="id бота",
  *         required=true,
  *         @OA\Schema(
  *             type="integer",
  *             format="int32"
  *         ),
  *         style="form"
  *     ),
  *     @OA\Response(response="200", description=""),
  *     security={{"apiAuth": {}}}
  * )
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
    }

    public function get($id)
    {
        $bot = Bot::where('id',$id)->where('creator_id',$this->user->id)->select(['id','name', 'storage_id', 'vk_token_group', 'vk_already_connected', 'tg_already_connected', 'vb_already_connected', 'vk_status', 'tg_token', 'tg_status', 'tg_proxy', 'vb_token', 'vb_status']);
        if($bot->count() < 1)
        {
          $response = ['status'=>'error', 'code'=>'BOT_NOT_EXIST', 'message'=>['en'=>'Bot not exist', 'ru'=>'Бот не существует']];
          return response()->json($response, 500);
        }
        $bot = $bot->first();

        $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$bot];
        return response()->json($response);
    }

    public function getList()
    {
      $bot = Bot::where('creator_id',$this->user->id)->select(['id','name', 'storage_id']);
      if($bot->count() < 1)
      {
        $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>[]];
        return response()->json($response);
      }
      $bot = $bot->get(['id', 'name']);
      $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$bot];
      return response()->json($response);
    }

    public function create(Request $req)
    {
      foreach($req->only(['name', 'storage_id']) as $value){
        if(is_null($value))
        {
          $response = ['status'=>'error', 'code'=>'PARAMS_CANT_BE_NULL', 'message'=>['en'=>'Params can not be null', 'ru'=>'Переданные параметры не должны быть пустыми']];
          return response()->json($response, 500);
        }
      }

        $bot = new Bot;

        $bot->name = $req->name;
        $bot->creator_id = $this->user->id;

        if(!$req->has('storage_id') || StorageUser::where('id', $req->storage_id)->where('creator_id', $this->user->id)->count() == 0)
        {
          $response = ['status'=>'error', 'code'=>'STORAGE_NOT_EXIST', 'message'=>['en'=>'Storage not exist', 'ru'=>'Хранилище не существует']];
          return response()->json($response, 500);
        }

        $bot->storage_id = $req->storage_id;
        $bot->hash_name = md5("bot".$req->name.microtime(true).rand(1,1000));
        $bot->vk_secret_key = md5("bot".$req->name.microtime(true).rand(1,1000));
        $bot->tg_proxy = '80.187.140.26:8080';
        $bot->save();

        Storage::makeDirectory("handlers/{$bot->id}");
        $json = Storage::get("new_bot.json");
        Storage::disk("handlers")->put("{$bot->id}/handler.json", $json);

        $JsonParseCode = new JsonParseCode($bot->id);
        $response = $JsonParseCode->set_handler($json);

        $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$bot, 'message'=>['en'=>'The bot was successfully created', 'ru'=>'Бот был успешно создан']];
        return response()->json($response);
    }

    public function connect($id, $platform){
      $bot = Bot::where('id',$id)->where('creator_id',$this->user->id);
      if($bot->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'BOT_NOT_EXIST', 'message'=>['en'=>'Bot not exist', 'ru'=>'Бот не существует']];
        return response()->json($response, 500);
      }
      $bot = $bot->first();
      if($platform != 'vk')
      {
        $token = $platform."_token";
        if($bot->$token == '')
        {
          $response = ['status'=>'error', 'code'=>'TOKEN_ERROR', 'message'=>['en'=>'Token error', 'ru'=>'Ошибка токена']];
          return response()->json($response, 500);
        }
      }else{
        $token = $platform."_token_group";
        if($bot->$token == '')
        {
          $response = ['status'=>'error', 'code'=>'TOKEN_ERROR', 'message'=>['en'=>'Token error', 'ru'=>'Ошибка токена']];
          return response()->json($response, 500);
        }
      }
      $already_connected = $platform."_already_connected";
      $status = $platform."_status";
      $bot->$already_connected = 1;
      $bot->save();

      $platform = mb_strtoupper($platform);
      $namespase = "\Lichi\\$platform\Callback";
      $config["VK_TOKEN_USER"]    = $bot->vk_token_user;
      $config["VK_TOKEN_GROUP"]   = $bot->vk_token_group;
      $config["VK_TOKEN_CONFIRM"] = $bot->vk_token_confirm;
      $config["VK_SECRET_KEY"]    = $bot->vk_secret_key;
      $config["TG_TOKEN"]         = $bot->tg_token;
      $config["TG_PROXY"]         = $bot->tg_proxy;
      $config["VB_TOKEN"]         = $bot->vb_token;

      $event = new $namespase($config);
      if($platform == "VK"){
        $params_connect = $event->get_connect_data();
        if($platform){
          if(!isset($params_connect['code_connect'])){
            $response = ['status'=>'error', 'code'=>'TOKEN_ERROR', 'message'=>['en'=>'Token error', 'ru'=>'Ошибка токена']];
            return response()->json($response, 500);

          }

          $bot->vk_token_confirm = $params_connect['code_connect'];
          $bot->save();
          $connect_status = $event->set_webhook("https://api.lichi-social.ru/api/webhook/{$bot->hash_name}/{$platform}", $bot->vk_secret_key, $params_connect['group_id']);

          if($connect_status)
          {
            $bot->vk_server_id = $connect_status;
            $bot->save();
            $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>[]];
            return response()->json($response);

          }else{
            $bot->$status = 0;
            $bot->$already_connected = 0;
            $bot->save();
            $response = ['status'=>'error', 'code'=>'ERROR_CONNECT', 'message'=>['en'=>'Token has no required rules', 'ru'=>'Токен не имеет прав на подключение бота']];
            return response()->json($response, 500);

          }

        }else{
          $bot->$status = 0;
          $bot->$already_connected = 0;
          $bot->save();
          $response = ['status'=>'error', 'code'=>'TOKEN_ERROR', 'message'=>['en'=>'Token error', 'ru'=>'Ошибка токена']];
          return response()->json($response, 500);
        }
      }else{
        $data = $event->set_webhook("https://api.lichi-social.ru/api/webhook/{$bot->hash_name}/{$platform}");
        if($data){
          $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$data, 'message'=>['en'=>'The bot was successfully connected', 'ru'=>'Бот был успешно подключен']];
          return response()->json($response);
        }else{
          $bot->$status = 0;
          $bot->$already_connected = 0;
          $bot->save();
          $response = ['status'=>'error', 'code'=>'CONNECT_REFUSED', 'message'=>['en'=>'Connect refused', 'ru'=>'Подключение не удалось']];
          return response()->json($response, 500);
        }
      }
    }

    public function update($id, Request $req)
    {
      foreach($req->only(['name', 'storage_id', 'vk_token_group', 'tg_token', 'vb_token', 'vk_status', 'tg_status', 'vb_status']) as $value){
        if(is_null($value))
        {
          $response = ['status'=>'error', 'code'=>'PARAMS_CANT_BE_NULL', 'message'=>['en'=>'Params can not be null', 'ru'=>'Переданные параметры не должны быть пустыми']];
          return response()->json($response, 500);
        }
      }

      $bot = Bot::where('id',$id)->where('creator_id',$this->user->id);
      if($bot->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'BOT_NOT_EXIST', 'message'=>['en'=>'Bot not exist', 'ru'=>'Бот не найден']];
        return response()->json($response, 500);
      }

      $bot = $bot->first();
      $connect = false;

      foreach ($req->only(['name', 'storage_id', 'vk_token_group', 'tg_token', 'vb_token', 'vk_status', 'tg_status', 'vb_status']) as $key => $value){

        if($key == "storage_id" && StorageUser::where('id', $req->storage_id)->where('creator_id', $this->user->id)->count() == 0)
        {
          $response = ['status'=>'error', 'code'=>'STORAGE_NOT_EXIST', 'message'=>['en'=>'Storage not exist', 'ru'=>'Хранилище не существует']];
          return response()->json($response, 500);
        }

        if(in_array($key,["vk_status","tg_status","vb_status"]) && $value == '1')
        {
          $platform = explode('_status', $key)[0];
          $platform_already = $platform."_already_connected";
          if($bot->$platform_already == '0')
          {
            $connect = true;
          }
        }

        if(in_array($key,["vk_status","tg_status","vb_status"]) && $value == '0')
        {
          $platform = explode('_status', $key)[0];
          $platform_already = $platform."_already_connected";
          if($bot->$platform_already == '1')
          {
            $bot->$platform_already = 0;
            if($platform == "vk"){
              $platform = mb_strtoupper($platform);
              $namespase = "\Lichi\\$platform\Callback";
              $config["VK_TOKEN_USER"]    = $bot->vk_token_user;
              $config["VK_TOKEN_GROUP"]   = $bot->vk_token_group;
              $event = new $namespase($config);
              $event->delete_webhook($bot->vk_server_id);
            }
          }
        }

        $bot->{$key} = $value;
      }

      $bot->save();

      if($connect){
        return $this->connect($bot->id, $platform);
      }

      $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$bot, 'message'=>['en'=>'The bot was successfully updated', 'ru'=>'Бот был успешно обновлен']];
      return response()->json($response);
    }

    public function delete($id)
    {
      $bot = Bot::where('id', $id)->where('creator_id',$this->user->id);
      if($bot->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'BOTS_NOT_EXISTS', 'message'=>['en'=>'Bot not exist', 'ru'=>'Бот не существует']];
        return response()->json($response, 500);
      }
      $bot = $bot->first();
      Storage::deleteDirectory("handlers/{$bot->id}");
      $bot->delete();
      $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>[], 'message'=>['en'=>'The bot was successfully deleted', 'ru'=>'Бот был успешно удален']];
      return response()->json($response);
    }

    /*Обработчики бота*/
    public function getHandler($id, Request $req)
    {
      $bot = Bot::where('id',$id)->where('creator_id',$this->user->id);
      if($bot->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'BOT_NOT_EXIST', 'message'=>['en'=>'Bot not exist', 'ru'=>'Бот не существует']];
        return response()->json($response, 500);
      }

      $bot = $bot->first();
      $JsonParseCode = new JsonParseCode($id);
      $data = $JsonParseCode->get_handler($id);
      $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>['handler'=>json_encode(json_decode($data, true), JSON_UNESCAPED_UNICODE)]];
      return response()->json($response);
    }

    public function setHandler($id, Request $req)
    {
      $bot = Bot::where('id',$id)->where('creator_id',$this->user->id);
      if($bot->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'BOT_NOT_EXIST', 'message'=>['en'=>'Bot not exist', 'ru'=>'Бот не существует']];
        return response()->json($response, 500);
      }

      $bot = $bot->first();
      if($bot->handler_block == '1'){
        $response = ['status'=>'error', 'code'=>'HANDLER_WAS_BLOCKED', 'message'=>['en'=>'Handler was blocked', 'ru'=>'Обновление обработчика было отключено']];
        return response()->json($response, 500);
      }
      $JsonParseCode = new JsonParseCode($id);
      $response = $JsonParseCode->set_handler($req->handler);
      if($response['status'] == 'error')
        return response()->json($response, 500);
      else
        return response()->json($response);
    }
}
