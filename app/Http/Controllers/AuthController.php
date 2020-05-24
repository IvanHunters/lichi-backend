<?php
namespace App\Http\Controllers;

use App\User;
use App\Http\Requests\RegisterRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JWTAuth;
use Hash;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
  /**
  *
  *
  * @OA\Post(
  *     path="/api/auth/registration",
  *     summary="Регистрация пользователя",
  *     tags={"Auth"},
  *     @OA\Parameter(
  *         name="name",
  *         in="query",
  *         description="Имя пользоваьтеля",
  *         required=true,
  *         @OA\Schema(
  *             type="string",
  *             format="string32"
  *         )
  *     ),
  *     @OA\Parameter(
  *         name="email",
  *         in="query",
  *         description="Почта пользователя",
  *         required=true,
  *         @OA\Schema(
  *             type="string",
  *             format="string32"
  *         )
  *     ),
  *     @OA\Parameter(
  *         name="password",
  *         in="query",
  *         description="Пароль пользователя",
  *         required=true,
  *         @OA\Schema(
  *             type="string",
  *             format="string32"
  *         )
  *     ),
  *     @OA\Response(response="200", description="Авторизует пользователя")
  * )
  *
  * @OA\Post(
  *     path="/api/auth/login",
  *     summary="Авторизация пользователя",
  *     tags={"Auth"},
  *     @OA\Parameter(
  *         name="email",
  *         in="query",
  *         description="Почта пользователя",
  *         required=true,
  *         @OA\Schema(
  *             type="string",
  *             format="string32"
  *         )
  *     ),
  *     @OA\Parameter(
  *         name="password",
  *         in="query",
  *         description="Пароль пользователя",
  *         required=true,
  *         @OA\Schema(
  *             type="string",
  *             format="string32"
  *         )
  *     ),
  *     @OA\Response(response="200", description="Авторизует пользователя")
  * )
  *
  * @OA\Post(
  *     path="/api/auth/change_password",
  *     summary="Изменение пароля пользователя",
  *     tags={"Auth"},
  *     @OA\Parameter(
  *         name="new_password",
  *         in="query",
  *         description="Новый пароль пользователя",
  *         required=true,
  *         @OA\Schema(
  *             type="string",
  *             format="string32"
  *         )
  *     ),
  *     @OA\Response(response="200", description="Изменяет пароль пользователя"),
  *     security={{"apiAuth": {}}}
  * )
  *
  * @OA\Get(
  *     path="/api/auth/me",
  *     summary="Получение информации о пользователе",
  *     tags={"Auth"},
  *     @OA\Response(response="200", description="Выводит информацию о пользователе"),
  *     security={{"apiAuth": {}}}
  * )
  *
  * @OA\Post(
  *     path="/api/auth/refresh",
  *     summary="Получение долгоживущего токена пользователя",
  *     tags={"Auth"},
  *     @OA\Response(response="200", description="Обновляет токен пользователя"),
  *     security={{"apiAuth": {}}}
  * )
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
  }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
     public function login(Request $request): JsonResponse
     {
       $credentials = $request->only(['email', 'password']);
       try {
         if (!$token = JWTAuth::attempt($credentials)) {
           throw new Exception('invalid_credentials');
         }
         $this->data = [
           'status' => true,
           'code' => 200,
           'data' => [
           '_token' => $token
         ],
          'err' => null
         ];
       } catch (Exception $e) {
         $this->data['err']['message'] = $e->getMessage();
         $this->data['code'] = 401;
       } catch (JWTException $e) {
         $this->data['err']['message'] = 'Could not create token';
         $this->data['code'] = 500;
       }
       return response()->json($this->data, $this->data['code']);
     }

     public function login_vk(Request $request){
       $url = "http://lichi-social.ru/auth/vk";
       $secret = "f2BeXyxevyAuauCSe5t2";
       $client = 7448703;
       $vk_token = $request->access_token;
       $user_id = $request->user_id;

       $userData = User::where('vk_id', $user_id);

       if ($userData->count() == 0) {
         $user = new User();
         $user->name = "User";
         $user->email = "$user_id@ya.ru";
         $user->password = Hash::make($user_id."password_vk");
         $user->vk_id = $user_id;
         $user->vk_token = $vk_token;
         $user->fb_id = 0;
         $user->ggl_id = 0;
         $user->save();

         $token = JWTAuth::attempt(['email'=>$user->email, 'password'=>$user->vk_id."password_vk"]);

         $this->data = [
           'status' => true,
           'code' => 200,
           'data' => [
             '_token' => $token
           ],
           'err' => null
          ];
       }else{
         $userData = $userData->first();
         $token = JWTAuth::attempt(['email'=>$userData->email, 'password'=>$userData->vk_id."password_vk"]);
         $this->data = [
           'status' => true,
           'code' => 200,
           'data' => [
             '_token' => $token
           ],
           'err' => null
         ];
       }
       return response()->json($this->data);
     }

    /**
     * User registration
     */
    public function registration(Request $req)
    {
        $name = $req->name;
        $email = $req->email;
        $password = $req->password;

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->vk_id = 0;
        $user->vk_token = 'none';
        $user->fb_id = 0;
        $user->ggl_id = 0;
        $user->save();

        return response()->json(['message' => 'Successfully registration!']);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
     public function me(): JsonResponse
     {
     $this->data = [
       'status' => true,
       'code' => 200,
       'data' => [
         'User' => auth()->user()
        ],
        'err' => null
      ];
     return response()->json($this->data);
     }

     public function logout(): JsonResponse
     {
       auth()->logout();
       $data = [
         'status' => true,
         'code' => 200,
         'data' => [
         'message' => 'Successfully logged out'
       ],
        'err' => null
       ];
       return response()->json($data);
     }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
     public function refresh(): JsonResponse
      {
        $data = [
          'status' => true,
          'code' => 200,
          'data' => [
          '_token' => auth()->refresh()
        ],
          'err' => null
        ];
        return response()->json($data, 200);
      }

      public function change_password(Request $req)
      {

          $password = $req->new_password;

          $user = User::where('id', auth()->user()->id);
          $user->password = Hash::make($password);
          $user->save();

          return response()->json(['message' => 'Successfully change password']);
      }
}
