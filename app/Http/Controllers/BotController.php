<?php
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Models\Bot;
class BotController extends Controller
{
  /**
  *
  * @OA\Get(
  *     path="/api/auth/method/bots",
  *     tags={"Bots"},
  *     @OA\Response(response="200", description="Выводит ботов у пользователя")
  * )
  *
  * @OA\Get(
  *     path="/api/auth/method/bot",
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
  *     @OA\Response(response="200", description="Выводит информацию о боте пользователя")
  * )
  *
  * @OA\Post(
  *     path="/api/auth/method/bot",
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
  *     @OA\Parameter(
  *         name="preset_id",
  *         in="query",
  *         description="id пресета бота",
  *         required=true,
  *         @OA\Schema(
  *             type="integer",
  *             format="int64"
  *         ),
  *         style="form"
  *     ),
  *     @OA\Response(response="200", description="Создает бота пользователя")
  * )
  *
  * @OA\Put(
  *     path="/api/auth/method/bot",
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
  *         name="preset_id",
  *         in="query",
  *         description="id шаблона",
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
  *         name="vk_token_user",
  *         in="query",
  *         description="Токен пользователя Вконтакте",
  *         required=false,
  *         @OA\Schema(
  *             type="string",
  *             format="string32"
  *         )
  *     ),
  *     @OA\Parameter(
  *         name="vk_token_confirm",
  *         in="query",
  *         description="Confirm-токен для группы Вконтакте",
  *         required=false,
  *         @OA\Schema(
  *             type="string",
  *             format="string32"
  *         )
  *     ),
  *     @OA\Parameter(
  *         name="vk_secret_key",
  *         in="query",
  *         description="Секретный ключ для Вконтакте",
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
  *     @OA\Response(response="200", description="Обновляет информацию о боте пользователя")
  * )
  *
  * @OA\Delete(
  *     tags={"Bots"},
  *     path="/api/auth/method/bot",
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
  *     @OA\Response(response="200", description="Удаляет бота пользователя")
  * )
  */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'registration']]);
        $this->user = auth()->user();
    }

    public function get($id)
    {
        $bot = Bot::where('id',$id)->where('creator_id',$this->user->id);
        if($bot->count() < 1)
        {
          $response = ['status'=>'error', 'code'=>'BOT_NOT_EXIST'];
          return response()->json($response);
        }
        $bot = $bot->first();
        return response()->json($bot);
    }

    public function getList()
    {
      $bot = Bot::where('creator_id',$this->user->id);
      if($bot->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'BOTS_NOT_EXISTS'];
        return response()->json($response);
      }
      $bot = $bot->get(['id', 'name']);
      return response()->json($bot);
    }

    public function create(Request $req)
    {
        $bot = new Bot;

        $bot->name = $req->name;
        $bot->preset_id = $req->preset_id;
        $bot->creator_id = $this->user->id;
        $bot->storage_id = $req->storage_id;
        $bot->hash_name = md5("bot".$req->name.microtime(true).rand(1,1000));

        $bot->save();
        $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$bot];
        return response()->json($response);
    }

    public function update($id, Request $req)
    {
      $bot = Bot::where('id',$id)->where('creator_id',$this->user->id);
      if($bot->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'BOT_NOT_EXIST', 'items'=>$bot];
        return response()->json($response);
      }

      $bot = $bot->first();
      foreach ($req->input() as $key => $value)  $bot->{$key} = $value;
      $bot->save();

      $response = ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>$bot];
      return response()->json($response);
    }

    public function delete($id)
    {
      $bot = Bot::where('id', $id)->where('creator_id',$this->user->id);
      if($bot->count() < 1)
      {
        $response = ['status'=>'error', 'code'=>'BOTS_NOT_EXISTS'];
        return response()->json($response);
      }
      $bot->delete();
      $response = ['status'=>'ok', 'code'=>'SUCCESS'];
      return response()->json($response);
    }
}
