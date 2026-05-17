# FlatNest — "Near Me" Feature Spec & Implementation Notes

> Implemented: `GET /api/v1/listings/nearby`
> Controller: `ListingController@nearby`
> Service:    `ListingService@getNearby`
> Repository: `ListingRepository@findNearby`

---

## 📍 Database Coordinate Columns (DO NOT CHANGE)

| DB Column | Meaning     | Global Standard |
|-----------|-------------|-----------------|
| `coord_x` | **longitude** | lng / x axis  |
| `coord_y` | **latitude**  | lat / y axis  |

**Frontend must send:**
```
lat → coord_y
lng → coord_x
```

Both values are `DOUBLE / float`, nullable. `NULL` rows are excluded from geo search.

---

## 🎯 Endpoint

```
GET /api/v1/listings/nearby
```

No auth required (public).

### Query Parameters

| Param                  | Required | Default | Constraints           | Description                        |
|------------------------|----------|---------|-----------------------|------------------------------------|
| `coord_x`              | ✅ Yes   | —       | float, longitude      | User's longitude                   |
| `coord_y`              | ✅ Yes   | —       | float, latitude       | User's latitude                    |
| `radius`               | ❌ No    | `10`    | 0.5 – 50 km           | Search radius in km                |
| **— Listing —**        |          |         |                       |                                    |
| `listing_type_id`      | ❌ No    | —       | integer               | Filter by listing type             |
| `price_min`            | ❌ No    | —       | integer               | Minimum price (BDT)                |
| `price_max`            | ❌ No    | —       | integer               | Maximum price (BDT)                |
| **— Rooms —**          |          |         |                       |                                    |
| `beds`                 | ❌ No    | —       | integer               | Exact number of bedrooms           |
| `baths`                | ❌ No    | —       | integer               | Exact number of bathrooms          |
| **— Property —**       |          |         |                       |                                    |
| `facing_id`            | ❌ No    | —       | integer               | Filter by facing direction         |
| `floor_min`            | ❌ No    | —       | integer               | Minimum floor number               |
| `floor_max`            | ❌ No    | —       | integer               | Maximum floor number               |
| `size_min`             | ❌ No    | —       | integer (sq ft)       | Minimum floor area                 |
| `size_max`             | ❌ No    | —       | integer (sq ft)       | Maximum floor area                 |
| **— Availability —**   |          |         |                       |                                    |
| `available_from_start` | ❌ No    | —       | date `Y-m-d`          | Available from this date or later  |
| `available_from_end`   | ❌ No    | —       | date `Y-m-d`          | Available from this date or sooner |
| **— Location —**       |          |         |                       |                                    |
| `division_id`          | ❌ No    | —       | integer               | Narrow to division (within radius) |
| `district_id`          | ❌ No    | —       | integer               | Narrow to district                 |
| `upazila_id`           | ❌ No    | —       | integer               | Narrow to upazila                  |
| `union_id`             | ❌ No    | —       | integer               | Narrow to union                    |
| **— Search —**         |          |         |                       |                                    |
| `search`               | ❌ No    | —       | string                | Text search (title, area, road…)   |
| `amenities`            | ❌ No    | —       | `"1,3,5"` CSV ints    | All listed amenities must match    |
| **— Sort —**           |          |         |                       |                                    |
| `sort_by`              | ❌ No    | —       | see values below      | Secondary sort (distance is always primary) |

**`sort_by` values:** `price_asc` · `price_desc` · `available_soon`
> Omit `sort_by` to get results sorted by distance only.

> If only one of `coord_x` / `coord_y` is sent → **422 GEO_INCOMPLETE**
> If neither is sent → **422 GEO_REQUIRED**

---

## ⚡ Optimized Search Flow

```
STEP 1 — Input validation
   └── both coords required, radius clamped to [0.5, 50]

STEP 2 — Bounding box pre-filter  (hits coord index — fast)
   latDelta = radius / 111
   lngDelta = radius / (111 × cos(radians(lat)))
   WHERE coord_y BETWEEN [lat - latDelta, lat + latDelta]
     AND coord_x BETWEEN [lng - lngDelta, lng + lngDelta]

STEP 3 — Haversine exact distance (accurate)
   distance_km = 6371 × acos(LEAST(1, GREATEST(-1,
       cos(radians(lat)) × cos(radians(coord_y)) ×
       cos(radians(coord_x) - radians(lng)) +
       sin(radians(lat)) × sin(radians(coord_y))
   )))
   HAVING distance_km <= radius

STEP 4 — Sort by distance ASC
STEP 5 — Paginate (15 per page), eager-load relations
```

---

## 📦 Response Shape

### With results

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "3BHK Family Flat – Dhanmondi",
      "coord_x": 90.3742,
      "coord_y": 23.7461,
      "distance_km": 1.43,
      "price": 35000,
      "beds": 3,
      "baths": 2,
      "listing_type": { "id": 1, "label": "Family", "slug": "family" },
      "photos": [ ... ],
      "..."
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  },
  "message": null
}
```

### No results

```json
{
  "success": true,
  "data": [],
  "message": "No listings found within the specified area."
}
```

---

## 🗺️ Why coord_x / coord_y are always returned

Every listing response already includes `coord_x` and `coord_y` (via `ListingResource`).
The `distance_km` field is **additionally** appended by the Haversine `selectRaw`.

Frontend needs these to:
- ✅ Show pins on map
- ✅ Render markers
- ✅ Future: clustering / geohash upgrade

---

## 🧠 Index Required

```sql
-- Already present in listings migration:
$table->index(['coord_x', 'coord_y']);
```

This index makes the bounding box `BETWEEN` query fast on large datasets.

---

## 🔧 Architecture

This feature follows the existing Repository → Service → Controller pattern.
**All existing methods are untouched.** The geo search is isolated in:

- `ListingRepositoryInterface::findNearby()`
- `ListingRepository::findNearby()`
- `ListingService::getNearby()`
- `ListingController::nearby()`

---

## 🧪 Test in Postman

```
# Basic — 5km radius
GET /api/v1/listings/nearby?coord_x=90.3742&coord_y=23.7461&radius=5

# By listing type + price cap
GET /api/v1/listings/nearby?coord_x=90.3742&coord_y=23.7461&radius=10&listing_type_id=1&price_max=30000

# By rooms
GET /api/v1/listings/nearby?coord_x=90.3742&coord_y=23.7461&radius=10&beds=3&baths=2

# By property size + floor
GET /api/v1/listings/nearby?coord_x=90.3742&coord_y=23.7461&radius=15&size_min=800&floor_max=5

# By facing direction
GET /api/v1/listings/nearby?coord_x=90.3742&coord_y=23.7461&radius=10&facing_id=2

# By availability window
GET /api/v1/listings/nearby?coord_x=90.3742&coord_y=23.7461&radius=10&available_from_start=2026-06-01&available_from_end=2026-09-01

# Narrow to a district within the radius
GET /api/v1/listings/nearby?coord_x=90.3742&coord_y=23.7461&radius=20&district_id=4

# Text search within radius
GET /api/v1/listings/nearby?coord_x=90.3742&coord_y=23.7461&radius=10&search=dhanmondi

# By amenities (Wifi=1, Parking=3, Gas=5 — all must be present)
GET /api/v1/listings/nearby?coord_x=90.3742&coord_y=23.7461&radius=10&amenities=1,3,5

# Combined — the real-world use case
GET /api/v1/listings/nearby?coord_x=90.3742&coord_y=23.7461&radius=10
  &listing_type_id=1&price_max=40000&beds=3&baths=2&amenities=1,3
```

Error cases:
```
GET /listings/nearby?coord_x=90.37   → 422 GEO_INCOMPLETE (missing coord_y)
GET /listings/nearby                 → 422 GEO_REQUIRED
```

---

*FlatNest Near Me — v1.1 — Laravel + PostgreSQL*
