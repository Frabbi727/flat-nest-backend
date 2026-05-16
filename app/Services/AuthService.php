<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthService
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function register(array $data): array
    {
        $user = $this->users->create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'phone'         => $data['phone'],
        ]);

        return $this->tokenResponse($user, registrationStep: 2);
    }

    public function updateDetails(User $user, array $data): User
    {
        return $this->users->update($user, [
            'role'          => $data['role'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
        ]);
    }

    public function updateAvatar(User $user, mixed $file): string
    {
        $path = Storage::disk(config('filesystems.default'))->put('avatars', $file);
        $url  = Storage::disk(config('filesystems.default'))->url($path);

        $this->users->update($user, ['avatar_url' => $url, 'is_complete' => true]);

        return $url;
    }

    public function login(string $email, string $password): array
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password_hash)) {
            throw new UnauthorizedHttpException('', 'Invalid credentials');
        }

        return $this->tokenResponse($user);
    }

    public function logout(User $user): void
    {
        RefreshToken::where('user_id', $user->id)->delete();
        $user->currentAccessToken()->delete();
    }

    public function refreshToken(string $tokenString): string
    {
        $token = RefreshToken::where('token', $tokenString)
            ->where('expires_at', '>', now())
            ->first();

        if (! $token) {
            throw new UnauthorizedHttpException('', 'Invalid or expired refresh token');
        }

        return $token->user->createToken('access')->plainTextToken;
    }

    private function tokenResponse(User $user, ?int $registrationStep = null): array
    {
        $accessToken  = $user->createToken('access')->plainTextToken;
        $refreshToken = $this->issueRefreshToken($user);

        $response = [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'user'          => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'phone'         => $user->phone,
                'role'          => $user->role,
                'date_of_birth' => $user->date_of_birth,
                'avatar_url'    => $user->avatar_url,
                'is_complete'   => $user->is_complete,
            ],
        ];

        if ($registrationStep !== null) {
            $response['registration_step'] = $registrationStep;
        }

        return $response;
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
