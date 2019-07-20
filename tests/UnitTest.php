<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\User;
use Firebase\JWT\JWT;


class UnitTest extends TestCase
{
    /**
     * A register test
     *
     * @return void
     */
    public function testRegister()
    {
        $response = $this->call('POST', '/register', ['name' => 'Hayreddin', 'email' => 'hayreddintuzel@mail.com', 'password' => '123456']);

        $this->assertEquals(200, $response->status());
        $this->seeInDatabase('users', ['email' => 'hayreddintuzel@mail.com']);
    }

    /**
     * A login test
     *
     * @return void
     */
    public function testLogin()
    {
        $response = $this->call('POST', '/login', ['email' => 'hayreddintuzel@mail.com', 'password' => '123456']);

        $this->assertEquals(200, $response->status());
    }

    /**
     * hash test
     *
     * @return void
     */
    public function testHash()
    {

        $user = User::create([
            'name' => 'test',
            'email' => 'test@mail.com',
            'password' => '123456',
        ]);

        $payload = [
            'iss' => "lumen-jwt",
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 60*60
        ];

        $token = JWT::encode($payload, env('SECRET'));

        $response = $this->withHeaders([
            'authorization' => $token,
        ])->json('GET', '/hash');

        $user->delete();

        $this->assertEquals(200, $response->status());
    }
}
