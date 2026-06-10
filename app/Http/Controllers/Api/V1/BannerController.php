<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    public function active(): JsonResponse
    {
        $banner = Banner::where('is_active', true)
            ->with(['images' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            }])
            ->first();

        if (!$banner) {
            return ApiResponse::success(null);
        }

        return ApiResponse::success(new BannerResource($banner));
    }
}
