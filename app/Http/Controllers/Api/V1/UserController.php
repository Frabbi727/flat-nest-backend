<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\User\UpdateLocationRequest;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function updateLocation(UpdateLocationRequest $request): JsonResponse
    {
        $request->user()->update([
            'last_lat' => $request->lat,
            'last_lng' => $request->lng,
        ]);

        return ApiResponse::success(null, 'Location updated');
    }
}
