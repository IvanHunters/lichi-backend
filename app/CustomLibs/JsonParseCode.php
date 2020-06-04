<?php


namespace App\CustomLibs;


use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use App\Models\MediaLib;

class JsonParseCode
{

    private $regexp_message = array(), $static_message = array(), $undefined_message = array(), $code = "";
    public function __construct($id){
      $this->id = $id;
    }

    public function get_handler()
    {
      return Storage::disk("handlers")->get("{$this->id}/handler.json");
    }

    public function set_handler($json){
      $this->json = $json;
      $array_handler = json_decode($json, true);
      if(is_null($array_handler))
        return ['status'=>'error', 'code'=>'INVALID_JSON', 'message'=>['eng'=>'Invalid JSON', 'ru'=>'Не валидный JSON']];
      $this->work_data = $array_handler['handlers'];
      $this->handle($this->work_data);
      $this->create_file();

      return ['status'=>'ok', 'code'=>'SUCCESS', 'items'=>[]];
    }

    private function handle($array_handlers)
    {
      foreach($array_handlers as $id=>$handler){
        $array_chain = $handler['chain'];
        $this->handle_chain($array_chain, $id);
      }
      $php = [];
      $php[0][1] = $this->handle_first;
      $php[0][2][0] = 'switch($event_data->text_lower){';
      $php[1][2][1][0] = $this->static_message;
      $php[1][2][2] = ' default:';
      $php[1][2][3][0] = $this->regexp_message;
      $php[1][2][4][0] = $this->undefined_message;
      $php[1][3][0] = ' break;';
      $php[2] = '}';
      $this->create_code($php);
    }

    private function handle_chain($array_chain, $id)
    {
      $php = [];
      if($array_chain[0]['code'] == "handle_first_message")
      {
        $need_return = $array_chain[0]['fields'][0]['value'];

        unset($array_chain[0]);
        sort($array_chain);
        $this->handle_first[0] = '$user_info = $event_data->db->exq("SELECT status, user_id FROM lichi_users WHERE user_id = \'{$event_data->user_id}\' and platform=\'{$event_data->platform}\'");';
        $this->handle_first[1] = '$handle = false;';
        $this->handle_first[2][0] = 'if($user_info["count_rows"] == 0){';
        $this->handle_first[2][1][0] = '$event_data->db->exq("INSERT INTO lichi_users SET user_id = \'{$event_data->user_id}\', platform=\'{$event_data->platform}\'");';
        $this->handle_first[2][1][1] = $this->handle_chain_actions($array_chain, 1, "");
        $this->handle_first[2][1][2] = 'if($event_data->is_ref) $event_data->db->exq("INSERT INTO lichi_authorization SET user_id = \'{$event_data->user_id}\', platform = \'{$event_data->platform}\' hash=\'{$this->ref}\'");';
        if($need_return){
          $this->handle_first[2][1][3] = 'return true;';
        }
        $this->handle_first[2][2][0] = '}';
      }elseif($array_chain[0]['code'] == "handle_message")
      {
        $handle_message = $array_chain[0];
        unset($array_chain[0]);
        sort($array_chain);
        if(!preg_match("/{[^}]+}/imu", $handle_message['fields'][0]['value']))
        {
          $this->static_message($handle_message['fields'][0]['value'], $array_chain, $id);
        }
        else
        {
          $this->regexp_message($handle_message['fields'][0]['value'], $array_chain, $id);
        }
      }elseif($array_chain[0]['code'] == "handle_undefined_message")
      {
        $need_response = $array_chain[0]['fields'][0]['value'];
        unset($array_chain[0]);
        sort($array_chain);
        if($need_response){
          $this->undefined_message[0] = 'if(!$handle){';
          $this->undefined_message[1][0] = $this->handle_chain_actions($array_chain, 1, "");
          $this->undefined_message[2] = '}';
        }
      }
    }

    private function static_message($message, $array_chain, $id)
    {
        $this->static_message[$id][] = "    case \"".preg_replace("/\n/imu",'\n', mb_strtolower(addslashes($message)))."\":";
        $this->static_message[$id][] = $this->handle_chain_actions($array_chain, 1, $message);
        $this->static_message[$id][] = "    break;";
    }

    private function regexp_message($message, $array_chain, $id)
    {
        $this->regexp_message[$id][] = '    $pattern_message = preg_replace("/{[^}]+}/imu", "([\w\W]+)", "'.preg_replace("/\n/imu",'\n', mb_strtolower(addslashes($message))).'");';
        $this->regexp_message[$id][] = '    preg_match_all("/{[^}]+}/imu", "'.preg_replace("/\n/imu",'\n', mb_strtolower(addslashes($message))).'", $words, PREG_PATTERN_ORDER);';
        $this->regexp_message[$id][] = '    if(preg_match("/$pattern_message/imu", $event_data->text_lower , $value_words)){';
        $this->regexp_message[$id][] = '       unset($value_words[0]);';
        $this->regexp_message[$id][] = '       $value_words = array_values($value_words);';
        $this->regexp_message[$id][] = '       $words = $words[0];';
        $this->regexp_message[$id][] = '       $handle = true;';
        $this->regexp_message[$id][] = $this->handle_chain_actions($array_chain, 2, $message);
        $this->regexp_message[$id][] = "    }";
    }

    private function handle_chain_actions($array_chain, $type_message, $original_message)
    {
      $tmp_id = 0;
      $tmp_array = [];
      foreach($array_chain as $chain){
        switch($chain['code']){
          case 'send_message':
            $message = $chain['fields'][0]['value'];
            $message = preg_replace("/@user_id/imu", '{$event_data->user_id}', $message);
            $message = preg_replace("/@username/imu", '{$event_data->username}', $message);
            $tmp_array[][] = '       $message = "'.preg_replace("/\n/imu",'\n', addslashes($message)).'";';
            if(is_array($chain['fields'][1]['value'])){
              $keyboard = $chain['fields'][1]['value'];
              $isset_media = false;
            }else{
              $media = $chain['fields'][1]['value'];
              $isset_media = true;
              $keyboard = $chain['fields'][2]['value'];
            }

            if($type_message == 2){
              $tmp_array[] = '       foreach($words as $id => $word){';
              $tmp_array[] = '         $word = "@".preg_replace("/[\{\}]/imu", "", $word);';
              $tmp_array[] = '         $message = preg_replace("/$word/imu", $value_words[$id], $message);';
              $tmp_array[] = '       }';
            }

            if(is_array($keyboard) || $isset_media)
            {
              $attach = [];

              if(is_array($keyboard) and count($keyboard) > 0)
              {
                $tmp_array[] = '       $keyboard = $event_data->keyboard_construct('.json_encode($keyboard, JSON_UNESCAPED_UNICODE).');';
                $attach[] = '\'keyboard\'=>$keyboard';
              }
              if($isset_media)
              {
                if($media > 0)
                {
                  $data = MediaLib::where('id', $media)->first();
                  $file_path = storage_path("app".$data->path);
                  if($data->type == '1')
                    $tmp_array[] = '       $media = $event_data->upload_photo("'.$file_path.'", true, false);';
                  else
                    $tmp_array[] = '       $media = $event_data->upload_document("'.$file_path.'");';
                  $attach[] = '\'attachment\'=>$media';
                }
              }
                if(count($attach) > 0)
                  $tmp_array[] = '       $event_data->message_send($message, ['.implode(",",$attach).']);';
                else
                  $tmp_array[] = '       $event_data->message_send($message);';
            }
            else
            {
              $tmp_array[] = '       $event_data->message_send($message);';
            }

            break;

          case 'send_http':
            $params = [];
            $url = $chain['fields'][0]['value'];
            $method = $chain['fields'][1]['value'] == "GET"? false: true;
            $params_array = $chain['fields'][2]['value'][0];
            foreach($params_array as $id=>$value){
              $params[$value]=$chain['fields'][2]['value'][1][$id];
            }
            $tmp_array[] = '       $event_data->provider("'.$url.'", "'.$method.'", \''.json_encode($params).'\');';
            break;
        }
        $tmp_id++;
      }

      return $tmp_array;
    }

    public function handle_regular($array)
    {
      $this->php[] = "switch(".'$event_data->text_lower'."){\n\t";
      foreach($array as $message => $response){
        $this->php[] = "case: '".preg_replace("/\n/imu",'\n', mb_strtolower(addslashes($message)))."':\n\t\t";
        if(isset($response['keyboard']))
          $this->php[] = '$event_data->message_send("'.addslashes($response['message']).'");'."\n\t";
        else
          $this->php[] = '$event_data->message_send("'.addslashes($response['message']).'", ["keyboard"=>$event_data->keyboard_construct('.$response['keyboard'].')]);'."\n\t";
        $this->php[] = "break;\n\n\t";
      }
      $this->php[] = "default:\n\t";

      $this->handle_regexp($this->work_data['regexp']);
    }

    private function return_n_tab($n){
      $return = "";
      for($i=0;$i<$n;$i++)
        $return.="\t";
      return $return;
    }

    private function create_code($array, $iteracion = 1){
      foreach ($array as $key => $value) {
        if(is_array($value))
          $this->code .= $this->create_code($value, $iteracion+1)."\n";
        else
          $this->code .= $value."\n";
      }
    }

    private function create_file(){
      Storage::disk("handlers")->put("{$this->id}/handler.php", $this->code);
      Storage::disk("handlers")->put("{$this->id}/handler.json", $this->json);
    }
}
