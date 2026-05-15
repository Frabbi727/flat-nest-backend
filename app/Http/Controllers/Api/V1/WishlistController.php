<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlist) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['saved_ids' => $this->wishlist->getSavedIds($request->user())]);
    }

    public function save(Request $request, string $listingId): JsonResponse
    {
        try {
            $this->wishlist->save($request->user(), $listingId);
            return response()->json(['message' => 'Saved']);
        } catch (NotFoundHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'NOT_FOUND'], 404);
        }
    }

    public function remove(Request $request, string $listingId): JsonResponse
    {
        $this->wishlist->remove($request->user(), $listingId);
        return response()->json(['message' => 'Removed']);
    }
}
