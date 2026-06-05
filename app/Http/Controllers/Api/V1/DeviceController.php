<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Device\RegisterFcmTokenRequest;
use App\Models\DeviceSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function registerFcmToken(RegisterFcmTokenRequest $request): JsonResponse
    {
        DeviceSession::updateOrCreate(
            ['access_token_id' => $request->user()->currentAccessToken()->id],
            [
                'user_id'       => $request->user()->id,
                'fcm_token'     => $request->fcm_token,
                'device_type'   => $request->device_type,
                'device_model'  => $request->device_model,
                'ip_address'    => $request->ip(),
                'logged_in_at'  => now(),
                'logged_out_at' => null,
            ]
        );

        return ApiResponse::success(null, 'Device registered');
    }

    public function sessions(Request $request): JsonResponse
    {
        $currentTokenId = $request->user()->currentAccessToken()->id;

        $sessions = DeviceSession::where('user_id', $request->user()->id)
            ->orderByDesc('logged_in_at')
            ->get()
            ->map(fn ($s) => [
                'id'            => $s->id,
                'device_model'  => $s->device_model ?? 'Unknown device',
                'device_type'   => $s->device_type,
                'ip_address'    => $s->ip_address,
                'logged_in_at'  => $s->logged_in_at,
                'logged_out_at' => $s->logged_out_at,
                'is_active'     => is_null($s->logged_out_at),
                'is_current'    => $s->access_token_id === $currentTokenId,
            ]);

        return ApiResponse::success($sessions);
    }
}
