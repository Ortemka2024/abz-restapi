<?php

namespace Tests\Feature\V1;

use App\Helpers\Api\V1\ApiResponseHelper;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    protected function getExpectedSuccessResponse(array $data, string $message = '')
    {
        return json_decode(
            ApiResponseHelper::success($data, $message)->getContent(),
            true
        );
    }

    protected function getExpectedErrorResponse(string $message, array $fails = [])
    {
        return json_decode(
            ApiResponseHelper::error($message, $fails)->getContent(),
            true
        );
    }

    protected function getNewUserData()
    {
        Position::factory()->create();

        $user = User::factory()->make()->toArray();

        $filePath = public_path(User::photoPath('photo_1.jpg'));

        $photo = new UploadedFile($filePath, 'photo_1.jpg', 'image/jpeg', null, true);

        $user['photo'] = $photo;

        return $user;
    }

    protected function getValidToken()
    {
        $response = $this->getJson(route('api.token.generate'));

        return $response->json('token');
    }
}
