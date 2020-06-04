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
use App\Models\Mailing;
use App\Models\Bot;
use App\Models\StorageUser;
use App\Models\MediaLib;
use App\CustomLibs\DatabaseLib;

class MailingController extends Controller
{

    /**
    *
    * @OA\Get(
    *     path="/api/methods/mailing",
    *     summary="Вывести все рассылки пользователя",
    *     tags={"Mailing"},
    *     @OA\Response(response="200", description="Выводит рассылки пользователя"),
    *     security={{"apiAuth": {}}}
    * ),
    * @OA\Get(
    *     path="/api/methods/mailing/{id}",
    *     summary="Вывести информацию о рассылке пользователя",
    *     tags={"Mailing"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="id рассылки",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64"
    *         )
    *     ),
    *     @OA\Response(response="200", description="Выводит информацию о рассылке пользователя"),
    *     security={{"apiAuth": {}}}
    * ),
    * @OA\Get(
    *     path="/api/methods/mailing/bot/{id}",
    *     summary="Вывести информацию о рассылках бота",
    *     tags={"Mailing"},
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
    *     @OA\Response(response="200", description="Выводит информацию о рассылках пользователя"),
    *     security={{"apiAuth": {}}}
    * ),
    * @OA\Post(
    *     path="/api/methods/mailing",
    *     summary="Создать рассылку пользователя",
    *     tags={"Mailing"},
    *     @OA\Parameter(
    *         name="name",
    *         in="query",
    *         description="Имя рассылки",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             format="string32"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="bot_id",
    *         in="query",
    *         description="id бота",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="platform",
    *         in="query",
    *         description="Платформа для рассылки [vk, tg, vb]",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             format="string32"
    *         )
    *     ),
    *     @OA\Response(response="200", description="Создает рассылку"),
    *     security={{"apiAuth": {}}}
    * ),
    * @OA\Put(
    *     path="/api/methods/mailing/{id}",
    *     summary="Изменить рассылку пользователя",
    *     tags={"Mailing"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="id рассылки",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="bot_id",
    *         in="query",
    *         description="id бота",
    *         @OA\Schema(
    *             type="integer",
    *             format="int64"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="platform",
    *         in="query",
    *         description="Платформа для рассылки [vk, tg, vb]",
    *         @OA\Schema(
    *             type="string",
    *             format="string32"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="media_id",
    *         in="query",
    *         description="id Медиафайла",
    *         @OA\Schema(
    *             type="integer",
    *             format="int64"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="text_message",
    *         in="query",
    *         description="Текст сообщения",
    *         @OA\Schema(
    *             type="string",
    *             format="int64"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="status",
    *         in="query",
    *         description="Статус рассылки [0-1]",
    *         @OA\Schema(
    *             type="integer",
    *             format="int64"
    *         )
    *     ),
    *     @OA\Response(response="200", description="Изменяет хранилище пользователя (только пользовательское)"),
    *     security={{"apiAuth": {}}}
    * ),
    * @OA\Delete(
    *     path="/api/methods/mailing/{id}",
    *     summary="Удалить рассылку пользователя",
    *     tags={"Mailing"},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="id рассылки",
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
    }

    public function start($platform, $bot_id, $message, $media_id){

      header("Content-Encoding: none");
      header("Connection: close");
      $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>[]];
      echo json_encode($response);

      fastcgi_finish_request();

      $bot = Bot::where('id', $bot_id)->first();
      $storage_id = $bot->storage_id;

      $storage = StorageUser::where('id', $storage_id)->first();
      $PDO = new DatabaseLib($storage->base, $storage->host.":".$storage->port, $storage->username,  $storage->password, $storage->database);
      $db_rows = $PDO->exq("SELECT * FROM lichi_users WHERE platform ='{$platform}'", true);
      while($row = $db_rows->fetch()){
        $users[] = $row['user_id'];
      }
      $namespase = "\Lichi\\".strtoupper($platform)."\Callback";
      $config["VK_TOKEN_USER"]    = $bot->vk_token_user;
      $config["VK_TOKEN_GROUP"]   = $bot->vk_token_group;
      $config["VK_TOKEN_CONFIRM"] = $bot->vk_token_confirm;
      $config["VK_SECRET_KEY"]    = $bot->vk_secret_key;
      $config["TG_TOKEN"]         = $bot->tg_token;
      $config["TG_PROXY"]         = $bot->tg_proxy;
      $config["VB_TOKEN"]         = $bot->vb_token;



      $event = new $namespase($config);
      if($media_id != '0')
      {
      $data = MediaLib::where('id', $media_id)->first();
      $file_path = storage_path("app".$data->path);

      if($data->type == '1')
        $media = $event->upload_photo($file_path, true, false);
      else
        $media = $event_data->upload_document($file_path);
      }
      \DB::disconnect();
      if($platform == "vk")
      {
        $chunks = array_chunk($users, 100);
        foreach($chunks as $chunk)
        {
          $users = implode(",", $chunk);
          $arrayParams['user_ids'] = $users;
          $arrayParams['message'] = $message;
          $arrayParams['random_id'] = rand(1, 435345345345093);
          if($media_id != '0')
          {
            $arrayParams['attachment'] = $media;
          }
          $data = $event->CallHowGroup('messages.send', $arrayParams);
		      sleep(5);
        }
      }
      else
      {
        $arrayParams = [];
		    $i = 0;
        foreach($users as $user)
        {
          $event->user_id = $user;
          $event->chat_id = $user;

          if($media_id != '0')
          {
            $arrayParams['attachment'] = $media;
          }

          $event->message_send($message, $arrayParams);
		  $i++;
		  if($i > 100){
			sleep(1);
			$i = 0;
		  }
        }
      }

    }

    public function get($id)
    {
        $mailing = Mailing::where('id',$id)->where('creator_id',$this->user->id);
        if($mailing->count() < 1)
        {
          $response = ['status'=>'error', 'code'=>'MAILING_NOT_EXIST', 'message'=>['en'=>'Mailing not exist', 'ru'=>'Рассылки не существует']];
          return response()->json($response, 500);
        }

        $mailing = $mailing->get(['id', 'name', 'status', 'platform', 'bot_id', 'text_message', 'media_id']);
        $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$mailing];
        return response()->json($response);
    }

    public function getForBot($id)
    {
        $mailing = Mailing::where('bot_id',$id)->where('creator_id',$this->user->id);
        if($mailing->count() < 1)
        {
          $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>[]];
          return response()->json($response);
        }

        $mailing = $mailing->get(['id', 'name', 'status', 'platform', 'bot_id', 'text_message', 'media_id']);
        $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$mailing];
        return response()->json($response);
    }

    public function getList()
    {
      $mailing = Mailing::where('creator_id',$this->user->id);
      if($mailing->count() < 1)
      {
        $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>[]];
        return response()->json($response);
      }
      $mailing = $mailing->get(['id', 'name']);
      $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$mailing];
      return response()->json($response);
    }

    public function create(Request $req)
    {
      foreach($req->only(['name', 'bot_id', 'status', 'media_id', 'text_message', 'platform']) as $value){
        if(is_null($value))
        {
          $response = ['status'=>'error', 'code'=>'PARAMS_CANT_BE_NULL', 'message'=>['en'=>'Params can not be null', 'ru'=>'Переданные параметры не должны быть пустыми']];
          return response()->json($response, 500);
        }
      }

        $mailing = new Mailing;

        $mailing->name = $req->name;
        $mailing->creator_id = $this->user->id;

        if(in_array($req->platform, ['vk', 'tg', 'vb']))
        {
          $mailing->platform = $req->platform;
        }

        $status = "{$req->platform}_status";
        if(Bot::where('id', $req->bot_id)->where('creator_id', $this->user->id)->count() > 0)
        {
          if(Bot::where('id', $req->bot_id)->first()->$status == '0')
          {
            $response = ['status'=>'error', 'code'=>'PLATFORM_NOT_CONNECTED', 'message'=>['en'=>'Bot platform not connected', 'ru'=>'Платформа бота не подключена']];
            return response()->json($response, 500);
          }
          $mailing->bot_id = $req->bot_id;
        }

        $mailing->save();

        $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$mailing, 'message'=>['en'=>'The mailer was successfully connected', 'ru'=>'Рассылка была успешно создана']];
        return response()->json($response);

    }

    public function update($id, Request $req)
    {
      foreach($req->only(['name', 'bot_id', 'status', 'media_id', 'text_message', 'platform']) as $value){
        if(is_null($value))
        {
          $response = ['status'=>'error', 'code'=>'PARAMS_CANT_BE_NULL', 'message'=>['en'=>'Params can not be null', 'ru'=>'Переданные параметры не должны быть пустыми']];
          return response()->json($response, 500);
        }
      }

      $mailing = Mailing::where('id',$id)->where('creator_id',$this->user->id);
      if($mailing->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'MAILING_NOT_EXIST', 'message'=>['en'=>'Mailing not exist', 'ru'=>'Рассылки не существует']];
        return response()->json($response, 500);
      }

      $mailing = $mailing->first();

      if($mailing->status == '3'){
        $response = ['status'=>'error', 'code'=>'MAILING_ALREADU_RUN', 'message'=>['en'=>'Mailing already run, please waiting when was stopped', 'ru'=>'Рассылки уже запущена, подождите пока она завершится!']];
        return response()->json($response, 500);
      }

      foreach ($req->only(['name', 'bot_id', 'status', 'media_id', 'text_message', 'platform']) as $key => $value)
      {
        if($key == 'bot_id' && Bot::where('id', $value)->where('creator_id', $this->user->id)->count() == 0)
        {
          $response = ['status'=>'error', 'code'=>'BOT_NOT_FOUND', 'message'=>['en'=>'Bot id not found', 'ru'=>'Бот не найден']];
          return response()->json($response, 500);
        }

        if($key == 'platform' && (!in_array($value, ['vk', 'tg', 'vb'])))
        {
          $response = ['status'=>'error', 'code'=>'PLATFORM_NOT_FOUD', 'message'=>['en'=>'Platform not found, request please vk or vb or tg', 'ru'=>'Платформа не найдена, отправьте пожалуйста vk или vb или tg']];
          return response()->json($response, 500);
        }

        $status = "{$value}_status";
        if($key == 'platform' && Bot::where('id', $mailing->bot_id)->first()->$status == '0')
        {
          $response = ['status'=>'error', 'code'=>'PLATFORM_NOT_CONNECTED', 'message'=>['en'=>'Bot platform not connected', 'ru'=>'Платформа бота не подключена']];
          return response()->json($response, 500);
        }

        if($key == 'media_id' && (MediaLib::where('id', $value)->where('creator_id', $this->user->id)->count() == 0 && $value != '0'))
        {
          $response = ['status'=>'error', 'code'=>'MEDIA_NOT_FOUD', 'message'=>['en'=>'Media not found', 'ru'=>'Медиафайл не найден']];
          return response()->json($response, 500);
        }

        $mailing->{$key} = $value;
      }
      $mailing->save();

      if($mailing->status == '1')
      {
        $mailing->status = '3';
        $mailing->save();

        $this->start($mailing->platform, $mailing->bot_id, $mailing->text_message, $mailing->media_id);
        \DB::reconnect();
        $mailing->status = '0';
        $mailing->save();
      }
      $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$mailing, 'message'=>['en'=>'The mailer was successfully updated', 'ru'=>'Рассылка была успешно обновлена']];
      return response()->json($response);
    }

    public function delete($id)
    {
      $mailing = Mailing::where('id', $id)->where('creator_id',$this->user->id);
      if($mailing->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'MAILINGS_NOT_EXISTS', 'message'=>['en'=>'Mailing not exist', 'ru'=>'Рассылки не существует']];
        return response()->json($response, 500);
      }
      $mailing = $mailing->first();
      $mailing->delete();
      $response = ['status'=>'ok', 'code'=>'SUCCESS', 'message'=>['en'=>'The mailer was successfully deleted', 'ru'=>'Рассылка была успешно удалена']];
      return response()->json($response);
    }
}
