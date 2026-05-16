<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\RegisterAvatarRequest;
use App\Http\Requests\Auth\RegisterDetailsRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        return ApiResponse::success($this->auth->register($request->validated()), null, 201);
    }

    public function registerDetails(RegisterDetailsRequest $request): JsonResponse
    {
        $this->auth->updateDetails($request->user(), $request->validated());
        return ApiResponse::success(['registration_step' => 3], 'Details saved');
    }

    public function registerAvatar(RegisterAvatarRequest $request): JsonResponse
    {
        $url = $this->auth->updateAvatar($request->user(), $request->file('avatar'));
        return ApiResponse::success(['avatar_url' => $url], 'Registration complete');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            return ApiResponse::success($this->auth->login($request->email, $request->password));
        } catch (UnauthorizedHttpException $e) {
            return ApiResponse::error($e->getMessage(), 'INVALID_CREDENTIALS', 401);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->user());
        return ApiResponse::success(null, 'Logged out');
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $token = $this->auth->refreshToken($request->refresh_token);
            return ApiResponse::success(['access_token' => $token]);
        } catch (UnauthorizedHttpException $e) {
            return ApiResponse::error($e->getMessage(), 'INVALID_REFRESH_TOKEN', 401);
        }
    }
}
