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
      $rows = $PDO->exq("SELECT * FROM lichi_users WHERE platform ='{$platform}'", true);

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

      $users = [];

      while($row = $rows['result']->fetch()){
        if(count($users) == 100 && $platform == "vk"){
          $tokens =
          ['be5ca1c0afa05292c8ac98e21b97f29955109bc85c6fc456e6952212330389fd852782a0221bf405353ab',
          '01a326440d90cb1cac7148a461a43c65e7e568ef02ab92ad082ef7192ec914565a69270e5473f1dc5096c',
          'f5d012ce4de75908f56f3b2a4dcfde7c1dd916a2befb56ed5fb9aa643d82ec6c7930a5da041647101cb8b',
          '8795f27758ee78970b66255b5b0ec8a989c5bbaf5fa7f8dcf4490e3df9bd2fa349f6dad5664299ae6005b',
          '8b1e1350f097180c96c777869c881afd96322fb2c862c2891bcd9286a6a1f43f0d5507a30c9d0231fca25',
          'd3d21592436c6824f7615e4067e8d0390940dec49e11c7f7121b92f6931816fc378e32df0e452e97d2c4a',
          'ca1f865df7d3992477841058cc854595752eefcce73f54142b860943fc9447bf584fea6e5d4b6a0a8f9d4',
          '627242a6942f713439c80951d22698ee27674bce61d9590400eb07da3e876869eb264035ab2e532ece45a',
          '98c48606f6c6170e1406debe860e4a12c2f6c14010587129cc1284c101797135022c96470ef120e66e667',
          '95a20d435a6a8abaee355d22afaec8d8f54ce8d71230effcd8fe22a0ead6d7815edfb5b6309f326554f2e',];

          $event->token_group = $tokens[rand(0, count($tokens) - 1)];

          $users = implode(",", $users);
          $arrayParams['user_ids'] = $users;
          $arrayParams['message'] = $message;
          $arrayParams['random_id'] = rand(1, 435345345345093);
          $arrayParams['keyboard'] = $event->keyboard_construct([["type"=>"link","link"=>"https://vk.com/odivodi?w=app6471849_-148441127","text"=>"Премиум 🔥"]]);
          if($media_id != '0')
          {
            $arrayParams['attachment'] = $media;
          }
          $data = $event->CallHowGroup('messages.send', $arrayParams);
          $users = [];
        }else{
          if($platform == "vk")
            $users[] = $row['user_id'];
          else{
            $event->user_id = $row['user_id'];
            $event->chat_id = $row['user_id'];

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

      if(count($users) > 0 && $platform == "vk"){
        $users = implode(",", $users);
        $arrayParams['user_ids'] = $users;
        $arrayParams['message'] = $message;
        $arrayParams['random_id'] = rand(1, 435345345345093);
        //$arrayParams['keyboard'] = $event->keyboard_construct([["type"=>"link","link"=>"https://vk.com/app7583351","text"=>"Лицо Знаменитости"]]);
        if($media_id != '0')
        {
          $arrayParams['attachment'] = $media;
        }
        $data = $event->CallHowGroup('messages.send', $arrayParams);
        sleep(5);
        unset($users);
      }

      $mailing = Mailing::where('id',$id)->where('creator_id',$this->user->id);
      $mailing->status = '0';
      $mailing->save();

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
