<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Device\RegisterFcmTokenRequest;
use App\Models\DeviceSession;
use Illuminate\Http\JsonResponse;

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
}
