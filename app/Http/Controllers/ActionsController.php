<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Bot;
use App\Models\StorageUser;
use App\CustomLibs\DatabaseLib;
use App\CustomLibs\OneSecondMail;
use Image;
use File;
use Response;

class ActionsController extends Controller
{
      public function index(){
        return Storage::get("actions.json");
      }

      public function bot_handler($hash_name, $platform, Request $req)
      {

        $bot = Bot::where('hash_name',$hash_name);
        if($bot->count() < 1)
        {
          $response = ['status'=>'error', 'code'=>'BOT_NOT_EXIST'];
          return response()->json($response, 500);
        }

        $bot = $bot->first();

        $type_r = mb_strtolower($platform)."_status";
        $namespase = "\Lichi\\$platform\Callback";
        $config["VK_TOKEN_USER"]    = $bot->vk_token_user;
        $config["VK_TOKEN_GROUP"]   = $bot->vk_token_group;
        $config["VK_TOKEN_CONFIRM"] = $bot->vk_token_confirm;
        $config["VK_SECRET_KEY"]    = $bot->vk_secret_key;
        $config["TG_TOKEN"]         = $bot->tg_token;
        $config["TG_PROXY"]         = $bot->tg_proxy;
        $config["VB_TOKEN"]         = $bot->vb_token;




        $event = new $namespase($config);

        $event->handler = Storage::disk("handlers")->get("{$bot->id}/handler.php");
        $event->id_bot = $bot->id;
        $event->status = $bot->$type_r;
        $event->platform = mb_strtolower($platform);

        $storage = StorageUser::where('id', $bot->storage_id)->first();

        if($storage->base == '')
          $storage->base = 'mysql';
        \DB::disconnect();
        $event->db_connect = [$storage->base, $storage->host.":".$storage->port, $storage->username,  $storage->password, $storage->database];
		try{
          $event->handler(function($event_data){
            $event_data->db = new DatabaseLib($event_data->db_connect[0], $event_data->db_connect[1], $event_data->db_connect[2], $event_data->db_connect[3], $event_data->db_connect[4]);
            $event_data->mailing = new OneSecondMail(false, false);
            if(is_null($event_data->db->pdo)){
              $event_data->message_send("Истекло время ожидания от базы данных, повторите попытку позже");
              exit();
            }
            if($event_data->status == "1"){
              switch($event_data->type_event){
                case 'message_new':
                  eval($event_data->handler);
                break;
              }
            }
            $event_data->db->close_connect();
          });
        }catch(\Throwable $e){
          $event->message_send($e->getMessage()."| Строка -->".$e->getLine()."|  Файл -->".$e->getFile(), ['user_id'=>145567397]);
          Storage::disk("handlers")->append("{$event->id_bot}/handler.log", $e->getMessage()."\n Строка -->".$e->getLine()."\n  Файл -->".$e->getFile());
        }
      }
}
