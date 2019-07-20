<?php

namespace App\Http\Middleware;

use Log;
use Exception;
use App\User;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

use Closure;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');

        if(!$token) {

            return response()->json([
                'status' => 401,
                'error' => 'Token required.'
            ], 401);
        }

        try {
            $credentials = JWT::decode($token, env('SECRET'), ['HS256']);
        } catch(ExpiredException $e) {

            return response()->json([
                'error' => 'Provided token is expired.'
            ], 400);
        } catch(Exception $e) {
            Log::error('Token decoding error:' . $e);

            return response()->json([
                'error' => 'An error while decoding.'
            ], 400);
        }

        $user = User::find($credentials->sub);

        if(!empty($user)){
            $request->auth = $user;
        }else{

            return response()->json([
                'error' => 'Provided token is invalid.'
            ], 400);
        }

        return $next($request);
    }
}
