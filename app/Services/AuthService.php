<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\DeviceSession;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthService
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function register(array $data, ?string $ip = null): array
    {
        $user = $this->users->create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'phone'         => $data['phone'],
        ]);

        return $this->tokenResponse($user, registrationStep: 1, ip: $ip);
    }

    public function updateDetails(User $user, array $data): array
    {
        if ($user->is_complete && $user->role !== $data['role']) {
            throw new ConflictHttpException('Your account role is already set and cannot be changed.');
        }

        $user = $this->users->update($user, [
            'role' => $data['role'],
        ]);

        return ['user' => $this->userArray($user), 'registration_step' => 2];
    }

    public function updateAvatar(User $user, mixed $file): array
    {
        $path = Storage::disk('public')->put('avatars', $file);
        $url  = Storage::disk('public')->url($path);

        $user = $this->users->update($user, ['avatar_url' => $url, 'is_complete' => true]);

        return ['user' => $this->userArray($user->fresh()), 'registration_step' => 3];
    }

    public function googleSignIn(string $idToken, ?string $ip = null): array
    {
        $payload = $this->verifyGoogleToken($idToken);

        if (! $payload) {
            throw new UnauthorizedHttpException('', 'Invalid Google token');
        }

        $googleId = $payload['sub'];
        $email    = $payload['email'];
        $name     = $payload['name'] ?? $email;

        $user = $this->users->findByGoogleId($googleId);

        if (! $user) {
            $user = $this->users->findByEmail($email);

            if ($user) {
                $this->users->update($user, ['google_id' => $googleId]);
                $user = $user->fresh();
            } else {
                $user = $this->users->create([
                    'name'      => $name,
                    'email'     => $email,
                    'google_id' => $googleId,
                ]);
            }
        }

        $step = $user->is_complete ? 3 : ($user->role ? 2 : 1);

        return $this->tokenResponse($user, registrationStep: $step, ip: $ip);
    }

    public function login(string $email, string $password, ?string $ip = null): array
    {
        $user = $this->users->findByEmail($email);

        if ($user && is_null($user->password_hash)) {
            throw new UnauthorizedHttpException('', 'This account uses Google Sign-In. Please sign in with Google.');
        }

        if (! $user || ! Hash::check($password, $user->password_hash)) {
            throw new UnauthorizedHttpException('', 'Invalid credentials');
        }

        $step = $user->is_complete ? 3 : ($user->role ? 2 : 1);

        return $this->tokenResponse($user, registrationStep: $step, ip: $ip);
    }

    public function deleteAccount(User $user): void
    {
        $user->delete();
    }

    public function logout(User $user): void
    {
        DeviceSession::where('user_id', $user->id)
            ->whereNull('logged_out_at')
            ->update(['logged_out_at' => now(), 'fcm_token' => null]);

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

    private function verifyGoogleToken(string $idToken): ?array
    {
        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $payload  = $response->json();
        $clientId = config('services.google.client_id');

        if (($payload['aud'] ?? null) !== $clientId) {
            return null;
        }

        return $payload;
    }

    private function tokenResponse(User $user, ?int $registrationStep = null, ?string $ip = null): array
    {
        $newToken     = $user->createToken('access');
        $accessToken  = $newToken->plainTextToken;
        $refreshToken = $this->issueRefreshToken($user);

        DeviceSession::create([
            'user_id'         => $user->id,
            'access_token_id' => $newToken->accessToken->id,
            'ip_address'      => $ip,
            'logged_in_at'    => now(),
        ]);

        $response = [
            'access_token'      => $accessToken,
            'refresh_token'     => $refreshToken,
            'registration_step' => $registrationStep,
            'user'              => $this->userArray($user),
        ];

        return $response;
    }

    private function userArray(User $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'role'          => $user->role,
            'date_of_birth' => $user->date_of_birth,
            'avatar_url'    => $user->avatar_url,
            'is_complete'   => $user->is_complete,
        ];
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
