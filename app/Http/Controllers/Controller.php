<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
    * @OA\Info(title="Lichi API", version="0.5")
    * @OA\Tag(
    *   name="Auth",
    *   description="Работа с сессией"
    * )
    * @OA\Tag(
    *   name="Storage",
    *   description="Работа с хранилищами"
    * )
    * @OA\Tag(
    *   name="Bots",
    *   description="Работа с ботами"
    * )
    * @OA\Tag(
    *   name="Media",
    *   description="Работа с медиабиблиотекой"
    * )
    * @OA\Tag(
    *   name="Mailing",
    *   description="Работа с рассылкой"
    * )
    * @OA\SecurityScheme(
    *     type="http",
    *     description="Login with email and password to get the authentication token",
    *     name="Token based Based",
    *     in="header",
    *     scheme="bearer",
    *     bearerFormat="JWT",
    *     securityScheme="apiAuth",
    * )
    */
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
