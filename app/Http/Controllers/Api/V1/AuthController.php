<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8',
            'phone'    => 'required|regex:/^01[3-9]\d{8}$/|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'code' => 'VALIDATION_ERROR'], 400);
        }

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'phone'         => $request->phone,
        ]);

        $accessToken  = $user->createToken('access')->plainTextToken;
        $refreshToken = $this->issueRefreshToken($user);

        return response()->json([
            'user_id'           => $user->id,
            'email'             => $user->email,
            'name'              => $user->name,
            'access_token'      => $accessToken,
            'refresh_token'     => $refreshToken,
            'registration_step' => 2,
        ]);
    }

    public function registerDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role'          => 'required|in:renter,owner',
            'date_of_birth' => 'required|date|before:-18 years',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'code' => 'VALIDATION_ERROR'], 400);
        }

        $request->user()->update([
            'role'          => $request->role,
            'date_of_birth' => $request->date_of_birth,
        ]);

        return response()->json(['message' => 'Details saved', 'registration_step' => 3]);
    }

    public function registerAvatar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'code' => 'VALIDATION_ERROR'], 400);
        }

        $path = Storage::disk(config('filesystems.default'))->put('avatars', $request->file('avatar'));
        $url  = Storage::disk(config('filesystems.default'))->url($path);

        $request->user()->update(['avatar_url' => $url, 'is_complete' => true]);

        return response()->json(['message' => 'Registration complete', 'avatar_url' => $url]);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'code' => 'VALIDATION_ERROR'], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password_hash)) {
            return response()->json(['message' => 'Invalid credentials', 'code' => 'INVALID_CREDENTIALS'], 401);
        }

        $accessToken  = $user->createToken('access')->plainTextToken;
        $refreshToken = $this->issueRefreshToken($user);

        return response()->json([
            'user_id'           => $user->id,
            'email'             => $user->email,
            'name'              => $user->name,
            'role'              => $user->role,
            'is_complete'       => $user->is_complete,
            'access_token'      => $accessToken,
            'refresh_token'     => $refreshToken,
            'registration_step' => $user->is_complete ? null : ($user->role ? 3 : 2),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        RefreshToken::where('user_id', $request->user()->id)->delete();
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function refresh(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'code' => 'VALIDATION_ERROR'], 400);
        }

        $token = RefreshToken::where('token', $request->refresh_token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $token) {
            return response()->json(['message' => 'Invalid or expired refresh token', 'code' => 'INVALID_REFRESH_TOKEN'], 401);
        }

        $accessToken = $token->user->createToken('access')->plainTextToken;

        return response()->json(['access_token' => $accessToken]);
    }

    private function issueRefreshToken(User $user): string
    {
        $tokenString = (string) Str::uuid();

        RefreshToken::create([
            'user_id'    => $user->id,
            'token'      => $tokenString,
            'expires_at' => now()->addDays(30),
        ]);

        return $tokenString;
    }
}