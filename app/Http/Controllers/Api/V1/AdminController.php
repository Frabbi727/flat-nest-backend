<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ListingStatus;
use App\Enums\NotificationKind;
use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\BannerResource;
use App\Http\Resources\ListingResource;
use App\Models\AppNotification;
use App\Models\Banner;
use App\Models\BannerImage;
use App\Models\DeviceSession;
use App\Models\Listing;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $listings = Listing::all();
        $users    = User::where('role', '!=', 'admin')->get();

        $stats = [
            'active_listings'  => $listings->where('status', ListingStatus::Active)->count(),
            'total_users'      => $users->count(),
            'pending_approval' => $listings->where('status', ListingStatus::Pending)->count(),
            'total_listings'   => $listings->count(),
        ];

        // Daily counts for the last 14 days (oldest → newest)
        $days = collect(range(13, 0))->map(fn ($d) => now()->subDays($d)->toDateString());

        $listingCounts = Listing::selectRaw('DATE(created_at) as day, COUNT(*) as n')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('day')->pluck('n', 'day');

        $userCounts = User::where('role', '!=', 'admin')
            ->selectRaw('DATE(created_at) as day, COUNT(*) as n')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('day')->pluck('n', 'day');

        $listings_trend = $days->map(fn ($d) => (int) ($listingCounts[$d] ?? 0))->values();
        $users_trend    = $days->map(fn ($d) => (int) ($userCounts[$d]   ?? 0))->values();

        // Listings by status
        $by_status = $listings->groupBy(fn ($l) => $l->status->value)
            ->map->count()->toArray();

        // Listings by type (top 6)
        $by_type = Listing::with('listingType:id,label')
            ->select('listing_type_id')
            ->get()
            ->groupBy(fn ($l) => $l->listingType?->label ?? 'Unknown')
            ->map->count()
            ->sortDesc()
            ->take(6)
            ->map(fn ($count, $label) => ['type' => $label, 'count' => $count])
            ->values();

        $recent_listings = Listing::with('owner:id,name')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($l) => [
                'id'      => $l->id,
                'title'   => $l->title,
                'owner'   => $l->owner?->name,
                'area'    => $l->area,
                'price'   => $l->price,
                'status'  => $l->status->value,
                'created' => $l->created_at->diffForHumans(),
            ]);

        return ApiResponse::success([
            'stats'           => $stats,
            'listings_trend'  => $listings_trend,
            'users_trend'     => $users_trend,
            'by_status'       => $by_status,
            'by_type'         => $by_type,
            'recent_listings' => $recent_listings,
        ]);
    }

    public function listings(Request $request): JsonResponse
    {
        $query = Listing::with('owner:id,name')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('area', 'like', "%{$request->search}%")
                  ->orWhereHas('owner', fn ($q) => $q->where('name', 'like', "%{$request->search}%"));
            }))
            ->latest();

        $paginator = $query->paginate(20);

        $data = $paginator->map(fn ($l) => [
            'id'               => $l->id,
            'title'            => $l->title,
            'owner'            => $l->owner?->name,
            'owner_id'         => $l->owner_id,
            'area'             => $l->area,
            'type'             => $l->listingType?->label ?? '—',
            'price'            => $l->price,
            'deposit'          => $l->deposit,
            'beds'             => $l->beds,
            'baths'            => $l->baths,
            'size'             => $l->size,
            'description'      => $l->description,
            'status'           => $l->status->value,
            'rejection_reason' => $l->rejection_reason,
            'views'            => $l->views,
            'created'          => $l->created_at->diffForHumans(),
        ]);

        return ApiResponse::paginated($data, $paginator);
    }

    public function users(Request $request): JsonResponse
    {
        $query = User::where('role', '!=', 'admin')
            ->withCount('listings')
            ->when($request->role, fn ($q) => $q->where('role', $request->role))
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            }))
            ->latest();

        $paginator = $query->paginate(20);

        $data = $paginator->map(fn ($u) => [
            'id'          => $u->id,
            'name'        => $u->name,
            'email'       => $u->email,
            'phone'       => $u->phone ?? '—',
            'role'        => $u->role,
            'verified'    => $u->is_complete,
            'listings'    => $u->listings_count,
            'last_active' => $u->updated_at->diffForHumans(),
            'joined'      => $u->created_at->format('M Y'),
        ]);

        return ApiResponse::paginated($data, $paginator);
    }

    public function showListing(string $id): JsonResponse
    {
        $listing = Listing::with([
            'owner:id,name,email,phone',
            'photos',
            'amenities',
            'listingType',
            'facing',
            'division',
            'district',
            'upazila',
            'union',
        ])->findOrFail($id);

        return ApiResponse::success(new ListingResource($listing));
    }

    public function approveListing(string $id): JsonResponse
    {
        $listing = Listing::findOrFail($id);

        $listing->update(['status' => ListingStatus::Active]);

        AppNotification::create([
            'user_id'      => $listing->owner_id,
            'kind'         => NotificationKind::ListingApproved->value,
            'title'        => 'Your listing was approved!',
            'body'         => $listing->title . ' is now live.',
            'reference_id' => $listing->id,
        ]);

        app(FcmService::class)->sendToUser(
            $listing->owner_id,
            'Your listing was approved!',
            $listing->title . ' is now live.',
            ['kind' => NotificationKind::ListingApproved->value, 'reference_id' => $listing->id]
        );

        return ApiResponse::success(null, 'Listing approved');
    }

    public function rejectListing(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $listing = Listing::findOrFail($id);

        $listing->update([
            'status'           => ListingStatus::Rejected,
            'rejection_reason' => $request->reason,
        ]);

        AppNotification::create([
            'user_id'      => $listing->owner_id,
            'kind'         => NotificationKind::ListingRejected->value,
            'title'        => 'Your listing was rejected.',
            'body'         => $request->reason,
            'reference_id' => $listing->id,
        ]);

        app(FcmService::class)->sendToUser(
            $listing->owner_id,
            'Your listing was rejected.',
            $request->reason,
            ['kind' => NotificationKind::ListingRejected->value, 'reference_id' => $listing->id]
        );

        return ApiResponse::success(null, 'Listing rejected');
    }

    public function updateListing(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'title'             => 'sometimes|string|max:255',
            'area'              => 'sometimes|string|max:255',
            'road_and_house'    => 'sometimes|nullable|string|max:255',
            'price'             => 'sometimes|integer|min:0',
            'deposit'           => 'sometimes|nullable|integer|min:0',
            'beds'              => 'sometimes|integer|min:0',
            'baths'              => 'sometimes|integer|min:0',
            'size'              => 'sometimes|nullable|integer|min:0',
            'floor_no'          => 'sometimes|nullable|integer|min:0',
            'listing_type_id'   => 'sometimes|nullable|exists:listing_types,id',
            'facing_id'         => 'sometimes|nullable|exists:listing_facings,id',
            'available_from'    => 'sometimes|nullable|date',
            'description'       => 'sometimes|nullable|string',
            'road'              => 'sometimes|nullable|string|max:255',
            'house_name'        => 'sometimes|nullable|string|max:255',
            'block'             => 'sometimes|nullable|string|max:100',
            'section'           => 'sometimes|nullable|string|max:100',
            'coord_x'           => 'sometimes|nullable|numeric',
            'coord_y'           => 'sometimes|nullable|numeric',
            'owner_name'        => 'sometimes|nullable|string|max:255',
            'owner_phone'       => 'sometimes|nullable|string|max:20',
            'owner_alt_phone'   => 'sometimes|nullable|string|max:20',
            'owner_email'       => 'sometimes|nullable|email|max:255',
            'preferred_contact' => 'sometimes|nullable|in:call,whatsapp,both',
            'status'            => 'sometimes|string|in:draft,pending,active,rented,rejected',
            'rejection_reason'  => 'sometimes|nullable|string|max:500',
        ]);

        $listing    = Listing::findOrFail($id);
        $oldStatus  = $listing->status;
        $newStatus  = $request->has('status') ? ListingStatus::from($request->status) : null;

        $listing->update($request->only([
            'title', 'area', 'road_and_house', 'price', 'deposit', 'beds', 'baths',
            'size', 'floor_no', 'listing_type_id', 'facing_id', 'available_from',
            'description', 'road', 'house_name', 'block', 'section',
            'coord_x', 'coord_y',
            'owner_name', 'owner_phone', 'owner_alt_phone', 'owner_email', 'preferred_contact',
            'status', 'rejection_reason',
        ]));

        // Fire push notification when status changes
        if ($newStatus && $newStatus !== $oldStatus) {
            [$title, $body, $kind] = match ($newStatus) {
                ListingStatus::Active   => ['Your listing was approved!',          $listing->title . ' is now live.',       NotificationKind::ListingApproved],
                ListingStatus::Rejected => ['Your listing was rejected.',           $request->rejection_reason ?? 'See the rejection reason in the app.', NotificationKind::ListingRejected],
                ListingStatus::Pending  => ['Your listing is under review.',        $listing->title . ' has been sent back for review.', NotificationKind::ListingReview],
                default                 => [null, null, null],
            };

            if ($title) {
                AppNotification::create([
                    'user_id'      => $listing->owner_id,
                    'kind'         => $kind->value,
                    'title'        => $title,
                    'body'         => $body,
                    'reference_id' => $listing->id,
                ]);

                app(FcmService::class)->sendToUser(
                    $listing->owner_id,
                    $title,
                    $body,
                    ['kind' => $kind->value, 'reference_id' => $listing->id]
                );
            }
        }

        return ApiResponse::success(null, 'Listing updated');
    }

    public function updateUser(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name'        => 'sometimes|string|max:255',
            'email'       => 'sometimes|email|unique:users,email,' . $id,
            'phone'       => 'sometimes|nullable|string|max:20',
            'role'        => 'sometimes|in:renter,owner',
            'is_complete' => 'sometimes|boolean',
        ]);

        $user = User::where('role', '!=', 'admin')->findOrFail($id);
        $user->update($request->only(['name', 'email', 'phone', 'role', 'is_complete']));

        return ApiResponse::success(null, 'User updated');
    }

    public function deleteListing(string $id): JsonResponse
    {
        $listing = Listing::findOrFail($id);
        $listing->delete();

        return ApiResponse::success(null, 'Listing deleted');
    }

    public function deleteUser(string $id): JsonResponse
    {
        $user = User::where('role', '!=', 'admin')->findOrFail($id);
        $user->delete();

        return ApiResponse::success(null, 'User deleted');
    }

    public function userSessions(string $id): JsonResponse
    {
        User::findOrFail($id);

        $sessions = DeviceSession::where('user_id', $id)
            ->orderByDesc('logged_in_at')
            ->get()
            ->map(fn ($s) => [
                'id'            => $s->id,
                'device_model'  => $s->device_model ?? 'Unknown device',
                'device_type'   => $s->device_type ?? 'unknown',
                'ip_address'    => $s->ip_address,
                'logged_in_at'  => $s->logged_in_at,
                'logged_out_at' => $s->logged_out_at,
                'is_active'     => is_null($s->logged_out_at),
            ]);

        return ApiResponse::success($sessions);
    }

    public function allSessions(Request $request): JsonResponse
    {
        $query = DeviceSession::with('user:id,name,email,avatar_url')
            ->orderByDesc('logged_in_at');

        if ($request->filled('status')) {
            $query->when($request->status === 'active',   fn ($q) => $q->whereNull('logged_out_at'));
            $query->when($request->status === 'inactive', fn ($q) => $q->whereNotNull('logged_out_at'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%"));
        }

        $paginator = $query->paginate(20);

        $data = $paginator->map(fn ($s) => [
            'id'            => $s->id,
            'user'          => $s->user ? ['id' => $s->user->id, 'name' => $s->user->name, 'email' => $s->user->email] : null,
            'device_model'  => $s->device_model ?? 'Unknown device',
            'device_type'   => $s->device_type ?? 'unknown',
            'ip_address'    => $s->ip_address,
            'logged_in_at'  => $s->logged_in_at,
            'logged_out_at' => $s->logged_out_at,
            'is_active'     => is_null($s->logged_out_at),
        ]);

        return ApiResponse::paginated($data, $paginator);
    }

    public function allBanners(): JsonResponse
    {
        $banners = Banner::withCount('images')->latest()->get();
        return ApiResponse::success($banners);
    }

    public function storeBanner(Request $request): JsonResponse
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $banner = Banner::create($request->all());

        return ApiResponse::success($banner, 'Banner created successfully', 201);
    }

    public function showBanner(string $id): JsonResponse
    {
        $banner = Banner::with(['images' => fn ($q) => $q->orderBy('order')])->findOrFail($id);
        return ApiResponse::success(new BannerResource($banner));
    }

    public function updateBanner(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'is_active'   => 'sometimes|boolean',
        ]);

        $banner = Banner::findOrFail($id);
        $banner->update($request->all());

        return ApiResponse::success($banner, 'Banner updated successfully');
    }

    public function deleteBanner(string $id): JsonResponse
    {
        $banner = Banner::findOrFail($id);
        // Images will be deleted via model boot events
        $banner->delete();

        return ApiResponse::success(null, 'Banner deleted successfully');
    }

    public function addBannerImage(Request $request, string $id): JsonResponse
    {
        $banner = Banner::findOrFail($id);

        if ($banner->images()->count() >= 5) {
            return ApiResponse::error('Maximum 5 images allowed per banner', null, 422);
        }

        $request->validate([
            'image'      => 'required|image|max:10240', // 10MB raw limit, will compress
            'target_url' => 'nullable|url',
            'order'      => 'nullable|integer',
        ]);

        try {
            $path = $this->compressAndSaveImage($request->file('image'));

            $bannerImage = $banner->images()->create([
                'image_path' => $path,
                'target_url' => $request->target_url,
                'order'      => $request->order ?? 0,
                'is_active'  => true,
            ]);

            return ApiResponse::success($bannerImage, 'Image added successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to process image: ' . $e->getMessage());
        }
    }

    public function updateBannerImage(Request $request, string $imageId): JsonResponse
    {
        $request->validate([
            'target_url' => 'sometimes|nullable|url',
            'order'      => 'sometimes|integer',
            'is_active'  => 'sometimes|boolean',
        ]);

        $image = BannerImage::findOrFail($imageId);
        $image->update($request->all());

        return ApiResponse::success($image, 'Banner image updated');
    }

    public function deleteBannerImage(string $imageId): JsonResponse
    {
        $image = BannerImage::findOrFail($imageId);
        $image->delete(); // boots delete from storage

        return ApiResponse::success(null, 'Banner image deleted');
    }

    private function compressAndSaveImage($file, $targetSizeKb = 200)
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);

        $quality = 90;
        
        // Ensure directory exists
        if (!Storage::disk('public')->exists('banners')) {
            Storage::disk('public')->makeDirectory('banners');
        }

        $encoded = null;
        do {
            $encoded = $image->toWebp($quality);
            if ($encoded->size() / 1024 <= $targetSizeKb || $quality <= 10) {
                break;
            }
            $quality -= 10;
        } while (true);

        $filename = 'banners/' . uniqid() . '.webp';
        Storage::disk('public')->put($filename, (string) $encoded);
        
        return $filename;
    }
}
