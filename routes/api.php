<?php

use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AmenityController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\ListingAccessController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\GeoController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ListingController;
use App\Http\Controllers\Api\V1\ListingTypeController;
use App\Http\Controllers\Api\V1\MetaController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OwnerController;
use App\Http\Controllers\Api\V1\WishlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Auth — public
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/google',   [AuthController::class, 'googleSignIn']);
    Route::post('/auth/login',    [AuthController::class, 'login']);
    Route::post('/auth/refresh',  [AuthController::class, 'refresh']);

    // Listings — public browse (no owner contact in response for guests)
    Route::get('/listings', [ListingController::class, 'index']);

    // Banners — public
    Route::get('/banners/active', [BannerController::class, 'active']);

    // Geo — public
    Route::get('/geo/divisions',               [GeoController::class, 'divisions']);
    Route::get('/geo/districts/{division_id}', [GeoController::class, 'districts']);
    Route::get('/geo/upazilas/{district_id}',  [GeoController::class, 'upazilas']);
    Route::get('/geo/unions/{upazila_id}',     [GeoController::class, 'unions']);

    // Meta — public
    Route::get('/meta/roles',           [MetaController::class, 'roles']);
    Route::get('/meta/listing-types',   [MetaController::class, 'types']);
    Route::get('/meta/listing-facings', [MetaController::class, 'facings']);
    Route::get('/meta/links',           [MetaController::class, 'links']);

    // Amenities — public read, protected write
    Route::get('/amenities', [AmenityController::class, 'index']);

    // Listing types — public read, protected write
    Route::get('/listing-types', [ListingTypeController::class, 'index']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        // Listings — auth required (detail + map expose owner contact)
        Route::get ('/listings/nearby',                       [ListingController::class, 'nearby']); // must be before /{id}
        Route::get ('/listings/{id}',                         [ListingController::class, 'show']);
        Route::post('/listings/{listingId}/request-access',   [ListingAccessController::class, 'request']);

        // Auth
        Route::post  ('/auth/logout',          [AuthController::class, 'logout']);
        Route::delete('/auth/account',         [AuthController::class, 'deleteAccount']);
        Route::patch ('/auth/register/details', [AuthController::class, 'registerDetails']);
        Route::patch ('/auth/register/avatar',  [AuthController::class, 'registerAvatar']);

        // Wishlist
        Route::get ('/wishlist',                         [WishlistController::class, 'index']);
        Route::post('/wishlist/{listing_id}/toggle',     [WishlistController::class, 'toggle']);


        // Owner
        Route::middleware('owner')->group(function () {
            Route::get   ('/owner/listings',                          [OwnerController::class, 'index']);
            Route::get   ('/owner/access-requests',                   [ListingAccessController::class, 'ownerIndex']);
            Route::post  ('/owner/access-requests/{id}/accept',       [ListingAccessController::class, 'accept']);
            Route::post  ('/owner/access-requests/{id}/reject',       [ListingAccessController::class, 'reject']);
            Route::post  ('/listings',                  [OwnerController::class, 'store']);
            Route::post  ('/listings/{id}/photos',      [OwnerController::class, 'uploadPhotos']);
            Route::patch ('/listings/{id}/location',    [OwnerController::class, 'updateLocation']);
            Route::post  ('/listings/{id}/submit',      [OwnerController::class, 'submit']);
            Route::post  ('/listings/{id}/mark-rented', [OwnerController::class, 'markAsRented']);
            Route::patch ('/listings/{id}/owner-info', [OwnerController::class, 'updateOwnerInfo']);
            Route::patch ('/listings/{id}',             [OwnerController::class, 'update']);
            Route::delete('/listings/{id}',             [OwnerController::class, 'destroy']);
        });

        // Chat
        Route::get ('/chats',                    [ChatController::class, 'index']);
        Route::post('/chats',                    [ChatController::class, 'start']);
        Route::get ('/chats/{id}/messages',      [ChatController::class, 'messages']);
        Route::post('/chats/{id}/messages',      [ChatController::class, 'sendMessage']);
        Route::post('/chats/{id}/accept',        [ChatController::class, 'acceptRequest']);
        Route::post('/chats/{id}/reject',        [ChatController::class, 'rejectRequest']);

        // Amenities — write (admin/owner protected)
        Route::post  ('/amenities',      [AmenityController::class, 'store']);
        Route::patch ('/amenities/{id}', [AmenityController::class, 'update']);
        Route::delete('/amenities/{id}', [AmenityController::class, 'destroy']);

        // Listing types — write (admin/owner protected)
        Route::post  ('/listing-types',      [ListingTypeController::class, 'store']);
        Route::patch ('/listing-types/{id}', [ListingTypeController::class, 'update']);
        Route::delete('/listing-types/{id}', [ListingTypeController::class, 'destroy']);

        // Device / FCM
        Route::post('/device/fcm-token', [DeviceController::class, 'registerFcmToken']);
        Route::get ('/device/sessions',  [DeviceController::class, 'sessions']);

        // User
        Route::patch('/user/location', [UserController::class, 'updateLocation']);

        // Notifications
        Route::get  ('/notifications',                  [NotificationController::class, 'index']);
        Route::get  ('/notifications/unread-count',     [NotificationController::class, 'unreadCount']);
        Route::patch('/notifications/read-all',         [NotificationController::class, 'markAllRead']);
        Route::patch('/notifications/{id}/read',        [NotificationController::class, 'markRead']);

        // Admin API
        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::get   ('/dashboard',                  [AdminController::class, 'dashboard']);
            Route::get   ('/listings',                   [AdminController::class, 'listings']);
            Route::get   ('/listings/{id}',              [AdminController::class, 'showListing']);
            Route::get   ('/users',                      [AdminController::class, 'users']);
            Route::get   ('/users/{id}/sessions',        [AdminController::class, 'userSessions']);
            Route::get   ('/sessions',                   [AdminController::class, 'allSessions']);
            Route::post  ('/listings/{id}/approve',      [AdminController::class, 'approveListing']);
            Route::post  ('/listings/{id}/reject',       [AdminController::class, 'rejectListing']);
            Route::patch ('/listings/{id}',              [AdminController::class, 'updateListing']);
            Route::delete('/listings/{id}',              [AdminController::class, 'deleteListing']);
            Route::patch ('/users/{id}',                 [AdminController::class, 'updateUser']);
            Route::delete('/users/{id}',                 [AdminController::class, 'deleteUser']);

            // Banners
            Route::get   ('/banners',                    [AdminController::class, 'allBanners']);
            Route::post  ('/banners',                    [AdminController::class, 'storeBanner']);
            Route::get   ('/banners/{id}',               [AdminController::class, 'showBanner']);
            Route::patch ('/banners/{id}',               [AdminController::class, 'updateBanner']);
            Route::delete('/banners/{id}',               [AdminController::class, 'deleteBanner']);
            Route::post  ('/banners/{id}/images',        [AdminController::class, 'addBannerImage']);
            Route::delete('/banners/images/{imageId}',   [AdminController::class, 'deleteBannerImage']);
            Route::patch ('/banners/images/{imageId}',   [AdminController::class, 'updateBannerImage']);
        });
    });
});
