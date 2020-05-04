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

class AuthController extends Controller
{
  /**
  *
  * @OA\Post(
  *     path="/api/auth/login",
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
  *     path="/api/auth/registration",
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
  *     path="/api/auth/refresh",
  *     tags={"Auth"},
  *     @OA\Response(response="200", description="Обновляет токен пользователя")
  * )
  *
  * @OA\Post(
  *     path="/api/auth/me",
  *     tags={"Auth"},
  *     @OA\Response(response="200", description="Выводит информацию о пользователе")
  * )
  *
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
}
