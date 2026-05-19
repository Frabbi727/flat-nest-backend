# Listing Data Model Reference

**For mobile app developers (Flutter / Android / iOS)**

Listing creation is a 4-step flow. Each step is a separate API call.

---

## Step 1 — Create Listing

```
POST /api/v1/listings
Authorization: Bearer <access_token>
Content-Type: application/json
```

| Field | Type | Required | Notes |
|---|---|---|---|
| `title` | String | Yes | max 255 chars |
| `listing_type_id` | Int | Yes | from `GET /meta/listing-types` |
| `price` | Int | Yes | monthly rent in BDT, min 0 |
| `beds` | Int | Yes | number of bedrooms, min 0 |
| `baths` | Int | Yes | number of bathrooms, min 0 |
| `deposit` | Int | No | security deposit in BDT |
| `size` | Int | No | area in sq ft |
| `floor_no` | Int | No | floor number, 0–100 |
| `facing_id` | Int | No | from `GET /meta/listing-facings` |
| `available_from` | String | No | date format `YYYY-MM-DD`, today or future |
| `description` | String | No | free text description |
| `amenities` | Int[] | No | array of amenity IDs from `GET /amenities` |

**Example:**
```json
{
  "title": "2BHK Flat in Mirpur",
  "listing_type_id": 1,
  "price": 18000,
  "beds": 2,
  "baths": 2,
  "deposit": 36000,
  "size": 900,
  "floor_no": 3,
  "facing_id": 1,
  "available_from": "2026-06-01",
  "description": "Spacious flat, gas included.",
  "amenities": [1, 3, 5]
}
```

**Response:** returns the created listing object (see GET model below), with `status: "draft"`.

---

## Step 2 — Upload Photos

```
POST /api/v1/listings/{id}/photos
Authorization: Bearer <access_token>
Content-Type: multipart/form-data
```

| Field | Type | Required | Notes |
|---|---|---|---|
| `photos` | File[] | Yes | min 1 file, jpg/jpeg/png only, max 5MB each |

**Example (Flutter):**
```dart
var request = http.MultipartRequest('POST', Uri.parse('$baseUrl/api/v1/listings/$id/photos'));
request.headers['Authorization'] = 'Bearer $token';
for (final file in selectedFiles) {
  request.files.add(await http.MultipartFile.fromPath('photos[]', file.path));
}
```

---

## Step 3 — Set Location

```
PATCH /api/v1/listings/{id}/location
Authorization: Bearer <access_token>
Content-Type: application/json
```

| Field | Type | Required | Notes |
|---|---|---|---|
| `area` | String | No | neighbourhood/area name |
| `division_id` | Int | No | from `GET /geo/divisions` |
| `district_id` | Int | No | from `GET /geo/districts/{division_id}` |
| `upazila_id` | Int | No | from `GET /geo/upazilas/{district_id}` |
| `union_id` | Int | No | from `GET /geo/unions/{upazila_id}` |
| `coord_x` | Double | No | longitude (e.g. `90.4125`) |
| `coord_y` | Double | No | latitude (e.g. `23.8103`) |
| `road` | String | No | road name or number |
| `house_name` | String | No | building / house name |
| `block` | String | No | block (e.g. "Block C") |
| `section` | String | No | section (e.g. "Section 10") |

> `coord_x` = longitude, `coord_y` = latitude. Required for nearby listing notifications to work.

---

## Step 4 — Owner Contact Info

```
PATCH /api/v1/listings/{id}/owner-info
Authorization: Bearer <access_token>
Content-Type: application/json
```

| Field | Type | Required | Notes |
|---|---|---|---|
| `owner_name` | String | No | display name for contact |
| `owner_phone` | String | No | primary phone, max 20 chars |
| `owner_alt_phone` | String | No | alternate phone, max 20 chars |
| `owner_email` | String | No | valid email |
| `preferred_contact` | String | No | `call`, `whatsapp`, or `both` |

---

## Step 5 — Submit for Review

```
POST /api/v1/listings/{id}/submit
Authorization: Bearer <access_token>
```

No request body. Listing must have at least 1 photo (Step 2) before this will succeed.

---

## Update Listing (any time before/after submit)

```
PATCH /api/v1/listings/{id}
Authorization: Bearer <access_token>
Content-Type: application/json
```

All fields from Steps 1, 3, and 4 combined — every field is optional. Send only what you want to change.

---

## Other Owner Actions

| Endpoint | Method | Description |
|---|---|---|
| `POST /listings/{id}/mark-rented` | POST | Mark listing as rented (only active listings) |
| `DELETE /listings/{id}` | DELETE | Delete the listing |
| `GET /owner/listings` | GET | Get all your listings (paginated) |

Query params for `GET /owner/listings`: `status` (draft/pending/active/rejected/rented), `listing_type_id`

---

## GET /listings/{id} — Full Response Model

```
GET /api/v1/listings/{id}
```

```json
{
  "success": true,
  "data": {
    "id": "uuid-string",
    "owner_id": "uuid-string",
    "title": "2BHK Flat in Mirpur",
    "listing_type_id": 1,
    "listing_type": { "id": 1, "label": "Apartment", "slug": "apartment" },
    "price": 18000,
    "deposit": 36000,
    "available_from": "2026-06-01",
    "beds": 2,
    "baths": 2,
    "size": 900,
    "floor_no": 3,
    "facing_id": 1,
    "facing": { "id": 1, "label": "South", "slug": "south" },
    "description": "Spacious flat, gas included.",
    "division_id": 3,
    "district_id": 18,
    "upazila_id": 204,
    "union_id": 1502,
    "division": { "id": 3, "name": "Dhaka" },
    "district": { "id": 18, "name": "Dhaka" },
    "upazila": { "id": 204, "name": "Mirpur" },
    "union": { "id": 1502, "name": "Pallabi" },
    "area": "Mirpur-10",
    "road": "Road 5",
    "house_name": "Green Tower",
    "block": "Block C",
    "section": "Section 10",
    "coord_x": 90.3625,
    "coord_y": 23.8223,
    "owner_name": "Rahim Uddin",
    "owner_phone": "01711000000",
    "owner_alt_phone": "01811000000",
    "owner_email": "rahim@example.com",
    "preferred_contact": "whatsapp",
    "status": "active",
    "status_label": "Active",
    "rejection_reason": null,
    "views": 42,
    "owner": { "id": "uuid", "name": "Rahim Uddin", "phone": "01711000000" },
    "photos": [
      { "id": 1, "url": "https://...", "position": 0 },
      { "id": 2, "url": "https://...", "position": 1 }
    ],
    "amenities": [
      { "id": 1, "name": "gas", "label": "Gas" },
      { "id": 3, "name": "wifi", "label": "WiFi" }
    ],
    "created_at": "2026-05-19T10:00:00Z",
    "updated_at": "2026-05-19T12:00:00Z"
  }
}
```

---

## Field Type Reference

| Field | Dart type | Notes |
|---|---|---|
| `id`, `owner_id` | `String` | UUID |
| `title`, `area`, `road`, `house_name`, `block`, `section` | `String?` | |
| `description`, `rejection_reason` | `String?` | |
| `owner_name`, `owner_phone`, `owner_alt_phone`, `owner_email` | `String?` | |
| `preferred_contact` | `String?` | `call`/`whatsapp`/`both` |
| `status` | `String` | `draft`/`pending`/`active`/`rejected`/`rented` |
| `status_label` | `String` | human-readable |
| `price`, `deposit`, `beds`, `baths`, `size`, `floor_no`, `views` | `int?` | |
| `listing_type_id`, `facing_id` | `int?` | |
| `division_id`, `district_id`, `upazila_id`, `union_id` | `int?` | |
| `coord_x` | `double?` | longitude |
| `coord_y` | `double?` | latitude |
| `available_from` | `String?` | `YYYY-MM-DD` |
| `created_at`, `updated_at` | `String` | ISO 8601 datetime |
| `listing_type`, `facing` | `Object?` | `{ id, label, slug }` |
| `division`, `district`, `upazila`, `union` | `Object?` | `{ id, name }` |
| `owner` | `Object` | `{ id, name, phone }` |
| `photos` | `List` | `[{ id, url, position }]` |
| `amenities` | `List` | `[{ id, name, label }]` |

---

## Listing Status Flow

```
Draft → (submit) → Pending → (admin approve) → Active
                           → (admin reject)  → Rejected → (resubmit) → Pending
Active → (owner edits)    → Pending (re-review)
Active → (mark-rented)    → Rented
```

| Status | Meaning |
|---|---|
| `draft` | Created, not yet submitted |
| `pending` | Submitted, waiting for admin review |
| `active` | Approved, visible in feed |
| `rejected` | Rejected by admin, check `rejection_reason` |
| `rented` | Marked as rented by owner, no longer available |
