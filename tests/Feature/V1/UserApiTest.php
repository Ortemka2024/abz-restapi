<?php

namespace Tests\Feature\V1;

use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\UsersCollection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class UserApiTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_user_creation_fails_without_token()
    {
        $response = $this->postJson(route('api.users.store'));

        $response->assertStatus(401);

        $expectedResponse = $this->getExpectedErrorResponse(
            'The token expired.',
        );

        $response->assertJson($expectedResponse);
    }

    public function test_user_creation_fails_with_missing_required_fields()
    {
        $token = $this->getValidToken();

        $data = [
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson(route('api.users.store'), $data, [
            'Token' => $token,
        ]);

        $response->assertStatus(422);

        $expectedResponse = $this->getExpectedErrorResponse(
            'Validation failed',
            [
                'name' => ['The name field is required.'],
                'phone' => ['The phone field is required.'],
                'position_id' => ['The position id field is required.'],
                'photo' => ['The photo field is required.'],
            ]
        );

        $response->assertJson($expectedResponse);
    }

    public function test_user_creation_fails_when_email_or_phone_already_exists()
    {
        $user = User::factory()->create();

        $email = $user->email;
        $phone = $user->phone;

        $newUserData = $this->getNewUserData();

        // Email already exist
        $user_1 = $newUserData;

        $user_1['email'] = $email;

        $response = $this->postJson(route('api.users.store'), $user_1, [
            'Token' => $this->getValidToken(),
        ]);

        $response->assertStatus(409);

        $response->assertJson([
            'message' => 'User with this phone or email already exist',
        ]);

        // Phone already exist
        $user_2 = $newUserData;

        $user_2['phone'] = $phone;

        $response = $this->postJson(route('api.users.store'), $user_2, [
            'Token' => $this->getValidToken(),
        ]);

        $response->assertStatus(409);

        $response->assertJson([
            'message' => 'User with this phone or email already exist',
        ]);
    }

    public function test_user_creation_successfully_with_valid_data()
    {
        $userData = $this->getNewUserData();

        $response = $this->postJson(route('api.users.store'), $userData, [
            'Token' => $this->getValidToken(),
        ]);

        $response->assertStatus(201);

        $expectedResponse = $this->getExpectedSuccessResponse(
            ['user_id' => 1],
            'New user successfully registered',
        );

        $response->assertJson($expectedResponse);

        $userId = $response->json('user_id');
        $this->assertNotNull(
            $userId,
            'User ID not returned in the response.'
        );

        $user = User::find($userId);
        $this->assertNotNull(
            $user,
            'User not found in the database.'
        );

        $photoPath = public_path(User::photoPath($user->photo));

        $this->assertTrue(
            file_exists($photoPath),
            "Photo {$photoPath} does not exist in the public directory."
        );

        unlink($photoPath);
    }

    public function test_show_user_fails_with_invalid_or_nonexistent_id()
    {
        $user = User::factory()->create();

        // str instead of int
        $response = $this->getJson(route('api.users.show', ['userId' => $user->name]));

        $response->assertStatus(404);

        $expectedResponse = $this->getExpectedErrorResponse(
            'The user with the requested id does not exist',
            ['userId' => ['The user must be an integer.']]
        );

        $response->assertJson($expectedResponse);

        // non-existent ID
        $response = $this->getJson(route('api.users.show', ['userId' => 1000]));

        $response->assertStatus(404);

        $expectedResponse = $this->getExpectedErrorResponse(
            'User not found',
        );

        $response->assertJson($expectedResponse);
    }

    public function test_show_user_successfully_with_valid_id()
    {
        $user = User::factory()->create();

        $response = $this->getJson(route('api.users.show', ['userId' => $user->id]));

        $response->assertStatus(200);

        $expectedResponse = $this->getExpectedSuccessResponse([
            'user' => new UserResource($user),
        ]);

        $response->assertJson($expectedResponse);
    }

    public function test_users_list_successfully_with_empty_database()
    {
        $response = $this->getJson(route('api.users.index'));

        $expectedResponse = $this->getExpectedSuccessResponse([
            'page' => 1,
            'total_pages' => 1,
            'total_users' => 0,
            'count' => 5,
            'links' => [
                'next_url' => null,
                'prev_url' => null,
            ],
            'users' => [],
        ]);

        $response->assertStatus(200)->assertJson($expectedResponse);
    }

    public function test_users_list_fails_with_invalid_count_parameter()
    {
        User::factory()->create();

        // if count is string
        $response = $this->getJson(route('api.users.index', ['count' => 'abc']));

        $response->assertStatus(422);

        $expectedResponse = $this->getExpectedErrorResponse(
            'Validation failed',
            ['count' => ['The count must be an integer.']],
        );

        $response->assertJson($expectedResponse);

        // if count is less than 1
        $response = $this->getJson(route('api.users.index', ['count' => -1]));

        $response->assertStatus(422);

        $expectedResponse = $this->getExpectedErrorResponse(
            'Validation failed',
            ['count' => ['The count must be at least 1.']],
        );

        $response->assertJson($expectedResponse);
    }

    public function test_users_list_default_count()
    {
        $response = $this->getJson(route('api.users.index'));

        $response->assertStatus(200);

        $response->assertJsonPath('count', 5);
    }

    public function test_users_list_fails_beyond_the_available_pages()
    {
        $response = $this->getJson(route('api.users.index', ['page' => '2']));

        $response->assertStatus(404);

        $expectedResponse = $this->getExpectedErrorResponse(
            'Page not found',
        );

        $response->assertJson($expectedResponse);
    }

    public function test_users_list_successfully()
    {
        $count = 5;

        User::factory()->count($count)->create();

        $users = User::orderBy('id', 'desc')->paginate($count);

        $currentPage = $users->currentPage();
        $totalPages = $users->lastPage();
        $totalUsers = $users->total();
        $perPage = $users->perPage();

        $nextUrl = $currentPage < $totalPages
            ? $users->url($currentPage + 1)
            : null;

        $prevUrl = $currentPage > 1
            ? $users->url($currentPage - 1)
            : null;

        $response = $this->getJson(route('api.users.index'));

        $expectedResponse = $this->getExpectedSuccessResponse([
            'page' => $currentPage,
            'total_pages' => $totalPages,
            'total_users' => $totalUsers,
            'count' => $perPage,
            'links' => [
                'next_url' => $nextUrl,
                'prev_url' => $prevUrl,
            ],
            'users' => new UsersCollection($users),
        ]);

        $response->assertStatus(200)->assertJson($expectedResponse);
    }

    public function test_users_list_contains_pagination_links()
    {
        $totalUsers = 15;
        $perPage = 5;

        User::factory()->count($totalUsers)->create();

        $response = $this->getJson(route('api.users.index', ['page' => 1]));

        $responseData = $response->json();

        $this->assertEquals(1, $responseData['page']);
        $this->assertEquals(3, $responseData['total_pages']);
        $this->assertEquals($totalUsers, $responseData['total_users']);
        $this->assertEquals($perPage, $responseData['count']);

        $this->assertNotNull($responseData['links']['next_url']);
        $this->assertNull($responseData['links']['prev_url']);

        $response = $this->getJson(route('api.users.index', ['page' => 3]));
        $responseData = $response->json();

        $this->assertEquals(3, $responseData['page']);
        $this->assertNull($responseData['links']['next_url']);
        $this->assertNotNull($responseData['links']['prev_url']);

        $response->assertStatus(200);
    }

    public function test_users_list_contains_user_collection()
    {
        $users = User::factory()->count(10)->create();

        $response = $this->getJson(route('api.users.index'));

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'page',
            'total_pages',
            'total_users',
            'count',
            'links' => [
                'next_url',
                'prev_url',
            ],
            'users' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'position',
                    'position_id',
                    'registration_timestamp',
                    'photo',
                ],
            ],
        ]);

        $responseData = $response->json();
        $this->assertCount(5, $responseData['users']);

        foreach ($responseData['users'] as $user) {
            $this->assertTrue(
                $users->pluck('id')->contains($user['id'])
            );
        }
    }
}
