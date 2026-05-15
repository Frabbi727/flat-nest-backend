# FlatNest Backend — Build Guide

> Keep this file in your Laravel project root.
> Tick off each checkbox as you complete it.
> Each phase is independent — test before moving to the next.

---

## Stack

- **Framework:** Laravel 11 (PHP 8.2+)
- **Database:** PostgreSQL
- **Auth:** Laravel Sanctum
- **File Storage:** Cloudflare R2 (S3-compatible)
- **Admin Panel:** Filament v3
- **Real-time:** Laravel Broadcasting + Pusher
- **Local Dev:** Laravel Herd or `php artisan serve`

---

## Before You Start

```bash
# Install Laravel
composer create-project laravel/laravel flatnest-backend

cd flatnest-backend

# Install required packages
composer require laravel/sanctum
composer require league/flysystem-aws-s3-v3
composer require filament/filament:"^3.0" -W

# Publish Sanctum config
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Install Filament
php artisan filament:install --panels

# Run default migrations
php artisan migrate
```

---

## .env Setup (Local)

```env
APP_NAME=FlatNest
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=flatnest
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Cloudflare R2 (fill after creating bucket)
FILESYSTEM_DISK=r2
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_BUCKET=flatnest-photos
R2_ENDPOINT=https://xxxx.r2.cloudflarestorage.com
R2_PUBLIC_URL=https://pub-xxxx.r2.dev
R2_REGION=auto

# Pusher (fill after creating app)
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=ap2
```

---

## API Base URL

All Flutter API calls go to:

```
http://localhost:8000/api/v1
```

Configure this in `lib/core/network/api_client.dart` in the Flutter app.

---

---

# PHASE 1 — Auth

> **Goal:** Register, login, logout working. Flutter app can get a token.

## Checklist

- [ ] Create `users` migration
- [ ] Update `User` model
- [ ] Create `refresh_tokens` migration and model
- [ ] Create `AuthController`
- [ ] Add routes to `routes/api.php`
- [ ] Test all endpoints in Postman

---

## Step 1.1 — Users Migration

```bash
php artisan make:migration create_users_table --create=users
```

```php
// database/migrations/xxxx_create_users_table.php
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->string('email')->unique();
    $table->string('password_hash');
    $table->string('name');
    $table->string('phone')->unique();
    $table->string('role')->default('renter'); // renter | owner
    $table->date('date_of_birth')->nullable();
    $table->string('avatar_url')->nullable();
    $table->boolean('is_complete')->default(false);
    $table->timestamps();

    // Indexes
    $table->index('role');
    $table->index('phone');
});
```

---

## Step 1.2 — Refresh Tokens Migration

```bash
php artisan make:migration create_refresh_tokens_table --create=refresh_tokens
```

```php
Schema::create('refresh_tokens', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->text('token')->unique();
    $table->timestampTz('expires_at');
    $table->timestamps();

    // Indexes
    $table->index('user_id');
    $table->index('expires_at');
});
```

---

## Step 1.3 — User Model

```php
// app/Models/User.php
protected $primaryKey = 'id';
public $incrementing = false;
protected $keyType = 'string';

protected $fillable = [
    'email', 'password_hash', 'name', 'phone',
    'role', 'date_of_birth', 'avatar_url', 'is_complete'
];

protected $hidden = ['password_hash'];
```

---

## Step 1.4 — AuthController

```bash
php artisan make:controller Api/V1/AuthController
```

### POST /api/v1/auth/register (Step 1)
**Request:**
```json
{
    "name": "Rahim Uddin",
    "email": "rahim@example.com",
    "password": "secret123",
    "phone": "01712345678"
}
```
**Response 200:**
```json
{
    "user_id": "uuid",
    "email": "rahim@example.com",
    "name": "Rahim Uddin",
    "access_token": "token_string",
    "refresh_token": "uuid_string",
    "registration_step": 2
}
```
**Validation:**
```php
'name'     => 'required|string|max:100',
'email'    => 'required|email|unique:users',
'password' => 'required|min:8',
'phone'    => 'required|regex:/^01[3-9]\d{8}$/|unique:users',
```

---

### PATCH /api/v1/auth/register/details (Step 2)
**Headers:** `Authorization: Bearer {token}`
**Request:**
```json
{
    "role": "renter",
    "date_of_birth": "1995-06-15"
}
```
**Validation:**
```php
'role'           => 'required|in:renter,owner',
'date_of_birth'  => 'required|date|before:-18 years',
```

---

### PATCH /api/v1/auth/register/avatar (Step 3)
**Headers:** `Authorization: Bearer {token}`
**Request:** `multipart/form-data`
```
avatar: file (jpg/png, max 2MB)
```
**Response 200:**
```json
{
    "message": "Registration complete",
    "avatar_url": "https://r2.flatnest.com/avatars/uuid.jpg"
}
```
**Logic:** Upload to R2 → save URL → set `is_complete = true`

---

### POST /api/v1/auth/login
**Request:**
```json
{
    "email": "rahim@example.com",
    "password": "secret123"
}
```
**Response 200:** Same shape as register step 1

**Errors:**
- `401` → `{"message": "Invalid credentials", "code": "INVALID_CREDENTIALS"}`

---

### POST /api/v1/auth/logout
**Headers:** `Authorization: Bearer {token}`
**Response 200:** `{"message": "Logged out"}`
**Logic:** Delete refresh token from DB + revoke Sanctum token

---

### POST /api/v1/auth/refresh
**Request:**
```json
{ "refresh_token": "uuid_string" }
```
**Response 200:**
```json
{ "access_token": "new_token_string" }
```
**Logic:** Validate token exists + not expired → issue new access token

---

## Step 1.5 — Routes

```php
// routes/api.php
Route::prefix('v1')->group(function () {

    // Auth - public
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login',    [AuthController::class, 'login']);
    Route::post('/auth/refresh',  [AuthController::class, 'refresh']);

    // Auth - protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::post  ('/auth/logout',             [AuthController::class, 'logout']);
        Route::patch ('/auth/register/details',   [AuthController::class, 'registerDetails']);
        Route::patch ('/auth/register/avatar',    [AuthController::class, 'registerAvatar']);
    });

});
```

---

## Step 1.6 — Test in Postman

```
POST   http://localhost:8000/api/v1/auth/register         → get token
PATCH  http://localhost:8000/api/v1/auth/register/details → add role + dob
PATCH  http://localhost:8000/api/v1/auth/register/avatar  → upload photo
POST   http://localhost:8000/api/v1/auth/login            → get token
POST   http://localhost:8000/api/v1/auth/logout           → clear token
POST   http://localhost:8000/api/v1/auth/refresh          → new token
```

✅ **Phase 1 done when:** Flutter auth screen logs in and stores token in Secure Storage.

---

---

# PHASE 2 — Listings (Renter Feed)

> **Goal:** Renters can see listings with filters. Flutter home feed shows real data.

## Checklist

- [ ] Create `listings` migration
- [ ] Create `listing_photos` migration
- [ ] Create `Listing` and `ListingPhoto` models
- [ ] Create `ListingController`
- [ ] Seed DB with test data
- [ ] Add routes
- [ ] Test filters in Postman

---

## Step 2.1 — Listings Migration

```bash
php artisan make:migration create_listings_table --create=listings
```

```php
Schema::create('listings', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('owner_id')->constrained('users');
    $table->string('title');
    $table->string('area');
    $table->string('road_and_house')->nullable();
    $table->string('type'); // Family|Bachelor|Student|Couple|Sublet
    $table->integer('price');
    $table->integer('deposit')->nullable();
    $table->integer('beds');
    $table->integer('baths');
    $table->integer('size')->nullable();
    $table->text('description')->nullable();
    $table->float('coord_x')->nullable();
    $table->float('coord_y')->nullable();
    $table->text('amenities')->nullable(); // stored as JSON
    $table->string('status')->default('pending');
    $table->integer('views')->default(0);
    $table->timestamps();

    // Indexes
    $table->index('owner_id');
    $table->index('status');
    $table->index('type');
    $table->index('price');
    $table->index('area');
    $table->index(['status', 'price']);
});
```

---

## Step 2.2 — Listing Photos Migration

```bash
php artisan make:migration create_listing_photos_table --create=listing_photos
```

```php
Schema::create('listing_photos', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('listing_id')->constrained()->cascadeOnDelete();
    $table->text('url');
    $table->integer('position')->default(0);
    $table->timestamps();

    $table->index('listing_id');
    $table->index(['listing_id', 'position']);
});
```

---

## Step 2.3 — Listing Model

```php
protected $casts = ['amenities' => 'array'];

public function owner()  { return $this->belongsTo(User::class, 'owner_id'); }
public function photos() { return $this->hasMany(ListingPhoto::class)->orderBy('position'); }
public function chats()  { return $this->hasMany(Chat::class); }
```

---

## Step 2.4 — Endpoints

### GET /api/v1/listings
**Query Params:**
```
type=Family           (optional)
maxPrice=50000        (optional)
amenities=Wifi,Gas    (optional, comma-separated)
```
**Response 200:** Array of listing objects (see schema doc for full shape)

**Logic:**
```php
Listing::with(['owner', 'photos'])
    ->where('status', 'active')
    ->when($type,     fn($q) => $q->where('type', $type))
    ->when($maxPrice, fn($q) => $q->where('price', '<=', $maxPrice))
    ->get();
```

---

### GET /api/v1/listings/{id}
**Response 200:** Single listing object
**Side effect:** `$listing->increment('views')`
**Error 404:** `{"message": "Listing not found"}`

---

## Step 2.5 — Seeder (Test Data)

```bash
php artisan make:seeder ListingSeeder
php artisan db:seed --class=ListingSeeder
```

Seed at least 10 listings with `status = 'active'` so the Flutter feed has data to show.

---

✅ **Phase 2 done when:** Flutter home feed shows real listings from the database.

---

---

# PHASE 3 — Wishlist

> **Goal:** Renters can save and unsave listings. Heart icon works in Flutter.

## Checklist

- [ ] Create `wishlists` migration
- [ ] Create `WishlistController`
- [ ] Add routes
- [ ] Test in Postman

---

## Step 3.1 — Wishlists Migration

```bash
php artisan make:migration create_wishlists_table --create=wishlists
```

```php
Schema::create('wishlists', function (Blueprint $table) {
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->foreignUuid('listing_id')->constrained()->cascadeOnDelete();
    $table->timestamp('created_at')->useCurrent();

    $table->primary(['user_id', 'listing_id']);
    $table->index('user_id');
    $table->index('listing_id');
});
```

---

## Step 3.2 — Endpoints

### GET /api/v1/wishlist
**Response 200:**
```json
{ "saved_ids": ["uuid1", "uuid2"] }
```

### POST /api/v1/wishlist/{listing_id}
**Response 200:** `{"message": "Saved"}`
**Logic:** `firstOrCreate` — ignore if already exists

### DELETE /api/v1/wishlist/{listing_id}
**Response 200:** `{"message": "Removed"}`

---

✅ **Phase 3 done when:** Heart icon saves/unsaves and persists after app restart.

---

---

# PHASE 4 — Owner Dashboard

> **Goal:** Owners can post listings and see their dashboard.

## Checklist

- [ ] Create `OwnerController`
- [ ] Set up R2 storage in `config/filesystems.php`
- [ ] Add role middleware
- [ ] Test listing creation + photo upload

---

## Step 4.1 — R2 Storage Config

```php
// config/filesystems.php — add inside 'disks'
'r2' => [
    'driver'   => 's3',
    'key'      => env('R2_ACCESS_KEY_ID'),
    'secret'   => env('R2_SECRET_ACCESS_KEY'),
    'region'   => env('R2_REGION', 'auto'),
    'bucket'   => env('R2_BUCKET'),
    'url'      => env('R2_PUBLIC_URL'),
    'endpoint' => env('R2_ENDPOINT'),
    'use_path_style_endpoint' => true,
],
```

---

## Step 4.2 — Endpoints

### GET /api/v1/owner/listings
**Headers:** `Authorization: Bearer {token}` (role: owner)
**Response 200:**
```json
[
    {
        "listing": { },
        "status": "active",
        "views": 142,
        "inquiries": 7
    }
]
```

### POST /api/v1/listings
**Request:** `multipart/form-data` with fields + photos[]
**Response 201:**
```json
{
    "id": "uuid",
    "message": "Listing submitted. Goes live after 24h review."
}
```
**Logic:**
1. Upload each photo to R2: `Storage::disk('r2')->put('listings', $photo)`
2. Insert listing with `status = 'pending'`
3. Insert photo URLs into `listing_photos`

### PATCH /api/v1/listings/{id}
**Request:** Partial update (same fields as POST)
**Response 200:** Updated listing object

### DELETE /api/v1/listings/{id}
**Response 200:** `{"message": "Listing deleted"}`

---

## Step 4.3 — Owner Role Middleware

```bash
php artisan make:middleware EnsureUserIsOwner
```

```php
// In middleware
if (auth()->user()->role !== 'owner') {
    return response()->json(['message' => 'Owners only', 'code' => 'FORBIDDEN'], 403);
}
```

---

✅ **Phase 4 done when:** Owner can post a listing and see it in the dashboard with status 'pending'.

---

---

# PHASE 5 — Chat (REST)

> **Goal:** Renters and owners can message each other.

## Checklist

- [ ] Create `chats` migration
- [ ] Create `messages` migration
- [ ] Create `Chat` and `Message` models
- [ ] Create `ChatController`
- [ ] Add routes
- [ ] Test full message flow in Postman

---

## Step 5.1 — Chats Migration

```bash
php artisan make:migration create_chats_table --create=chats
```

```php
Schema::create('chats', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('renter_id')->constrained('users');
    $table->foreignUuid('owner_id')->constrained('users');
    $table->foreignUuid('listing_id')->constrained('listings')->nullOnDelete();
    $table->timestamps();

    $table->unique(['renter_id', 'owner_id', 'listing_id']);
    $table->index('renter_id');
    $table->index('owner_id');
    $table->index('listing_id');
});
```

---

## Step 5.2 — Messages Migration

```bash
php artisan make:migration create_messages_table --create=messages
```

```php
Schema::create('messages', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('chat_id')->constrained()->cascadeOnDelete();
    $table->foreignUuid('sender_id')->constrained('users');
    $table->text('text');
    $table->boolean('is_read')->default(false);
    $table->timestamps();

    $table->index('chat_id');
    $table->index(['chat_id', 'created_at']);
    $table->index(['chat_id', 'is_read']);
    $table->index('sender_id');
});
```

---

## Step 5.3 — Endpoints

### GET /api/v1/chats
**Response 200:** Array of chat summaries with last message + unread count

### GET /api/v1/chats/{id}/messages
**Response 200:** Chat with messages array
**Side effect:** Mark all messages as read for current user

### POST /api/v1/chats/{id}/messages
**Request:** `{"text": "Is it available?"}`
**Response 201:** The new message object

### POST /api/v1/chats (Start new chat)
**Request:**
```json
{
    "listing_id": "uuid",
    "initial_message": "Is it available?"
}
```
**Response 201:** Chat object with first message
**Logic:** `firstOrCreate` chat → create first message

---

✅ **Phase 5 done when:** Flutter chat screen sends and receives messages (REST only, no real-time yet).

---

---

# PHASE 5B — Real-time Chat (WebSocket)

> **Goal:** Messages appear instantly without refresh.

## Checklist

- [ ] Install Pusher SDK: `composer require pusher/pusher-php-server`
- [ ] Create `MessageSent` event
- [ ] Broadcast on new message
- [ ] Test with Pusher debug console

---

## Step 5B.1 — Message Event

```bash
php artisan make:event MessageSent
```

```php
class MessageSent implements ShouldBroadcast {
    public function broadcastOn() {
        return new PrivateChannel('chat.' . $this->message->chat_id);
    }
    public function broadcastAs() {
        return 'message.sent';
    }
}
```

---

## Step 5B.2 — Flutter Side

Flutter listens on `private-chat.{chat_id}` channel for `message.sent` events and appends messages to the list.

---

✅ **Phase 5B done when:** Sending a message shows instantly on the other person's screen.

---

---

# PHASE 6 — Notifications

> **Goal:** Users get notified of new messages, listing approvals, etc.

## Checklist

- [ ] Create `notifications` migration
- [ ] Create `NotificationController`
- [ ] Auto-create notification on new message
- [ ] Auto-create notification on listing status change
- [ ] Add routes
- [ ] Test in Postman

---

## Step 6.1 — Notifications Migration

```bash
php artisan make:migration create_notifications_table --create=notifications
```

```php
Schema::create('notifications', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->string('kind'); // message|price|match|system|listing
    $table->string('title');
    $table->text('body');
    $table->uuid('reference_id')->nullable();
    $table->boolean('is_unread')->default(true);
    $table->timestamps();

    $table->index('user_id');
    $table->index(['user_id', 'is_unread']);
    $table->index(['user_id', 'kind']);
    $table->index(['user_id', 'created_at']);
});
```

---

## Step 6.2 — Endpoints

### GET /api/v1/notifications
**Response 200:**
```json
[
    {
        "id": "uuid",
        "kind": "message",
        "title": "New message from Karim Bhai",
        "body": "Yes, the flat is available!",
        "time": "2 hours ago",
        "is_unread": true
    }
]
```

### PATCH /api/v1/notifications/{id}/read
**Response 200:** `{"message": "Marked as read"}`

### PATCH /api/v1/notifications/read-all
**Response 200:** `{"message": "All marked as read"}`

---

## Step 6.3 — Auto-trigger Notifications

```php
// Trigger when a message is sent (inside ChatController@sendMessage)
Notification::create([
    'user_id'      => $recipientId,
    'kind'         => 'message',
    'title'        => 'New message from ' . auth()->user()->name,
    'body'         => Str::limit($request->text, 80),
    'reference_id' => $chat->id,
]);

// Trigger when listing status changes (inside admin approval)
Notification::create([
    'user_id'      => $listing->owner_id,
    'kind'         => 'listing',
    'title'        => 'Your listing was approved!',
    'body'         => $listing->title . ' is now live.',
    'reference_id' => $listing->id,
]);
```

---

✅ **Phase 6 done when:** Bell icon shows unread count and notification list is populated.

---

---

# PHASE 7 — Filament Admin Panel

> **Goal:** Admin can approve/reject listings, manage users, view stats.

## Checklist

- [ ] Create Filament admin user
- [ ] Create `ListingResource` in Filament
- [ ] Create `UserResource` in Filament
- [ ] Add listing approval action
- [ ] Add dashboard stats widget

---

## Step 7.1 — Create Admin User

```bash
php artisan make:filament-user
```

Access admin panel at: `http://localhost:8000/admin`

---

## Step 7.2 — Listing Resource

```bash
php artisan make:filament-resource Listing --generate
```

Add approve/reject actions in `ListingResource.php`:

```php
Tables\Actions\Action::make('approve')
    ->action(fn($record) => $record->update(['status' => 'active']))
    ->requiresConfirmation()
    ->color('success'),

Tables\Actions\Action::make('reject')
    ->action(fn($record) => $record->update(['status' => 'rejected']))
    ->requiresConfirmation()
    ->color('danger'),
```

---

## Step 7.3 — Stats Widget

```bash
php artisan make:filament-widget StatsOverview --stats-overview
```

```php
protected function getStats(): array {
    return [
        Stat::make('Total Users',    User::count()),
        Stat::make('Active Listings', Listing::where('status', 'active')->count()),
        Stat::make('Pending Review',  Listing::where('status', 'pending')->count()),
        Stat::make('Total Chats',     Chat::count()),
    ];
}
```

---

✅ **Phase 7 done when:** Admin can log into panel, approve listings, and see stats.

---

---

# Error Response Format

All errors must follow this shape (consistent across all endpoints):

```json
{
    "message": "Human readable message",
    "code": "MACHINE_READABLE_CODE"
}
```

| Status | When |
|--------|------|
| 200 | Success |
| 201 | Created |
| 400 | Validation error |
| 401 | Missing / invalid / expired token |
| 403 | Valid token but wrong role |
| 404 | Resource not found |
| 409 | Conflict (duplicate) |
| 500 | Server error |

---

# Quick Commands Reference

```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh start (wipes DB)
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_xxx_table --create=xxx

# Create controller
php artisan make:controller Api/V1/XxxController

# Create model + migration together
php artisan make:model Xxx -m

# Start local server
php artisan serve

# Run seeder
php artisan db:seed --class=XxxSeeder

# Clear cache
php artisan config:clear && php artisan cache:clear
```

---

# Flutter Files to Update Per Phase

| Phase | Flutter file |
|-------|-------------|
| 1 — Auth | `lib/features/auth/data/datasources/auth_remote_datasource.dart` |
| 2 — Listings | `lib/features/home/data/repositories/home_repository_impl.dart` |
| 3 — Wishlist | `lib/features/wishlist/presentation/bloc/wishlist_bloc.dart` |
| 4 — Owner | `lib/features/owner/presentation/bloc/owner_bloc.dart` |
| 5 — Chat | `lib/features/chat/data/repositories/chat_repository_impl.dart` |

Each file has `// API hook:` comments — replace mock calls with real HTTP calls at those points.

---

*FlatNest Backend — v1.0*
