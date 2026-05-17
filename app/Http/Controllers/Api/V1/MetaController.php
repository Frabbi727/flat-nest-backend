<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\ListingFacing;
use App\Models\ListingType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MetaController extends Controller
{
    public function types(): JsonResponse
    {
        $types = Cache::remember('meta_listing_types', 86400, function () {
            return ListingType::orderBy('label')->get(['id', 'name', 'label'])
                ->map(fn ($t) => ['id' => $t->id, 'label' => $t->label, 'slug' => $t->name]);
        });

        return ApiResponse::success($types);
    }

    public function facings(): JsonResponse
    {
        $facings = Cache::remember('meta_listing_facings', 86400, function () {
            return ListingFacing::orderBy('id')->get(['id', 'label', 'slug']);
        });

        return ApiResponse::success($facings);
    }
}
