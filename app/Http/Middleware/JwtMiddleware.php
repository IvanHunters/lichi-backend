<?php
namespace App\Http\Middleware;use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
class JwtMiddleware extends BaseMiddleware
{
/**
* Handle an incoming request.
*
* @param \Illuminate\Http\Request $request
* @param \Closure $next
* @return mixed
*/
  public function handle($request, Closure $next)
  {
    try {
      $user = JWTAuth::parseToken()->authenticate();
      if( !$user ) throw new Exception('User Not Found');
    } catch (Exception $e) {
      if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
          return response()->json(['error' => 'Token Invalid'], 405);
      }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
          return response()->json(['error' => 'Token expired'], 401);
      }else{
      if( $e->getMessage() === 'User Not Found') {
        return response()->json(['error' => 'User not found'], 402);
      }
      return response()->json(['error' => 'Authorization Token not found'], 403);
      }
    }
    return $next($request);
  }
}
