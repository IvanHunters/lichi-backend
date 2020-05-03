<?php


namespace App\CustomLibs;


use Illuminate\Database\Eloquent\Collection;
use App\Models\StorageUser;

class Beget
{
    public function __construct($login, $password){
      $this->login = $login;
      $this->password = $password;
    }

    public function addStorage()
    {
        $login = mb_substr(hash('adler32', rand(1, 100)."database-name".microtime(true).rand(1, 100)), 0, -1);
        $password = "WET".hash('adler32',rand(1, 100)."database-password".microtime(true).rand(1, 100))."PET";
        $params['input_data'] = json_encode(
                ["suffix"=>$login,
                "password"=>$password]);
        if($this->apiRequest("mysql/addDb", $params)){
          $this->setAccessStorage($login, $password);
          return ['login'=>"w999623p_".$login, 'password'=>$password];
        }else{
          return false;
        }
    }

    public function setAccessStorage($suffix, $password)
    {
      $params['input_data'] = json_encode(
              ["suffix"=>$suffix,
              "access"=>"%",
              "password"=>$password]);
      return $this->apiRequest("mysql/addAccess", $params);
    }

    public function dropStorage($suffix)
    {
        $params['input_data'] = json_encode(
                ["suffix"=>$suffix]);
        $this->apiRequest("mysql/dropDb", $params);
    }

    public function apiRequest($method, $params, $flag=false)
    {
        $url = "https://api.beget.com/api/{$method}";

        $params['login'] = $this->login;
        $params['passwd'] = $this->password;
        $params['input_format']='json';
        $params['output_format']='json';

        return $this->CurlRequest($url, $params);
    }

    private function CurlRequest($url, $params, $flag=false)
    {
        usleep(36400);
      	$ch = curl_init();
      	curl_setopt($ch, CURLOPT_URL, $url);
      	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, $flag);
      	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Expect:'));
      	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
      	$dataJson = curl_exec($ch);
      	$dataArray = json_decode($dataJson,true);
      	curl_close($ch);

        if($dataArray['status'] != "success") return false;
        return $dataArray;
    }
}
