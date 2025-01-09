<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Api\V1\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserIndexRequest;
use App\Http\Requests\Api\V1\UserStoreRequest;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\UsersCollection;
use App\Models\User;
use App\Services\TinyPNGService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    protected $tinyPNGService;

    public function __construct(TinyPNGService $tinyPNGService)
    {
        $this->tinyPNGService = $tinyPNGService;
    }

    public function index(UserIndexRequest $request)
    {
        $data = $request->validated();

        $count = $data['count'] ?? 5;

        $users = User::orderBy('id', 'desc')->paginate($count);

        if ($users->isEmpty() && $request->query('page', 1) > $users->lastPage()) {
            return ApiResponseHelper::error('Page not found', [], 404);
        }

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

        $response = [
            'page' => $currentPage,
            'total_pages' => $totalPages,
            'total_users' => $totalUsers,
            'count' => $perPage,
            'links' => [
                'next_url' => $nextUrl,
                'prev_url' => $prevUrl,
            ],
            'users' => new UsersCollection($users),
        ];

        return ApiResponseHelper::success($response);
    }

    public function show($userId)
    {
        if (! is_numeric($userId)) {
            return ApiResponseHelper::error(
                'The user with the requested id does not exist',
                ['userId' => ['The user must be an integer.']],
                404
            );
        }

        $user = User::find($userId);

        if (! $user) {
            return ApiResponseHelper::error('User not found', [], 404);
        }

        return ApiResponseHelper::success([
            'user' => new UserResource($user),
        ]);
    }

    public function store(UserStoreRequest $request)
    {
        $data = $request->validated();

        $photo = $request->file('photo');

        $extension = $photo->getClientOriginalExtension();
        $processedContent = $this->tinyPNGService->processImage($photo, $extension);

        $fileName = Str::uuid().'.'.$extension;

        $filePath = public_path(User::photoPath($fileName));

        file_put_contents($filePath, $processedContent);

        $data['photo'] = $fileName;

        $data['password'] = Hash::make('password');

        $user = User::create($data);

        return ApiResponseHelper::success(
            ['user_id' => $user->id],
            'New user successfully registered',
            201
        );
    }
}
