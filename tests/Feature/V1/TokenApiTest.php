<?php

namespace Tests\Feature\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TokenApiTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_token_generation_successfully()
    {
        $response = $this->getJson(route('api.token.generate'));

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'token',
        ]);

        $token = $response->json('token');
        $this->assertTrue(Cache::has($token));

        $expiration = Cache::get($token);
        $this->assertTrue(now()->addMinutes(40)->greaterThanOrEqualTo($expiration));
    }

    public function test_access_is_denied_without_valid_token()
    {
        $response = $this->postJson(route('api.users.store'));

        $response->assertStatus(401);

        $expectedResponse = $this->getExpectedErrorResponse(
            'The token expired.',
        );

        $response->assertJson($expectedResponse);
    }

    public function test_access_is_denied_with_expired_token()
    {
        $token = Str::random(32);

        Cache::put($token, true, now()->addSeconds(1));

        sleep(2);

        $userData = $this->getNewUserData();

        $response = $this->postJson(route('api.users.store'), $userData, [
            'Token' => $token,
        ]);

        $response->assertStatus(401);

        $expectedResponse = $this->getExpectedErrorResponse(
            'The token expired.'
        );

        $response->assertJson($expectedResponse);
    }

    public function test_access_is_granted_with_valid_token()
    {
        $response = $this->getValidToken();

        $userData = $this->getNewUserData();

        $response = $this->postJson(route('api.users.store'), $userData, [
            'Token' => $this->getValidToken(),
        ]);

        $response->assertStatus(201);

        $newUser = User::find($response->json('user_id'));

        $photoPath = public_path(User::photoPath($newUser->photo));

        unlink($photoPath);
    }

    public function test_token_is_32_characters()
    {
        $token = $this->getValidToken();

        $this->assertEquals(32, strlen($token));
    }
}
