<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OwnerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $listings = $request->user()->listings()
            ->with('photos')
            ->withCount('chats as inquiries')
            ->get()
            ->map(fn ($l) => [
                'listing'   => $l,
                'status'    => $l->status,
                'views'     => $l->views,
                'inquiries' => $l->inquiries,
            ]);

        return response()->json($listings);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'         => 'required|string|max:255',
            'area'          => 'required|string',
            'type'          => 'required|in:Family,Bachelor,Student,Couple,Sublet',
            'price'         => 'required|integer|min:1',
            'beds'          => 'required|integer|min:0',
            'baths'         => 'required|integer|min:0',
            'road_and_house'=> 'nullable|string',
            'deposit'       => 'nullable|integer|min:0',
            'size'          => 'nullable|integer|min:0',
            'description'   => 'nullable|string',
            'coord_x'       => 'nullable|numeric',
            'coord_y'       => 'nullable|numeric',
            'amenities'     => 'nullable|array',
            'amenities.*'   => 'string',
            'photos'        => 'nullable|array',
            'photos.*'      => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'code' => 'VALIDATION_ERROR'], 400);
        }

        $listing = Listing::create([
            'owner_id'      => $request->user()->id,
            'title'         => $request->title,
            'area'          => $request->area,
            'road_and_house'=> $request->road_and_house,
            'type'          => $request->type,
            'price'         => $request->price,
            'deposit'       => $request->deposit,
            'beds'          => $request->beds,
            'baths'         => $request->baths,
            'size'          => $request->size,
            'description'   => $request->description,
            'coord_x'       => $request->coord_x,
            'coord_y'       => $request->coord_y,
            'amenities'     => $request->amenities ?? [],
            'status'        => 'pending',
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = Storage::disk(config('filesystems.default'))->put('listings/'.$listing->id, $photo);
                $url  = Storage::disk(config('filesystems.default'))->url($path);

                ListingPhoto::create([
                    'listing_id' => $listing->id,
                    'url'        => $url,
                    'position'   => $index,
                ]);
            }
        }

        return response()->json([
            'id'      => $listing->id,
            'message' => 'Listing submitted. Goes live after 24h review.',
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $listing = Listing::where('id', $id)->where('owner_id', $request->user()->id)->first();

        if (! $listing) {
            return response()->json(['message' => 'Listing not found', 'code' => 'NOT_FOUND'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'       => 'sometimes|string|max:255',
            'area'        => 'sometimes|string',
            'type'        => 'sometimes|in:Family,Bachelor,Student,Couple,Sublet',
            'price'       => 'sometimes|integer|min:1',
            'beds'        => 'sometimes|integer|min:0',
            'baths'       => 'sometimes|integer|min:0',
            'deposit'     => 'nullable|integer|min:0',
            'size'        => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'amenities'   => 'nullable|array',
            'amenities.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'code' => 'VALIDATION_ERROR'], 400);
        }

        $listing->update($request->only([
            'title', 'area', 'road_and_house', 'type', 'price', 'deposit',
            'beds', 'baths', 'size', 'description', 'coord_x', 'coord_y', 'amenities',
        ]));

        return response()->json($listing->fresh('photos'));
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $listing = Listing::where('id', $id)->where('owner_id', $request->user()->id)->first();

        if (! $listing) {
            return response()->json(['message' => 'Listing not found', 'code' => 'NOT_FOUND'], 404);
        }

        $listing->delete();

        return response()->json(['message' => 'Listing deleted']);
    }
}