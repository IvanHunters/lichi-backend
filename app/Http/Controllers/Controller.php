<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
    * @OA\Info(title="Lichi API", version="0.1")
    *
    * @OA\Tag(
    *   name="Storage",
    *   description="Работа с хранилищами"
    * )
    * @OA\Tag(
    *   name="Bots",
    *   description="Работа с ботами"
    * )
    */
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
