<?php


namespace App\CustomLibs;


use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

class DatabaseLib
{

    public function __construct($base='mysql', $host, $username, $password, $database){

      $opt = [
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES   => false,
      ];

      $this->pdo = new \PDO($base.':host='.$host.';dbname='.$database.';charset=utf8', $username, $password, $opt);
    }

    public function exq($query, $flag = false){

        $result = $this->pdo->query($query);
        if($flag || preg_match("/delete|update/imu", $query)) return $result;

        if(preg_match("/insert/imu", $query)){
            $response  = (int)$this->pdo->lastInsertId();
            return $response;

        }
        
        if(!$result->rowCount()){
          $result_fin['count_rows'] = 0;
          return $result_fin;
        }
        $result_fin = $result->fetch();
        $result_fin['count_rows'] = $result->rowCount();

        return $result_fin;

    }

}
