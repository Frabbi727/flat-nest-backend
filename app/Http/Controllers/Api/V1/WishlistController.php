<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlist) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(['saved_ids' => $this->wishlist->getSavedIds($request->user())]);
    }

    public function toggle(Request $request, string $listingId): JsonResponse
    {
        $saved = $this->wishlist->toggle($request->user(), $listingId);
        return ApiResponse::success(['saved' => $saved], $saved ? 'Saved' : 'Removed');
    }
}
