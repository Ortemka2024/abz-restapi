<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Api\V1\ApiResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TokenController extends Controller
{
    public function generateToken()
    {
        $token = Str::random(32);

        Cache::put($token, now(), now()->addMinutes(40));

        return ApiResponseHelper::success([
            'token' => $token,
        ]);
    }
}
