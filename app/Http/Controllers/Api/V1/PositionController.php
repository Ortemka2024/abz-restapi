<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Api\V1\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PositionCollection;
use App\Models\Position;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::all();

        if ($positions->isEmpty()) {
            return ApiResponseHelper::error('Positions not found', [], 404);
        }

        return ApiResponseHelper::success([
            'positions' => new PositionCollection($positions),
        ]);
    }
}
