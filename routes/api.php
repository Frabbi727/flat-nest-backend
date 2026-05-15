<?php

use App\Http\Controllers\Api\V1\AmenityController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ListingTypeController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\GeoController;
use App\Http\Controllers\Api\V1\ListingController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OwnerController;
use App\Http\Controllers\Api\V1\WishlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Auth — public
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login',    [AuthController::class, 'login']);
    Route::post('/auth/refresh',  [AuthController::class, 'refresh']);

    // Listings — public
    Route::get('/listings',      [ListingController::class, 'index']);
    Route::get('/listings/{id}', [ListingController::class, 'show']);

    // Geo — public
    Route::get('/geo/divisions',               [GeoController::class, 'divisions']);
    Route::get('/geo/districts/{division_id}', [GeoController::class, 'districts']);
    Route::get('/geo/upazilas/{district_id}',  [GeoController::class, 'upazilas']);
    Route::get('/geo/unions/{upazila_id}',     [GeoController::class, 'unions']);

    // Amenities — public read, protected write
    Route::get('/amenities', [AmenityController::class, 'index']);

    // Listing types — public read, protected write
    Route::get('/listing-types', [ListingTypeController::class, 'index']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post ('/auth/logout',           [AuthController::class, 'logout']);
        Route::patch('/auth/register/details', [AuthController::class, 'registerDetails']);
        Route::patch('/auth/register/avatar',  [AuthController::class, 'registerAvatar']);

        // Wishlist
        Route::get   ('/wishlist',                    [WishlistController::class, 'index']);
        Route::post  ('/wishlist/{listing_id}',       [WishlistController::class, 'save']);
        Route::delete('/wishlist/{listing_id}',       [WishlistController::class, 'remove']);

        // Owner
        Route::middleware('owner')->group(function () {
            Route::get   ('/owner/listings',            [OwnerController::class, 'index']);
            Route::post  ('/listings',                  [OwnerController::class, 'store']);
            Route::post  ('/listings/{id}/photos',      [OwnerController::class, 'uploadPhotos']);
            Route::patch ('/listings/{id}/location',    [OwnerController::class, 'updateLocation']);
            Route::post  ('/listings/{id}/submit',      [OwnerController::class, 'submit']);
            Route::patch ('/listings/{id}',             [OwnerController::class, 'update']);
            Route::delete('/listings/{id}',             [OwnerController::class, 'destroy']);
        });

        // Chat
        Route::get ('/chats',                    [ChatController::class, 'index']);
        Route::post('/chats',                    [ChatController::class, 'start']);
        Route::get ('/chats/{id}/messages',      [ChatController::class, 'messages']);
        Route::post('/chats/{id}/messages',      [ChatController::class, 'sendMessage']);

        // Amenities — write (admin/owner protected)
        Route::post  ('/amenities',      [AmenityController::class, 'store']);
        Route::patch ('/amenities/{id}', [AmenityController::class, 'update']);
        Route::delete('/amenities/{id}', [AmenityController::class, 'destroy']);

        // Listing types — write (admin/owner protected)
        Route::post  ('/listing-types',      [ListingTypeController::class, 'store']);
        Route::patch ('/listing-types/{id}', [ListingTypeController::class, 'update']);
        Route::delete('/listing-types/{id}', [ListingTypeController::class, 'destroy']);

        // Notifications
        Route::get  ('/notifications',                  [NotificationController::class, 'index']);
        Route::patch('/notifications/{id}/read',        [NotificationController::class, 'markRead']);
        Route::patch('/notifications/read-all',         [NotificationController::class, 'markAllRead']);
    });
});