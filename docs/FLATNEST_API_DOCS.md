# FlatNest — Business API Reference

> **Base URL:** `https://flatnest.techrealify.com/api/v1`  
> **Storage URL (for relative image paths):** `https://flatnest.techrealify.com`  
> **Format:** All requests/responses are `application/json` unless noted as `multipart/form-data`.

---

## Table of Contents

1. [Authentication & Headers](#1-authentication--headers)
2. [Auth APIs](#2-auth-apis)
3. [Registration Flow (Multi-Step)](#3-registration-flow-multi-step)
4. [Reference / Meta APIs](#4-reference--meta-apis)
5. [Renter — Discovery & Listings](#5-renter--discovery--listings)
6. [Renter — Filter & Search](#6-renter--filter--search)
7. [Renter — Nearby Map Listings](#7-renter--nearby-map-listings)
8. [Renter — Wishlist](#8-renter--wishlist)
9. [Owner — My Listings](#9-owner--my-listings)
10. [Owner — Create Listing (Multi-Step Wizard)](#10-owner--create-listing-multi-step-wizard)
11. [Owner — Edit Listing](#11-owner--edit-listing)
12. [Owner — Listing Actions](#12-owner--listing-actions)
13. [Chat / Messaging](#13-chat--messaging)
14. [Notifications](#14-notifications)
15. [Device — FCM Token](#15-device--fcm-token)
16. [User Location](#16-user-location)
17. [Data Models](#17-data-models)
18. [Business Flow Diagrams](#18-business-flow-diagrams)

---

## 1. Authentication & Headers

Every authenticated request must include:

```
Authorization: Bearer <access_token>
Accept: application/json
```

### Token Refresh

When a request returns `401 Unauthorized`, attempt a silent token refresh:

```
POST /auth/refresh
Body: { "refresh_token": "<refresh_token>" }
Response 200: { "data": { "access_token": "<new_token>" } }
```

If refresh fails (returns non-200), log the user out and redirect to login.

### Standard API Response Envelope

All endpoints return:

```json
{
  "success": true,
  "message": "...",
  "data": { ... } | [ ... ] | null
}
```

Paginated responses add a `meta` object:

```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72,
    "unread_count": 3   // notifications only
  }
}
```

---

## 2. Auth APIs

### 2.1 Login with Email/Password

```
POST /auth/login
```

**Body:**
```json
{
  "email": "user@example.com",
  "password": "secret"
}
```

**Response `data`:**
```json
{
  "access_token": "...",
  "refresh_token": "...",
  "registration_step": null,
  "user": {
    "id": "uuid",
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "01711000000",
    "role": "renter",          // "renter" | "owner" | null
    "avatar_url": "/storage/avatars/abc.jpg",
    "is_complete": true
  }
}
```

> If `is_complete` is `false`, the user hasn't finished registration — redirect to the appropriate registration step.  
> `registration_step` indicates which step was last completed (1, 2, 3, or null = complete).

---

### 2.2 Google Sign-In

```
POST /auth/google
```

**Body:**
```json
{
  "id_token": "<google_id_token>"
}
```

**Response:** Same as Login response.

> Business rule: If the Google account is new, the server creates the user. `is_complete` will be `false` and the app routes to role selection (step 2 of registration).

---

### 2.3 Logout

```
POST /auth/logout
Authorization: Bearer <token>
```

**Body:** _(empty)_  
**Response:** `{ "success": true }`

---

### 2.4 Refresh Token

```
POST /auth/refresh
```

**Body:**
```json
{ "refresh_token": "<refresh_token>" }
```

**Response `data`:**
```json
{ "access_token": "<new_access_token>" }
```

---

### 2.5 Delete Account

```
DELETE /auth/account
Authorization: Bearer <token>
```

**Response:** `{ "success": true }`

---

## 3. Registration Flow (Multi-Step)

The registration is a **3-step wizard**:

| Step | Endpoint | What happens |
|------|----------|--------------|
| 1 | `POST /auth/register` | Create account with name, email, phone, password |
| 2 | `PATCH /auth/register/details` | Set user role (`owner` or `renter`) |
| 3 | `PATCH /auth/register/avatar` | Upload profile photo (optional but shown) |

### Step 1 — Create Account

```
POST /auth/register
```

**Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123",
  "phone": "01711000000"
}
```

**Response `data`:** Same as Login (returns tokens + user).

---

### Step 2 — Save Role

```
PATCH /auth/register/details
Authorization: Bearer <token>
```

**Body:**
```json
{ "role": "renter" }   // "renter" | "owner"
```

**Response:** `{ "success": true }`

> This determines which home screen the user sees: **Owner Home** or **Renter Home**.

---

### Step 3 — Upload Avatar

```
PATCH /auth/register/avatar
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

**Body (multipart):**
```
avatar: <image_file>
```

**Response `data`:**
```json
{ "avatar_url": "/storage/avatars/abc.jpg" }
```

> Resolve relative URLs: `avatar_url` starting with `/` should be prepended with `https://flatnest.techrealify.com`.

---

## 4. Reference / Meta APIs

These endpoints provide dropdown data for forms and filters. They are **public** (no auth required for most).

### 4.1 Listing Types

```
GET /listing-types
GET /meta/listing-types
```

**Response `data`:**
```json
[
  { "id": 1, "name": "apartment", "label": "Apartment", "slug": "apartment" },
  { "id": 2, "name": "bachelor", "label": "Bachelor", "slug": "bachelor" },
  { "id": 3, "name": "sublet",   "label": "Sublet",   "slug": "sublet" }
]
```

---

### 4.2 Listing Facings

```
GET /meta/listing-facings
```

**Response `data`:**
```json
[
  { "id": 1, "label": "North",  "slug": "north" },
  { "id": 2, "label": "South",  "slug": "south" },
  { "id": 3, "label": "East",   "slug": "east" },
  { "id": 4, "label": "West",   "slug": "west" }
]
```

---

### 4.3 Amenities

```
GET /amenities
```

**Response `data`:**
```json
[
  { "id": 1,  "name": "wifi",       "label": "WiFi" },
  { "id": 2,  "name": "parking",    "label": "Parking" },
  { "id": 3,  "name": "generator",  "label": "Generator" },
  { "id": 4,  "name": "lift",       "label": "Lift/Elevator" },
  { "id": 5,  "name": "gym",        "label": "Gym" },
  { "id": 6,  "name": "security",   "label": "Security Guard" },
  { "id": 7,  "name": "cctv",       "label": "CCTV" },
  ...
]
```

---

### 4.4 Geo — Divisions → Districts → Upazilas → Unions

A cascaded 4-level geo hierarchy.

```
GET /geo/divisions
GET /geo/districts/{division_id}
GET /geo/upazilas/{district_id}
GET /geo/unions/{upazila_id}
```

**Response `data` (each level):**
```json
[
  { "id": 1, "name": "Dhaka",      "bn_name": "ঢাকা" },
  { "id": 2, "name": "Chittagong", "bn_name": "চট্টগ্রাম" }
]
```

**Usage pattern (cascade):**
1. Load all divisions on page mount.
2. When user picks a division → call `/geo/districts/{division_id}`.
3. When user picks a district → call `/geo/upazilas/{district_id}`.
4. When user picks an upazila → call `/geo/unions/{upazila_id}`.
5. Clear child selections when a parent changes.

---

### 4.5 Meta Roles

```
GET /meta/roles
```

Returns available user roles (used during onboarding).

---

## 5. Renter — Discovery & Listings

### 5.1 Browse Listings (Discovery)

```
GET /listings
Authorization: Bearer <token>
```

Returns all **approved** listings. Supports rich filtering (see §6).

**Response `data`:**
```json
[
  {
    "id": "uuid",
    "title": "3 Bed Apartment in Mirpur",
    "area": "Mirpur-10",
    "road_and_house": "House 5, Road 3",
    "price": 25000,
    "deposit": 50000,
    "beds": 3,
    "baths": 2,
    "size": 1200,
    "floor_no": 4,
    "facing_id": 1,
    "facing": { "id": 1, "label": "North", "slug": "north" },
    "available_from": "2026-07-01",
    "description": "Spacious apartment...",
    "status": "approved",
    "status_label": "Approved",
    "views": 142,
    "coord_y": 23.8103,
    "coord_x": 90.4125,
    "distance_km": null,
    "listing_type_id": 1,
    "type": "apartment",
    "division_id": 1,
    "district_id": 21,
    "upazila_id": 341,
    "union_id": null,
    "amenities": [
      { "id": 1, "name": "wifi",    "label": "WiFi" },
      { "id": 2, "name": "parking", "label": "Parking" }
    ],
    "photos": [
      { "id": "photo-uuid", "url": "https://flatnest.techrealify.com/storage/...", "position": 1 }
    ],
    "owner": {
      "id": "owner-uuid",
      "name": "Rahim Ahmed",
      "phone": "01711000001",
      "avatar_url": "/storage/avatars/xyz.jpg"
    },
    "created_at": "2026-05-01T10:00:00Z"
  }
]
```

> **Photo URLs:** If `url` starts with `/`, prepend `https://flatnest.techrealify.com`.  
> **Thumbnail:** Use the photo with the lowest `position` value.  
> **Available Now:** If `available_from` is `null`, the listing is available immediately.

---

### 5.2 Get Listing Detail

```
GET /listings/{id}
Authorization: Bearer <token>
```

Returns the same shape as a listing in the browse list, plus full details (all amenities, all photos, owner info).

---

## 6. Renter — Filter & Search

All filter params are appended as **query parameters** to `GET /listings`.

| Query Param | Type | Description |
|---|---|---|
| `search` | string | Full-text search on title/area |
| `listing_type_id` | int | Filter by listing type ID |
| `price_min` | int | Minimum monthly rent (BDT) |
| `price_max` | int | Maximum monthly rent (BDT) |
| `beds` | int | Number of bedrooms |
| `baths` | int | Number of bathrooms |
| `facing_id` | int | Facing direction ID |
| `floor_min` | int | Minimum floor number |
| `floor_max` | int | Maximum floor number |
| `size_min` | int | Minimum area (sq ft) |
| `size_max` | int | Maximum area (sq ft) |
| `available_from_start` | date (YYYY-MM-DD) | Earliest available date |
| `available_from_end` | date (YYYY-MM-DD) | Latest available date |
| `amenities` | string | Comma-separated amenity IDs e.g. `1,3,5` |
| `division_id` | int | Filter by division |
| `district_id` | int | Filter by district |
| `upazila_id` | int | Filter by upazila |
| `union_id` | int | Filter by union |
| `sort_by` | string | `price_asc` \| `price_desc` \| `newest` \| `oldest` |

**Example — 2-bed apartment in Dhaka under ৳30,000 with WiFi:**
```
GET /listings?listing_type_id=1&beds=2&price_max=30000&division_id=1&amenities=1
```

---

## 7. Renter — Nearby Map Listings

Fetches listings within a radius of a GPS coordinate.

```
GET /listings/nearby
Authorization: Bearer <token>
```

| Query Param | Type | Required | Description |
|---|---|---|---|
| `coord_x` | float | YES | User's **longitude** |
| `coord_y` | float | YES | User's **latitude** |
| `radius` | float | no | Search radius in km (default: 5.0) |
| `listing_type_id` | int | no | Filter by type |
| `price_min` | int | no | Min rent |
| `price_max` | int | no | Max rent |
| `beds` | int | no | Bedrooms |
| `baths` | int | no | Bathrooms |

> **Coordinate convention:** `coord_x` = longitude (X-axis), `coord_y` = latitude (Y-axis). This is the API's convention — ensure you pass them correctly.

**Response `data`:** Same listing shape as §5.1, but each listing includes:
```json
{ "distance_km": 1.24 }
```

---

## 8. Renter — Wishlist

### 8.1 Get Saved Listings

```
GET /wishlist
Authorization: Bearer <token>
```

**Response `data`:** Array of listing objects (same shape as §5.1).

---

### 8.2 Toggle Save / Unsave

```
POST /wishlist/{listing_id}/toggle
Authorization: Bearer <token>
```

**Response `data`:**
```json
{ "saved": true }    // true = now saved, false = now removed
```

> Use optimistic UI updates: flip the UI immediately, then confirm/revert based on the server response.

---

## 9. Owner — My Listings

### 9.1 Get Owner's Listings (Paginated)

```
GET /owner/listings
Authorization: Bearer <token>
```

| Query Param | Type | Description |
|---|---|---|
| `page` | int | Page number (default: 1) |
| `status` | string | `draft` \| `submitted` \| `approved` \| `rejected` \| `rented` |
| `type_id` | int | Filter by listing type |

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "My Apartment",
      "status": "approved",
      "status_label": "Approved",
      "inquiries": 12,
      "rejection_reason": null,
      "views": 300,
      "price": 25000,
      "beds": 3,
      "baths": 2,
      "photos": [ ... ],
      ...
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

**Listing Statuses:**

| Status | Meaning |
|---|---|
| `draft` | Created but not yet submitted for review |
| `submitted` | Submitted, pending admin review |
| `approved` | Live and visible to renters |
| `rejected` | Rejected by admin (see `rejection_reason`) |
| `rented` | Marked as rented by the owner |

> **`OwnerListingModel`** extends the base listing with:  
> - `inquiries` (int) — number of chat inquiries  
> - `rejection_reason` (string | null) — admin's reason if rejected

---

## 10. Owner — Create Listing (Multi-Step Wizard)

Creating a listing is a **5-step wizard**. Each step calls a different endpoint. The listing `id` returned in Step 1 is used for all subsequent steps.

```
Step 1: POST /listings             → creates draft, returns listing ID
Step 2: POST /listings/{id}/photos → uploads photos
Step 3: PATCH /listings/{id}/location → saves location info + GPS
Step 4: PATCH /listings/{id}       → updates any missing details (optional)
Step 5a: PATCH /listings/{id}/owner-info → saves owner contact details
Step 5b: POST /listings/{id}/submit → submits for admin review
```

---

### Step 1 — Create Draft Listing

```
POST /listings
Authorization: Bearer <token>
Content-Type: application/json
```

**Body:**
```json
{
  "title": "Spacious 3 Bed Apartment",
  "listing_type_id": 1,
  "price": 25000,
  "beds": 3,
  "baths": 2,
  "deposit": 50000,
  "size": 1200,
  "description": "Bright, well-ventilated...",
  "amenities": [1, 2, 4],
  "available_from": "2026-07-01",
  "floor_no": 4,
  "facing_id": 1
}
```

| Field | Type | Required | Description |
|---|---|---|---|
| `title` | string | YES | Listing title |
| `listing_type_id` | int | YES | From `/listing-types` |
| `price` | int | YES | Monthly rent in BDT |
| `beds` | int | YES | Number of bedrooms |
| `baths` | int | YES | Number of bathrooms |
| `deposit` | int | no | Security deposit amount |
| `size` | int | no | Area in sq ft |
| `description` | string | no | Full description |
| `amenities` | int[] | no | Array of amenity IDs |
| `available_from` | date | no | `YYYY-MM-DD` or omit = available now |
| `floor_no` | int | no | Floor number |
| `facing_id` | int | no | Facing direction ID |

**Response `data`:**
```json
{ "id": "new-listing-uuid", "status": "draft", ... }
```

---

### Step 2 — Upload Photos

```
POST /listings/{id}/photos
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

**Body (multipart):**
```
photos[]: <file1>
photos[]: <file2>
photos[]: <file3>
```

Upload multiple files using the same field name `photos[]`.

**Response:** `{ "success": true }`

---

### Step 3 — Save Location

```
PATCH /listings/{id}/location
Authorization: Bearer <token>
```

**Body:**
```json
{
  "area": "Mirpur-10",
  "division_id": 1,
  "district_id": 21,
  "upazila_id": 341,
  "union_id": null,
  "road": "Road 5",
  "house_name": "Green Villa",
  "block": "A",
  "section": "11",
  "coord_y": 23.8103,
  "coord_x": 90.4125
}
```

| Field | Type | Description |
|---|---|---|
| `area` | string | Neighborhood/area name |
| `division_id` | int | From `/geo/divisions` |
| `district_id` | int | From `/geo/districts/{id}` |
| `upazila_id` | int | From `/geo/upazilas/{id}` |
| `union_id` | int | From `/geo/unions/{id}` (optional) |
| `road` | string | Road name/number |
| `house_name` | string | Building/house name |
| `block` | string | Block identifier |
| `section` | string | Section identifier |
| `coord_y` | float | **Latitude** (Y-axis) — from map picker |
| `coord_x` | float | **Longitude** (X-axis) — from map picker |

**Response `data`:** Updated listing object.

---

### Step 4 (Optional) — Patch Listing Details

```
PATCH /listings/{id}
Authorization: Bearer <token>
```

Same fields as Step 1 (all optional — only include what changed). Also accepts address fields:

```json
{
  "road": "Road 3",
  "house_name": "Blue Tower",
  "block": "B",
  "section": "12",
  "owner_name": "Rahim Ahmed",
  "owner_phone": "01711000001",
  "owner_alt_phone": "01811000002",
  "owner_email": "rahim@example.com",
  "preferred_contact": "phone"
}
```

---

### Step 5a — Save Owner Contact Info

```
PATCH /listings/{id}/owner-info
Authorization: Bearer <token>
```

**Body:**
```json
{
  "owner_name": "Rahim Ahmed",
  "owner_phone": "01711000001",
  "owner_alt_phone": "01811000002",
  "owner_email": "rahim@example.com",
  "preferred_contact": "phone"
}
```

| Field | Description |
|---|---|
| `owner_name` | Contact person's name |
| `owner_phone` | Primary phone |
| `owner_alt_phone` | Alternate phone |
| `owner_email` | Contact email |
| `preferred_contact` | `phone` \| `email` \| `chat` |

**Response `data`:** Updated listing object.

---

### Step 5b — Submit for Review

```
POST /listings/{id}/submit
Authorization: Bearer <token>
```

**Body:** `{}` (empty)

**Response:** `{ "success": true }`

> After submission, listing `status` changes from `draft` → `submitted`. An admin reviews it and approves/rejects. The owner gets a push notification when the status changes.

---

## 11. Owner — Edit Listing

Edit an existing listing (any approved/rejected/draft listing).

```
PATCH /listings/{id}
Authorization: Bearer <token>
```

Accepts any subset of the fields from Create Listing (§10 Step 1) plus address and owner-info fields (§10 Step 4/5a). Only the provided fields are updated.

**Full editable fields:**

```json
{
  "title": "...",
  "listing_type_id": 1,
  "price": 28000,
  "beds": 3,
  "baths": 2,
  "deposit": 56000,
  "size": 1200,
  "description": "...",
  "amenities": [1, 2, 3],
  "available_from": "2026-08-01",
  "floor_no": 5,
  "facing_id": 2,
  "road": "Road 3",
  "house_name": "Blue Tower",
  "block": "B",
  "section": "12",
  "owner_name": "Rahim",
  "owner_phone": "01711000001",
  "owner_alt_phone": "01811000002",
  "owner_email": "rahim@example.com",
  "preferred_contact": "phone"
}
```

**Response `data`:** Updated listing object.

---

## 12. Owner — Listing Actions

### 12.1 Resubmit After Rejection

If a listing was `rejected`, the owner fixes the issues and resubmits:

```
POST /listings/{id}/submit
Authorization: Bearer <token>
Body: {}
```

**Response:** `{ "success": true }`

---

### 12.2 Mark as Rented

```
POST /listings/{id}/mark-rented
Authorization: Bearer <token>
Body: {}
```

**Response:** `{ "success": true }`

Status changes to `rented`. The listing is hidden from renter discovery.

---

### 12.3 Delete Listing

```
DELETE /listings/{id}
Authorization: Bearer <token>
```

**Response:** `{ "success": true }`

---

## 13. Chat / Messaging

### 13.1 Get All Chat Conversations

```
GET /chats
Authorization: Bearer <token>
```

**Response `data`:**
```json
[
  {
    "id": "chat-uuid",
    "listing": {
      "id": "listing-uuid",
      "title": "3 Bed Apartment",
      "area": "Mirpur-10"
    },
    "other_user": {
      "id": "user-uuid",
      "name": "John Doe",
      "avatar_url": "/storage/avatars/john.jpg"
    },
    "last_message": {
      "id": "msg-uuid",
      "chat_id": "chat-uuid",
      "sender_id": "user-uuid",
      "sender": { "id": "...", "name": "John", "avatar_url": null },
      "text": "Is this still available?",
      "is_read": false,
      "created_at": "2026-05-24T10:30:00Z"
    },
    "unread_count": 3,
    "updated_at": "2026-05-24T10:30:00Z"
  }
]
```

---

### 13.2 Start a New Chat (Renter → Owner)

Renters initiate a chat from a listing detail page.

```
POST /chats
Authorization: Bearer <token>
```

**Body:**
```json
{
  "listing_id": "listing-uuid",
  "initial_message": "Is this apartment still available?"
}
```

**Response `data`:** The created chat object (same shape as §13.1).

> If a chat for this listing already exists with this owner, the server returns the existing chat.

---

### 13.3 Get Messages in a Chat

```
GET /chats/{chat_id}/messages
Authorization: Bearer <token>
```

**Response `data`:**
```json
[
  {
    "id": "msg-uuid",
    "chat_id": "chat-uuid",
    "sender_id": "user-uuid",
    "sender": {
      "id": "user-uuid",
      "name": "John Doe",
      "avatar_url": null
    },
    "text": "Is this still available?",
    "is_read": true,
    "created_at": "2026-05-24T10:30:00Z"
  }
]
```

---

### 13.4 Send a Message

```
POST /chats/{chat_id}/messages
Authorization: Bearer <token>
```

**Body:**
```json
{ "text": "Yes, it is available from July 1st." }
```

**Response `data`:** The new `ChatMessageModel`.

---

## 14. Notifications

### 14.1 Get Notifications (Paginated)

```
GET /notifications?page=1
Authorization: Bearer <token>
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "notif-uuid",
      "kind": "listing_approved",
      "title": "Listing Approved!",
      "body": "Your listing '3 Bed Apartment' has been approved.",
      "time": "2 hours ago",
      "is_unread": true,
      "reference_id": "listing-uuid"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "unread_count": 5
  }
}
```

**Notification Kinds:**

| Kind | Who receives | What triggers it |
|---|---|---|
| `listing_approved` | Owner | Admin approves their listing |
| `listing_rejected` | Owner | Admin rejects their listing |
| `listing_submitted` | Admin | Owner submits a listing |
| `listing_review` | Owner | Listing sent back for revision |
| `new_message` | Both | New chat message received |

---

### 14.2 Get Unread Count

```
GET /notifications/unread-count
Authorization: Bearer <token>
```

**Response `data`:**
```json
{ "unread_count": 5 }
```

---

### 14.3 Mark One Notification as Read

```
PATCH /notifications/{id}/read
Authorization: Bearer <token>
```

**Response:** `{ "success": true }`

---

### 14.4 Mark All Notifications as Read

```
PATCH /notifications/read-all
Authorization: Bearer <token>
```

**Response:** `{ "success": true }`

---

## 15. Device — FCM Token

Register/update the device's Firebase Cloud Messaging token for push notifications. Call this immediately after login/Google Sign-In.

```
POST /device/fcm-token
Authorization: Bearer <token>
```

**Body:**
```json
{
  "fcm_token": "firebase_fcm_token_string",
  "device_type": "android",
  "device_model": "Samsung Galaxy S24"
}
```

| Field | Values |
|---|---|
| `device_type` | `android` \| `ios` \| `web` |
| `device_model` | e.g. `Samsung Galaxy S24`, `iPhone 15 Pro`, `Chrome/Windows` |

**Response:** `{ "success": true }`

> For a web app, use Firebase Web SDK to get the FCM token. Set `device_type` to `web`.

---

## 16. User Location

Update the user's current GPS location on the backend (used for proximity features).

```
PATCH /user/location
Authorization: Bearer <token>
```

**Body:**
```json
{
  "lat": 23.8103,
  "lng": 90.4125
}
```

**Response:** `{ "success": true }`

---

## 17. Data Models

### UserModel

```typescript
{
  id: string              // UUID
  name: string
  email: string
  phone: string | null
  role: "renter" | "owner" | null
  avatar_url: string | null  // may be relative path — resolve with base URL
  is_complete: boolean    // false = registration not finished
}
```

### ListingModel

```typescript
{
  id: string
  title: string
  area: string | null
  road_and_house: string | null
  price: number           // monthly rent in BDT
  deposit: number | null
  beds: number | null
  baths: number | null
  size: number | null     // sq ft
  floor_no: number | null
  facing_id: number | null
  facing: ListingFacingModel | null
  available_from: string | null  // ISO date, null = available now
  description: string | null
  status: "draft" | "submitted" | "approved" | "rejected" | "rented"
  status_label: string
  views: number
  coord_y: number | null  // latitude
  coord_x: number | null  // longitude
  distance_km: number | null  // only in /listings/nearby
  listing_type_id: number | null
  type: string            // slug e.g. "apartment"
  division_id: number | null
  district_id: number | null
  upazila_id: number | null
  union_id: number | null
  amenities: AmenityModel[]
  photos: ListingPhotoModel[]
  owner: ListingOwnerModel | null
  created_at: string      // ISO datetime
  // Owner-only fields (in OwnerListingModel):
  inquiries: number
  rejection_reason: string | null
}
```

### ListingPhotoModel

```typescript
{
  id: string
  url: string    // fully resolved URL (prepend base if relative)
  position: number  // sort order — lowest position = thumbnail
}
```

### ChatModel

```typescript
{
  id: string
  listing: { id: string, title: string, area: string | null }
  other_user: { id: string, name: string, avatar_url: string | null }
  last_message: ChatMessageModel | null
  unread_count: number
  updated_at: string
}
```

### ChatMessageModel

```typescript
{
  id: string
  chat_id: string
  sender_id: string
  sender: { id: string, name: string, avatar_url: string | null } | null
  text: string
  is_read: boolean
  created_at: string
}
```

### NotificationModel

```typescript
{
  id: string
  kind: string       // e.g. "listing_approved"
  title: string
  body: string
  time: string       // human-readable relative time e.g. "2 hours ago"
  is_unread: boolean
  reference_id: string | null  // e.g. listing ID to navigate to
}
```

---

## 18. Business Flow Diagrams

### Registration Flow

```
[Landing Page]
     │
     ├─ "Sign Up" ──→ Step 1: POST /auth/register
     │                         │
     │                    Save tokens
     │                         │
     │               Step 2: PATCH /auth/register/details (role)
     │                         │
     │               Step 3: PATCH /auth/register/avatar (optional)
     │                         │
     │               is_complete = true → route by role
     │
     └─ "Google" ──→ POST /auth/google
                          │
                     if is_complete=false → Step 2 (role selection)
                     if is_complete=true  → route by role
```

### Role-Based Routing

```
After login / auth:

  role = "owner"  → Owner Home (dashboard, listings, messages, profile)
  role = "renter" → Renter Home (discovery, map, wishlist, messages, profile)
  role = null     → Registration Step 2 (role selection)
```

### Owner — Create Listing Flow

```
[Create Listing Wizard]
  │
  Step 1: Basic Info (title, type, price, beds, baths, amenities, description)
       └─ POST /listings → get listing_id
  │
  Step 2: Photos
       └─ POST /listings/{id}/photos (multipart, photos[])
  │
  Step 3: Location (area, division → district → upazila → union, road, GPS)
       └─ PATCH /listings/{id}/location
  │
  Step 4: Owner Contact Info (name, phone, alt phone, email, preferred contact)
       └─ PATCH /listings/{id}/owner-info
  │
  Step 5: Review & Submit
       └─ POST /listings/{id}/submit
              │
         status: "submitted"
              │
         Admin reviews → "approved" or "rejected"
              │                  │
         (push notif)        (push notif with rejection_reason)
              │                  │
         Visible to renters  Owner edits → resubmit
```

### Renter — Discovery & Filter Flow

```
[Discovery Page]
  │
  onMount:
    GET /listing-types  → type chips (All, Apartment, Bachelor, Sublet...)
    GET /amenities      → filter sheet options
    GET /geo/divisions  → location filter
    PATCH /user/location (background, after GPS acquired)
    GET /listings       → initial listing cards
  │
  User interactions:
    Type chip click → GET /listings?listing_type_id=X
    Search input (debounced 500ms) → GET /listings?search=...
    Filter sheet → GET /listings?price_min=&price_max=&beds=&...
    Pull-to-refresh → GET /listings (same params)
    Listing card tap → GET /listings/{id} (detail view)
    Save button → POST /wishlist/{id}/toggle
  │
  [Map Tab]
    Get GPS → GET /listings/nearby?coord_x=lng&coord_y=lat&radius=5
```

### Chat Flow

```
[Renter — Listing Detail]
  "Contact Owner" button
        │
  POST /chats { listing_id, initial_message }
        │
  → Chat Detail screen
        │
  GET /chats/{id}/messages → load history
  POST /chats/{id}/messages { text } → send message
        │
  [Owner — Messages Tab]
  GET /chats → list all conversations
  Tap conversation → GET /chats/{id}/messages
```

### Notification Flow

```
App Launch / Login:
  POST /device/fcm-token  (register push token)
  GET /notifications/unread-count  (badge on bell icon)

Notification Bell tap:
  GET /notifications?page=1
  → paginate with ?page=2, 3...

Tap notification:
  PATCH /notifications/{id}/read
  → navigate based on 'kind':
      listing_approved / rejected / submitted → Owner Dashboard
      new_message → Chat Detail

Mark all read:
  PATCH /notifications/read-all
```

---

## Quick Reference — All Endpoints

| Method | Endpoint | Auth | Who | Description |
|---|---|---|---|---|
| `POST` | `/auth/login` | No | Both | Email/password login |
| `POST` | `/auth/google` | No | Both | Google Sign-In |
| `POST` | `/auth/register` | No | Both | Register new account |
| `PATCH` | `/auth/register/details` | Yes | Both | Set role (step 2) |
| `PATCH` | `/auth/register/avatar` | Yes | Both | Upload avatar (step 3) |
| `POST` | `/auth/refresh` | No | Both | Refresh access token |
| `POST` | `/auth/logout` | Yes | Both | Logout |
| `DELETE` | `/auth/account` | Yes | Both | Delete account |
| `GET` | `/listing-types` | No | Both | Listing type options |
| `GET` | `/meta/listing-types` | No | Both | Same as above (alt path) |
| `GET` | `/meta/listing-facings` | No | Both | Facing direction options |
| `GET` | `/amenities` | No | Both | All amenities |
| `GET` | `/meta/roles` | No | Both | User role options |
| `GET` | `/geo/divisions` | No | Both | All divisions |
| `GET` | `/geo/districts/{division_id}` | No | Both | Districts by division |
| `GET` | `/geo/upazilas/{district_id}` | No | Both | Upazilas by district |
| `GET` | `/geo/unions/{upazila_id}` | No | Both | Unions by upazila |
| `GET` | `/listings` | Yes | Renter | Browse & filter listings |
| `GET` | `/listings/nearby` | Yes | Renter | Map-based nearby listings |
| `GET` | `/listings/{id}` | Yes | Both | Listing detail |
| `POST` | `/listings` | Yes | Owner | Create listing (draft) |
| `PATCH` | `/listings/{id}` | Yes | Owner | Edit listing |
| `DELETE` | `/listings/{id}` | Yes | Owner | Delete listing |
| `POST` | `/listings/{id}/photos` | Yes | Owner | Upload listing photos |
| `PATCH` | `/listings/{id}/location` | Yes | Owner | Save location & GPS |
| `PATCH` | `/listings/{id}/owner-info` | Yes | Owner | Save contact info |
| `POST` | `/listings/{id}/submit` | Yes | Owner | Submit for review |
| `POST` | `/listings/{id}/mark-rented` | Yes | Owner | Mark as rented |
| `GET` | `/owner/listings` | Yes | Owner | Paginated own listings |
| `GET` | `/wishlist` | Yes | Renter | Saved/wishlisted listings |
| `POST` | `/wishlist/{id}/toggle` | Yes | Renter | Save or unsave listing |
| `GET` | `/chats` | Yes | Both | All chat conversations |
| `POST` | `/chats` | Yes | Renter | Start new chat |
| `GET` | `/chats/{id}/messages` | Yes | Both | Messages in chat |
| `POST` | `/chats/{id}/messages` | Yes | Both | Send message |
| `GET` | `/notifications` | Yes | Both | Paginated notifications |
| `GET` | `/notifications/unread-count` | Yes | Both | Unread badge count |
| `PATCH` | `/notifications/{id}/read` | Yes | Both | Mark one as read |
| `PATCH` | `/notifications/read-all` | Yes | Both | Mark all as read |
| `POST` | `/device/fcm-token` | Yes | Both | Register push token |
| `PATCH` | `/user/location` | Yes | Both | Update GPS location |
