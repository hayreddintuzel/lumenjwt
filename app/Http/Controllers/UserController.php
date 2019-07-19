<?php

namespace App\Http\Controllers;

use Validator;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Laravel\Lumen\Routing\Controller;
use App\User;
use htuzel\Hash\Hash;

class UserController extends Controller
{

    private $request;

    /**
     * Creates $request for controller
     *
     * @return void
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * Creates jwt token for user
     */
    protected function jwt(User $user) {
        $payload = [
            'iss' => "lumen-jwt",
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 60*60
        ];

     // As you can see we are passing `JWT_SECRET` as the second parameter that will
       // be used to decode the token in the future.

        return JWT::encode($payload, env('SECRET'));
    }

    /**
     * Create a new user on DB.
     *
     * @return Response
     */
    public function registerUser(Request $request)
    {

        $this->validate($this->request, [
            'name'      => 'required',
            'email'     => 'required',
            'password'  => 'required'
        ]);

        $user = User::where('email', $this->request->input('email'))->first();

        if (!$user) {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => $request->input('password'),  //TODO:needs improvement, we could not store password explicitly.
            ]);

            return response()->json([
                'token' => $this->jwt($user)
            ], 200);
        }

        return response()->json([
            'error' => 'User has been created before'
        ], 400);

    }

    /**
     * authanticates a user by generating JWT token
     *
     * @return Response
     */
    public function authenticateUser()
    {
        $this->validate($this->request, [
            'email'     => 'required',
            'password'  => 'required'
        ]);

        $user = User::where('email', $this->request->input('email'))->first();

        if ($this->request->input('password') == $user->password) {

            return response()->json([
                'token' => $this->jwt($user)
            ], 200);
        }

        return response()->json([
            'error' => 'Password is incorrect!'
        ], 410);

    }

    /**
     * generates hash string for authenticated user
     *
     * @return string $hash;
     */
    public function generateHash()
    {
        $hashObj = new Hash('md5','hayreddintuzel');
        $hash = $hashObj->getHash();
        return $hash;
    }
}
