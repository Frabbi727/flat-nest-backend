<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
        return response()->json($this->auth->register($request->validated()));
    }

    public function registerDetails(RegisterDetailsRequest $request): JsonResponse
    {
        $this->auth->updateDetails($request->user(), $request->validated());
        return response()->json(['message' => 'Details saved', 'registration_step' => 3]);
    }

    public function registerAvatar(RegisterAvatarRequest $request): JsonResponse
    {
        $url = $this->auth->updateAvatar($request->user(), $request->file('avatar'));
        return response()->json(['message' => 'Registration complete', 'avatar_url' => $url]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            return response()->json($this->auth->login($request->email, $request->password));
        } catch (UnauthorizedHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'INVALID_CREDENTIALS'], 401);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->user());
        return response()->json(['message' => 'Logged out']);
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $token = $this->auth->refreshToken($request->refresh_token);
            return response()->json(['access_token' => $token]);
        } catch (UnauthorizedHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'INVALID_REFRESH_TOKEN'], 401);
        }
    }
}
