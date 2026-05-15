# FlatNest — Database Schema

> Keep this file in your Laravel project root alongside `BUILD.md`.
> Reference this whenever you create migrations, models, or write queries.

---

## Overview — All Tables

| # | Table | Description |
|---|-------|-------------|
| 1 | `users` | All registered users (renters + owners) |
| 2 | `listings` | Property listings posted by owners |
| 3 | `listing_photos` | Photos for each listing (stored in R2) |
| 4 | `wishlists` | Saved/favorited listings per renter |
| 5 | `chats` | Conversation threads between renter + owner |
| 6 | `messages` | Individual messages within a chat |
| 7 | `notifications` | In-app notifications per user |
| 8 | `refresh_tokens` | Long-lived tokens for re-auth without login |

---

## Entity Relationship Diagram

```
users
 ├── listings          (owner_id → users.id)          ONE-TO-MANY
 ├── wishlists         (user_id  → users.id)          MANY-TO-MANY via wishlists
 ├── chats             (renter_id → users.id)         ONE-TO-MANY
 ├── chats             (owner_id  → users.id)         ONE-TO-MANY
 ├── messages          (sender_id → users.id)         ONE-TO-MANY
 ├── notifications     (user_id   → users.id)         ONE-TO-MANY
 └── refresh_tokens    (user_id   → users.id)         ONE-TO-MANY

listings
 ├── listing_photos    (listing_id → listings.id)     ONE-TO-MANY
 ├── wishlists         (listing_id → listings.id)     MANY-TO-MANY via wishlists
 └── chats             (listing_id → listings.id)     ONE-TO-MANY

chats
 └── messages          (chat_id → chats.id)           ONE-TO-MANY
```

---

---

## Table 1 — `users`

Central table. Every other table references this.

### Columns

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | UUID | PK, DEFAULT gen_random_uuid() | Unique user identifier |
| `email` | TEXT | UNIQUE, NOT NULL | Login email |
| `password_hash` | TEXT | NOT NULL | bcrypt hash (cost 12) |
| `name` | TEXT | NOT NULL | Full display name |
| `phone` | TEXT | UNIQUE, NOT NULL | BD mobile (01XXXXXXXXX) |
| `role` | TEXT | DEFAULT 'renter' | 'renter' or 'owner' |
| `date_of_birth` | DATE | NULLABLE | Must be 18+ at registration |
| `avatar_url` | TEXT | NULLABLE | Cloudflare R2 photo URL |
| `is_complete` | BOOLEAN | DEFAULT false | All 3 registration steps done |
| `created_at` | TIMESTAMPTZ | DEFAULT now() | Account creation time |
| `updated_at` | TIMESTAMPTZ | DEFAULT now() | Last update time |

### Indexes

| Index Name | Column(s) | Type | Purpose |
|------------|-----------|------|---------|
| `users_pkey` | `id` | PRIMARY KEY | Auto-created |
| `users_email_idx` | `email` | UNIQUE B-Tree | Fast login, duplicate check |
| `users_phone_idx` | `phone` | UNIQUE B-Tree | Fast phone lookup |
| `users_role_idx` | `role` | B-Tree | Filter renters vs owners |

### Migration

```php
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->string('email')->unique();
    $table->string('password_hash');
    $table->string('name');
    $table->string('phone')->unique();
    $table->string('role')->default('renter');
    $table->date('date_of_birth')->nullable();
    $table->string('avatar_url')->nullable();
    $table->boolean('is_complete')->default(false);
    $table->timestamps();

    $table->index('role');
});
```

### Eloquent Model

```php
// app/Models/User.php
protected $primaryKey = 'id';
public $incrementing  = false;
protected $keyType    = 'string';

protected $fillable = [
    'email', 'password_hash', 'name', 'phone',
    'role', 'date_of_birth', 'avatar_url', 'is_complete'
];

protected $hidden = ['password_hash'];

// Relationships
public function listings()       { return $this->hasMany(Listing::class, 'owner_id'); }
public function wishlist()       { return $this->belongsToMany(Listing::class, 'wishlists'); }
public function chatsAsRenter()  { return $this->hasMany(Chat::class, 'renter_id'); }
public function chatsAsOwner()   { return $this->hasMany(Chat::class, 'owner_id'); }
public function notifications()  { return $this->hasMany(Notification::class); }
public function refreshTokens()  { return $this->hasMany(RefreshToken::class); }
```

---

---

## Table 2 — `listings`

Core table. Owners post listings. Renters browse them.

### Columns

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | UUID | PK | Unique listing ID |
| `owner_id` | UUID | FK → users.id | Owner who posted this |
| `title` | TEXT | NOT NULL | e.g. "3BHK Family Flat – Dhanmondi" |
| `area` | TEXT | NOT NULL | Neighborhood name |
| `road_and_house` | TEXT | NULLABLE | Exact address (shown after contact) |
| `type` | TEXT | NOT NULL | Family, Bachelor, Student, Couple, Sublet |
| `price` | INTEGER | NOT NULL | Monthly rent in BDT |
| `deposit` | INTEGER | NULLABLE | Security deposit in BDT |
| `beds` | INTEGER | NOT NULL | Number of bedrooms |
| `baths` | INTEGER | NOT NULL | Number of bathrooms |
| `size` | INTEGER | NULLABLE | Floor area in sq ft |
| `description` | TEXT | NULLABLE | Full listing description |
| `coord_x` | FLOAT | NULLABLE | Latitude (for map pin) |
| `coord_y` | FLOAT | NULLABLE | Longitude (for map pin) |
| `amenities` | TEXT[] | DEFAULT '{}' | [Wifi, Parking, Gas, Lift, Generator, Gym] |
| `status` | TEXT | DEFAULT 'pending' | pending, active, rented, rejected |
| `views` | INTEGER | DEFAULT 0 | Incremented each time detail page opens |
| `created_at` | TIMESTAMPTZ | DEFAULT now() | Submission time |
| `updated_at` | TIMESTAMPTZ | DEFAULT now() | Last update |

### Status Lifecycle

```
pending  →  Newly submitted, awaiting 24h admin review
active   →  Approved, visible to renters in the feed
rented   →  Owner marked as rented, hidden from feed
rejected →  Admin rejected, owner notified with reason
```

### Indexes

| Index Name | Column(s) | Type | Purpose |
|------------|-----------|------|---------|
| `listings_pkey` | `id` | PRIMARY KEY | Auto-created |
| `listings_owner_idx` | `owner_id` | B-Tree | Owner dashboard queries |
| `listings_status_idx` | `status` | B-Tree | Filter active for renter feed |
| `listings_type_idx` | `type` | B-Tree | Filter by Family/Bachelor etc. |
| `listings_price_idx` | `price` | B-Tree | Sort and filter by price |
| `listings_area_idx` | `area` | B-Tree | Filter by neighborhood |
| `listings_coords_idx` | `(coord_x, coord_y)` | B-Tree | Geo distance queries |
| `listings_amenities_idx` | `amenities` | GIN | Fast array contains search |
| `listings_status_price_idx` | `(status, price)` | Composite | Feed: active + price filter |

### Migration

```php
Schema::create('listings', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('owner_id')->constrained('users');
    $table->string('title');
    $table->string('area');
    $table->string('road_and_house')->nullable();
    $table->string('type');
    $table->integer('price');
    $table->integer('deposit')->nullable();
    $table->integer('beds');
    $table->integer('baths');
    $table->integer('size')->nullable();
    $table->text('description')->nullable();
    $table->float('coord_x')->nullable();
    $table->float('coord_y')->nullable();
    $table->json('amenities')->nullable();
    $table->string('status')->default('pending');
    $table->integer('views')->default(0);
    $table->timestamps();

    $table->index('owner_id');
    $table->index('status');
    $table->index('type');
    $table->index('price');
    $table->index('area');
    $table->index(['status', 'price']);
    $table->index(['coord_x', 'coord_y']);
});
```

### Eloquent Model

```php
// app/Models/Listing.php
protected $casts = ['amenities' => 'array'];

public function owner()   { return $this->belongsTo(User::class, 'owner_id'); }
public function photos()  { return $this->hasMany(ListingPhoto::class)->orderBy('position'); }
public function chats()   { return $this->hasMany(Chat::class); }
public function savedBy() { return $this->belongsToMany(User::class, 'wishlists'); }
```

---

---

## Table 3 — `listing_photos`

One listing has many photos. Position 0 is the cover photo.

### Columns

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | UUID | PK | Unique photo record ID |
| `listing_id` | UUID | FK → listings.id, CASCADE | Parent listing |
| `url` | TEXT | NOT NULL | Full Cloudflare R2 public URL |
| `position` | INTEGER | DEFAULT 0 | Sort order (0 = cover photo) |
| `created_at` | TIMESTAMPTZ | DEFAULT now() | Upload time |

### Indexes

| Index Name | Column(s) | Type | Purpose |
|------------|-----------|------|---------|
| `listing_photos_pkey` | `id` | PRIMARY KEY | Auto-created |
| `listing_photos_listing_idx` | `listing_id` | B-Tree | All photos for a listing |
| `listing_photos_pos_idx` | `(listing_id, position)` | Composite | Ordered photos in one query |

### Migration

```php
Schema::create('listing_photos', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('listing_id')->constrained()->cascadeOnDelete();
    $table->text('url');
    $table->integer('position')->default(0);
    $table->timestamp('created_at')->useCurrent();

    $table->index('listing_id');
    $table->index(['listing_id', 'position']);
});
```

### Eloquent Model

```php
// app/Models/ListingPhoto.php
public function listing() { return $this->belongsTo(Listing::class); }
```

---

---

## Table 4 — `wishlists`

Junction table. Implements many-to-many between users and listings.

### Columns

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `user_id` | UUID | PK, FK → users.id, CASCADE | The renter |
| `listing_id` | UUID | PK, FK → listings.id, CASCADE | The saved listing |
| `created_at` | TIMESTAMPTZ | DEFAULT now() | When it was saved |

### Indexes

| Index Name | Column(s) | Type | Purpose |
|------------|-----------|------|---------|
| `wishlists_pkey` | `(user_id, listing_id)` | COMPOSITE PK | Prevents duplicates, fast lookup |
| `wishlists_user_idx` | `user_id` | B-Tree | All saved listings for a user |
| `wishlists_listing_idx` | `listing_id` | B-Tree | How many users saved a listing |

### Migration

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

---

## Table 5 — `chats`

One conversation thread per (renter + owner + listing) combination.

### Columns

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | UUID | PK | Unique chat room ID |
| `renter_id` | UUID | FK → users.id, RESTRICT | The renter in this conversation |
| `owner_id` | UUID | FK → users.id, RESTRICT | The listing owner |
| `listing_id` | UUID | FK → listings.id, SET NULL | The listing being discussed |
| `created_at` | TIMESTAMPTZ | DEFAULT now() | When conversation started |
| `updated_at` | TIMESTAMPTZ | DEFAULT now() | Last activity |

### Indexes

| Index Name | Column(s) | Type | Purpose |
|------------|-----------|------|---------|
| `chats_pkey` | `id` | PRIMARY KEY | Auto-created |
| `chats_renter_idx` | `renter_id` | B-Tree | All chats for a renter |
| `chats_owner_idx` | `owner_id` | B-Tree | All chats for an owner |
| `chats_listing_idx` | `listing_id` | B-Tree | Count inquiries per listing |
| `chats_unique_convo` | `(renter_id, owner_id, listing_id)` | UNIQUE | No duplicate chat rooms |

### Migration

```php
Schema::create('chats', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('renter_id')->constrained('users');
    $table->foreignUuid('owner_id')->constrained('users');
    $table->foreignUuid('listing_id')->nullable()->constrained('listings')->nullOnDelete();
    $table->timestamps();

    $table->unique(['renter_id', 'owner_id', 'listing_id']);
    $table->index('renter_id');
    $table->index('owner_id');
    $table->index('listing_id');
});
```

### Eloquent Model

```php
// app/Models/Chat.php
public function renter()      { return $this->belongsTo(User::class, 'renter_id'); }
public function owner()       { return $this->belongsTo(User::class, 'owner_id'); }
public function listing()     { return $this->belongsTo(Listing::class); }
public function messages()    { return $this->hasMany(Message::class)->orderBy('created_at'); }
public function lastMessage() { return $this->hasOne(Message::class)->latestOfMany(); }
public function unreadFor($userId) {
    return $this->messages()->where('is_read', false)->where('sender_id', '!=', $userId);
}
```

---

---

## Table 6 — `messages`

Individual messages inside a chat. Ordered by `created_at`.

### Columns

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | UUID | PK | Unique message ID |
| `chat_id` | UUID | FK → chats.id, CASCADE | Parent conversation |
| `sender_id` | UUID | FK → users.id, RESTRICT | Who sent this |
| `text` | TEXT | NOT NULL | Message content |
| `is_read` | BOOLEAN | DEFAULT false | Has recipient read this? |
| `created_at` | TIMESTAMPTZ | DEFAULT now() | Send timestamp |

### Indexes

| Index Name | Column(s) | Type | Purpose |
|------------|-----------|------|---------|
| `messages_pkey` | `id` | PRIMARY KEY | Auto-created |
| `messages_chat_idx` | `chat_id` | B-Tree | Load all messages in a chat |
| `messages_chat_time_idx` | `(chat_id, created_at)` | Composite | Paginate chronologically |
| `messages_unread_idx` | `(chat_id, is_read)` | Composite | Fast unread count per chat |
| `messages_sender_idx` | `sender_id` | B-Tree | Messages by specific user |

### Migration

```php
Schema::create('messages', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('chat_id')->constrained()->cascadeOnDelete();
    $table->foreignUuid('sender_id')->constrained('users');
    $table->text('text');
    $table->boolean('is_read')->default(false);
    $table->timestamp('created_at')->useCurrent();

    $table->index('chat_id');
    $table->index(['chat_id', 'created_at']);
    $table->index(['chat_id', 'is_read']);
    $table->index('sender_id');
});
```

### Eloquent Model

```php
// app/Models/Message.php
public function chat()   { return $this->belongsTo(Chat::class); }
public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
```

---

---

## Table 7 — `notifications`

In-app notifications. Auto-generated by backend events.

### Columns

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | UUID | PK | Unique notification ID |
| `user_id` | UUID | FK → users.id, CASCADE | Who receives this |
| `kind` | TEXT | NOT NULL | message, listing, price, match, system |
| `title` | TEXT | NOT NULL | Short notification title |
| `body` | TEXT | NOT NULL | Full notification text |
| `reference_id` | UUID | NULLABLE | Related entity ID (chat, listing) |
| `is_unread` | BOOLEAN | DEFAULT true | Unread badge indicator |
| `created_at` | TIMESTAMPTZ | DEFAULT now() | When it was created |

### Notification Kinds

| Kind | Triggered When |
|------|---------------|
| `message` | New chat message received |
| `listing` | Listing approved or rejected by admin |
| `price` | Price dropped on a wishlisted listing |
| `match` | New listing matches renter's saved filters |
| `system` | General platform announcements |

### Indexes

| Index Name | Column(s) | Type | Purpose |
|------------|-----------|------|---------|
| `notifications_pkey` | `id` | PRIMARY KEY | Auto-created |
| `notifications_user_idx` | `user_id` | B-Tree | All notifications for a user |
| `notifications_unread_idx` | `(user_id, is_unread)` | Composite | Fast unread badge count |
| `notifications_kind_idx` | `(user_id, kind)` | Composite | Filter by type |
| `notifications_time_idx` | `(user_id, created_at DESC)` | Composite | Latest first |

### Migration

```php
Schema::create('notifications', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->string('kind');
    $table->string('title');
    $table->text('body');
    $table->uuid('reference_id')->nullable();
    $table->boolean('is_unread')->default(true);
    $table->timestamp('created_at')->useCurrent();

    $table->index('user_id');
    $table->index(['user_id', 'is_unread']);
    $table->index(['user_id', 'kind']);
    $table->index(['user_id', 'created_at']);
});
```

### Eloquent Model

```php
// app/Models/Notification.php
public function user() { return $this->belongsTo(User::class); }
```

---

---

## Table 8 — `refresh_tokens`

Long-lived tokens. Deleted on logout. Cleaned up by cron after expiry.

### Columns

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | UUID | PK | Unique token record |
| `user_id` | UUID | FK → users.id, CASCADE | Token owner |
| `token` | TEXT | UNIQUE, NOT NULL | The token string (UUID v4) |
| `expires_at` | TIMESTAMPTZ | NOT NULL | 30 days from creation |
| `created_at` | TIMESTAMPTZ | DEFAULT now() | Issue time |

### Indexes

| Index Name | Column(s) | Type | Purpose |
|------------|-----------|------|---------|
| `refresh_tokens_pkey` | `id` | PRIMARY KEY | Auto-created |
| `refresh_tokens_token_idx` | `token` | UNIQUE B-Tree | Fast token lookup on refresh |
| `refresh_tokens_user_idx` | `user_id` | B-Tree | Delete all tokens on logout |
| `refresh_tokens_expiry_idx` | `expires_at` | B-Tree | Cleanup expired tokens (cron) |

### Migration

```php
Schema::create('refresh_tokens', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->text('token')->unique();
    $table->timestampTz('expires_at');
    $table->timestamp('created_at')->useCurrent();

    $table->index('user_id');
    $table->index('expires_at');
});
```

### Eloquent Model

```php
// app/Models/RefreshToken.php
public function user() { return $this->belongsTo(User::class); }
```

---

---

## All Foreign Key Relationships

| From Table | Column | → To Table | On Delete |
|------------|--------|------------|-----------|
| `listings` | `owner_id` | `users.id` | RESTRICT |
| `listing_photos` | `listing_id` | `listings.id` | CASCADE |
| `wishlists` | `user_id` | `users.id` | CASCADE |
| `wishlists` | `listing_id` | `listings.id` | CASCADE |
| `chats` | `renter_id` | `users.id` | RESTRICT |
| `chats` | `owner_id` | `users.id` | RESTRICT |
| `chats` | `listing_id` | `listings.id` | SET NULL |
| `messages` | `chat_id` | `chats.id` | CASCADE |
| `messages` | `sender_id` | `users.id` | RESTRICT |
| `notifications` | `user_id` | `users.id` | CASCADE |
| `refresh_tokens` | `user_id` | `users.id` | CASCADE |

### Cascade Rules Explained

- **CASCADE** — child rows are deleted automatically when parent is deleted
- **RESTRICT** — deletion is blocked if child rows exist (must delete children first)
- **SET NULL** — child column is set to NULL when parent is deleted (safe orphan)

---

## Run All Migrations

```bash
php artisan migrate
```

### Migration Order (must follow this order due to FK dependencies)

```
1. users
2. listings          (depends on users)
3. listing_photos    (depends on listings)
4. wishlists         (depends on users + listings)
5. chats             (depends on users + listings)
6. messages          (depends on chats + users)
7. notifications     (depends on users)
8. refresh_tokens    (depends on users)
```

---

*FlatNest Schema — v1.0 — Laravel + PostgreSQL*
