<?php


namespace App\CustomLibs;


use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

class OneSecondMail
{

    public function __construct(){
      $this->email = hash('crc32', md5(microtime(true).rand(1,2423423423)));
      $domains = ['1secmail.net', '1secmail.org'];
      $this->domain = $domains[rand(0, count($domains) - 1)];
    }

    function api($method){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, 'https://www.1secmail.com/api/v1/'.$method);
      curl_setopt($ch,CURLOPT_TIMEOUT,25000);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt ($ch, CURLOPT_PROXY, $this->proxy_random);
      curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->loginpassw);
      curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($ch);
      curl_close($ch);
      return $response;
    }

    public function wait_email($sec, $event_data){
      $data = [];
      $time = microtime(true);
      while($data == []){
        if(microtime(true) - $time > $sec)
          return false;
        $req_data = $this->api("?action=getMessages&login={$this->email}&domain={$this->domain}");
        $data = json_decode($req_data, true);
      }
      return $this->read_email($data[0]['id']);
    }

    public function read_email($id){

      $get_list = json_decode($this->api("?action=readMessage&login={$this->email}&domain={$this->domain}&id={$id}"), true);
      return $get_list['body'];

    }

    public function get_link($text){
      preg_match("/uuid=([^\"]+)/", $text, $match);
      return $this->get_image_for($match[1]);
    }

    public function get_image_for($uuid){
      $text = file_get_contents("https://api.selfie2anime.com/analysis/me?uuid=".$uuid);
      preg_match('/outgoing([^\"]+)/', $text, $match);
      return "https://selfie2anime.com/outgoing".$match[1];
    }

}
