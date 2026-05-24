<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\HostelTypeResource;
use App\Models\HostelType;
use Illuminate\Http\JsonResponse;

class HostelTypeController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success(HostelTypeResource::collection(HostelType::all()));
    }
}
