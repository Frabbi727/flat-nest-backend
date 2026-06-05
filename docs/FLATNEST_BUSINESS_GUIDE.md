# FlatNest — Business Guide for Web Frontend

> **API Base URL:** `https://flatnest.techrealify.com/api/v1`
> **All requests need:** `Accept: application/json`
> **Authenticated requests need:** `Authorization: Bearer <access_token>`

---

## HOW AUTHENTICATION WORKS

Every response from the API returns:
```json
{ "success": true, "data": { ... } }
```

After login, you get two tokens:
- `access_token` — use this in every API call (`Authorization: Bearer <token>`)
- `refresh_token` — use this to silently get a new access_token when it expires (401 response)

**Token refresh (do this silently when you get a 401):**
```
POST /auth/refresh
Body: { "refresh_token": "..." }
Returns: { "data": { "access_token": "new_token" } }
```

If refresh fails → log the user out, go to login page.

---

---

## 1. REGISTRATION FLOW

### How it works

Registration is **3 steps**. The user completes them in sequence. After Step 1 they are logged in (you get tokens), Steps 2 and 3 complete their profile.

---

### Step 1 — Create Account

The user fills: Name, Email, Phone, Password

```
POST /auth/register
Body: {
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "01711000000",
  "password": "secret123"
}
```

**What you get back:**
```json
{
  "access_token": "...",
  "refresh_token": "...",
  "user": {
    "id": "uuid",
    "name": "John Doe",
    "email": "john@example.com",
    "role": null,
    "is_complete": false
  }
}
```

→ Save the tokens. Go to Step 2.

---

### Step 2 — Choose Role

User picks: **I am an Owner** or **I am a Renter**

```
PATCH /auth/register/details
Authorization: Bearer <token>
Body: { "role": "owner" }     ← or "renter"
```

→ Save the role in your local state. Go to Step 3.

---

### Step 3 — Upload Profile Photo (Optional but shown)

User picks a photo from their device.

```
PATCH /auth/register/avatar
Authorization: Bearer <token>
Content-Type: multipart/form-data
Body: avatar = <image file>
```

**What you get back:**
```json
{ "data": { "avatar_url": "/storage/avatars/abc.jpg" } }
```

> Image URLs starting with `/` → prepend `https://flatnest.techrealify.com`

→ Registration complete. Now route by role:
- `role = "owner"` → Owner Dashboard
- `role = "renter"` → Renter Home (Discovery)

---

### Google Sign-In (Alternative to Steps 1-3)

User clicks "Continue with Google" → you get a Google ID token → send it to the API.

```
POST /auth/google
Body: { "id_token": "<google_id_token>" }
```

**Returns same response as login.**

- If `is_complete = false` → send user to **Step 2** (role selection), they skip Step 1
- If `is_complete = true` → route by role directly

---

### Login (Returning User)

```
POST /auth/login
Body: { "email": "john@example.com", "password": "secret123" }
```

Returns same response. Check `role` and route accordingly.

---

### Logout

```
POST /auth/logout
Authorization: Bearer <token>
```

Clear tokens from storage, redirect to login.

---

---

## 2. OWNER — HOW TO POST A FLAT

### How it works

Posting a flat is a **5-step wizard**. The flat is saved as a `draft` during the wizard. At the end the owner submits it for admin review. Once approved by admin it goes live.

**Listing Status Journey:**
```
draft → submitted → approved (live, visible to renters)
                 → rejected  (owner must fix and resubmit)
              
approved → rented  (owner marks it rented, disappears from search)
```

---

### Step 1 — Basic Info

Owner fills: Title, Type, Price, Beds, Baths, and optionally: Deposit, Size, Description, Amenities, Available From, Floor, Facing.

```
POST /listings
Authorization: Bearer <token>
Body: {
  "title": "Spacious 3 Bed Apartment in Mirpur",
  "listing_type_id": 1,
  "price": 25000,
  "beds": 3,
  "baths": 2,
  "deposit": 50000,
  "size": 1200,
  "description": "Bright, well-ventilated flat...",
  "amenities": [1, 2, 4],
  "available_from": "2026-07-01",
  "floor_no": 4,
  "facing_id": 1
}
```

**Required:** `title`, `listing_type_id`, `price`, `beds`, `baths`
**Optional:** everything else

> **To fill `listing_type_id`:** call `GET /listing-types` to get the list
> **To fill `amenities` array:** call `GET /amenities` to get IDs
> **To fill `facing_id`:** call `GET /meta/listing-facings`
> **`available_from`:** date string `YYYY-MM-DD`, or leave it out = "Available Now"

**What you get back:**
```json
{ "data": { "id": "listing-uuid", "status": "draft", ... } }
```

→ **Save the `id`**. You need it for all remaining steps.

---

### Step 2 — Upload Photos

Owner picks photos from their device (can be multiple).

```
POST /listings/{listing_id}/photos
Authorization: Bearer <token>
Content-Type: multipart/form-data
Body: photos[] = file1, photos[] = file2, photos[] = file3
```

→ Done. No special response needed.

---

### Step 3 — Location

Owner fills: Area name, Division, District, Upazila, Union (optional), Road, House Name, Block, Section. Also includes GPS coordinates from a map picker.

```
PATCH /listings/{listing_id}/location
Authorization: Bearer <token>
Body: {
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

> **Geo dropdowns are cascaded:**
> 1. `GET /geo/divisions` → fill Division dropdown
> 2. Owner picks Division → `GET /geo/districts/{division_id}` → fill District dropdown
> 3. Owner picks District → `GET /geo/upazilas/{district_id}` → fill Upazila dropdown
> 4. Owner picks Upazila → `GET /geo/unions/{upazila_id}` → fill Union dropdown

> **GPS:** `coord_y` = latitude, `coord_x` = longitude (use Google Maps / Leaflet picker)

---

### Step 4 — Owner Contact Info

Who should renters contact? Owner fills their name and contact details.

```
PATCH /listings/{listing_id}/owner-info
Authorization: Bearer <token>
Body: {
  "owner_name": "Rahim Ahmed",
  "owner_phone": "01711000001",
  "owner_alt_phone": "01811000002",
  "owner_email": "rahim@example.com",
  "preferred_contact": "phone"
}
```

`preferred_contact` options: `"phone"` | `"email"` | `"chat"`

---

### Step 5 — Submit for Review

Owner reviews everything and hits Submit.

```
POST /listings/{listing_id}/submit
Authorization: Bearer <token>
Body: {}
```

→ Status becomes `submitted`. Admin will review it.
→ Owner gets a **push notification** when admin approves or rejects.

---

### After Rejection — Resubmit

If admin rejects, owner sees the `rejection_reason` in their dashboard. They edit the listing and resubmit.

**Edit any field:**
```
PATCH /listings/{listing_id}
Authorization: Bearer <token>
Body: { "price": 28000, "description": "Updated description..." }
```
(Send only the fields you want to change)

**Resubmit:**
```
POST /listings/{listing_id}/submit
Authorization: Bearer <token>
Body: {}
```

---

### Mark as Rented

When the flat is rented out, owner marks it:

```
POST /listings/{listing_id}/mark-rented
Authorization: Bearer <token>
Body: {}
```

→ Listing disappears from renter search. Status becomes `rented`.

---

### Delete a Listing

```
DELETE /listings/{listing_id}
Authorization: Bearer <token>
```

---

---

## 3. OWNER DASHBOARD — MY LISTINGS

Owner sees all their listings with status badges.

```
GET /owner/listings?page=1
Authorization: Bearer <token>
```

**Optional filters:**
```
?status=approved     ← draft | submitted | approved | rejected | rented
?type_id=1           ← filter by listing type
?page=2              ← pagination
```

**What you get back:**
```json
{
  "data": [
    {
      "id": "uuid",
      "title": "My Apartment",
      "status": "approved",
      "status_label": "Approved",
      "price": 25000,
      "beds": 3,
      "baths": 2,
      "views": 142,
      "inquiries": 8,
      "rejection_reason": null,
      "photos": [ { "url": "https://...", "position": 1 } ],
      "created_at": "2026-05-01"
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

**Status meanings for the UI:**

| Status | Badge Color | What to show owner |
|---|---|---|
| `draft` | Gray | "Not submitted yet" + Edit + Submit button |
| `submitted` | Yellow | "Under Review" — waiting for admin |
| `approved` | Green | "Live" — visible to renters |
| `rejected` | Red | Show `rejection_reason` + Edit + Resubmit button |
| `rented` | Blue | "Rented Out" |

---

---

## 4. RENTER — HOW TO BROWSE AND FIND FLATS

### How the Discovery Page Works

When the renter opens the app:

1. Load listing type chips → `GET /listing-types`
2. Get renter's location (GPS) → send it to backend `PATCH /user/location`
3. Load listings → `GET /listings`

---

### Browse All Listings (no filters)

```
GET /listings
Authorization: Bearer <token>
```

**What you get back (array of listings):**
```json
[
  {
    "id": "uuid",
    "title": "3 Bed Apartment in Mirpur",
    "area": "Mirpur-10",
    "price": 25000,
    "deposit": 50000,
    "beds": 3,
    "baths": 2,
    "size": 1200,
    "floor_no": 4,
    "available_from": null,
    "type": "apartment",
    "status": "approved",
    "views": 142,
    "coord_y": 23.8103,
    "coord_x": 90.4125,
    "amenities": [ { "id": 1, "name": "wifi", "label": "WiFi" } ],
    "photos": [ { "id": "p1", "url": "https://...", "position": 1 } ],
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

> **Thumbnail photo:** use the photo with the lowest `position` number
> **"Available Now":** if `available_from` is `null`, the flat is immediately available
> **Image URLs:** if starts with `/` → prepend `https://flatnest.techrealify.com`

---

### Get Listing Detail (full info)

When renter taps a listing card:

```
GET /listings/{listing_id}
Authorization: Bearer <token>
```

Same shape as above but with all photos, full amenities list, and owner contact details.

---

---

## 5. RENTER — HOW FILTERING WORKS

All filters are **query parameters** on `GET /listings`. Combine as many as needed.

### Filter by Type (Apartment / Bachelor / Sublet chips)

```
GET /listings?listing_type_id=1
```

Get type IDs from `GET /listing-types`

---

### Search by Text

```
GET /listings?search=mirpur+3+bed
```

Searches title and area name. Use debounce (wait 500ms after user stops typing).

---

### Filter by Price

```
GET /listings?price_min=10000&price_max=30000
```

---

### Filter by Beds / Baths

```
GET /listings?beds=2&baths=1
```

---

### Filter by Amenities

```
GET /listings?amenities=1,2,4
```

Comma-separated amenity IDs from `GET /amenities`

---

### Filter by Location (Geo)

```
GET /listings?division_id=1&district_id=21&upazila_id=341
```

Cascade the geo dropdowns exactly like the owner wizard (see Section 2, Step 3).

---

### Filter by Floor Range

```
GET /listings?floor_min=2&floor_max=6
```

---

### Filter by Size (sq ft)

```
GET /listings?size_min=800&size_max=1500
```

---

### Filter by Facing Direction

```
GET /listings?facing_id=1
```

Get IDs from `GET /meta/listing-facings`

---

### Filter by Availability Date Range

```
GET /listings?available_from_start=2026-07-01&available_from_end=2026-09-01
```

---

### Sort Results

```
GET /listings?sort_by=price_asc
```

Options: `price_asc` | `price_desc` | `newest` | `oldest`

---

### Full Example — Combined Filters

3-bed apartment in Dhaka, BDT 15,000–30,000, with WiFi, sorted cheapest first:

```
GET /listings?listing_type_id=1&beds=3&price_min=15000&price_max=30000&division_id=1&amenities=1&sort_by=price_asc
```

---

---

## 6. RENTER — MAP VIEW (NEARBY LISTINGS)

When the renter opens the map tab, get their GPS and find nearby listings.

```
GET /listings/nearby?coord_x=90.4125&coord_y=23.8103&radius=5
Authorization: Bearer <token>
```

| Param | What it is |
|---|---|
| `coord_x` | User's **longitude** |
| `coord_y` | User's **latitude** |
| `radius` | Search radius in km (default 5) |

Also supports: `listing_type_id`, `price_min`, `price_max`, `beds`, `baths`

**Each listing in the response includes:**
```json
{ "distance_km": 1.24 }
```

Use `coord_x` and `coord_y` from the listing to place the pin on the map.

---

---

## 7. RENTER — WISHLIST (SAVED LISTINGS)

### Save or Unsave a Listing

Renter taps the heart/bookmark icon on any listing:

```
POST /wishlist/{listing_id}/toggle
Authorization: Bearer <token>
```

**Response:**
```json
{ "data": { "saved": true } }
```

`true` = now saved, `false` = now removed. Use **optimistic UI** — flip the icon immediately, confirm/revert after API responds.

---

### View Saved Listings

```
GET /wishlist
Authorization: Bearer <token>
```

Returns same listing shape as browse. Show these on the Saved / Wishlist tab.

---

---

## 8. CHAT / MESSAGING

### How it works

Only **renters start chats**. They go to a listing detail and tap "Contact Owner". Owners reply. Both can send messages after that.

---

### Renter starts a chat

```
POST /chats
Authorization: Bearer <token>
Body: {
  "listing_id": "listing-uuid",
  "initial_message": "Is this still available?"
}
```

> If a chat already exists between this renter and this listing, the API returns the existing one.

---

### Get all conversations

```
GET /chats
Authorization: Bearer <token>
```

**Response:**
```json
[
  {
    "id": "chat-uuid",
    "listing": { "id": "...", "title": "3 Bed Apartment", "area": "Mirpur-10" },
    "other_user": { "id": "...", "name": "John Doe", "avatar_url": null },
    "last_message": {
      "text": "Is this still available?",
      "is_read": false,
      "created_at": "2026-05-24T10:30:00Z"
    },
    "unread_count": 2,
    "updated_at": "2026-05-24T10:30:00Z"
  }
]
```

`other_user` = the person on the other side (owner sees renter here, renter sees owner).

---

### Get messages in a conversation

```
GET /chats/{chat_id}/messages
Authorization: Bearer <token>
```

---

### Send a message

```
POST /chats/{chat_id}/messages
Authorization: Bearer <token>
Body: { "text": "Yes, available from July 1st." }
```

---

---

## 9. NOTIFICATIONS

### How it works

Push notifications arrive via Firebase (FCM). The app/web also polls for unread count to show a badge on the bell icon.

---

### Register device for push notifications

Call this immediately after login (also after Google sign-in):

```
POST /device/fcm-token
Authorization: Bearer <token>
Body: {
  "fcm_token": "firebase_token_here",
  "device_type": "web",
  "device_model": "Chrome/Windows"
}
```

For web: use Firebase Web SDK to get the token, set `device_type` to `"web"`.

---

### Get unread count (for bell badge)

```
GET /notifications/unread-count
Authorization: Bearer <token>
```

Returns: `{ "data": { "unread_count": 5 } }`

---

### Get notification list (paginated)

```
GET /notifications?page=1
Authorization: Bearer <token>
```

**Response:**
```json
{
  "data": [
    {
      "id": "notif-uuid",
      "kind": "listing_approved",
      "title": "Listing Approved!",
      "body": "Your listing has been approved and is now live.",
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

**Notification types and what to do when tapped:**

| `kind` | Who gets it | Navigate to |
|---|---|---|
| `listing_approved` | Owner | Owner Dashboard → their listings |
| `listing_rejected` | Owner | Owner Dashboard → show rejection reason |
| `listing_review` | Owner | Owner Dashboard → edit listing |
| `listing_submitted` | Admin | (admin panel) |
| `new_message` | Both | Chat conversation (`reference_id` = chat_id) |

---

### Mark one as read

```
PATCH /notifications/{notification_id}/read
Authorization: Bearer <token>
```

---

### Mark all as read

```
PATCH /notifications/read-all
Authorization: Bearer <token>
```

---

---

## 10. ALL REFERENCE DATA ENDPOINTS

Load these once on app start or when the relevant form opens. Cache them locally.

| What | Endpoint | Use for |
|---|---|---|
| Listing types | `GET /listing-types` | Type chips, create listing dropdown |
| Amenities | `GET /amenities` | Filter sheet, create listing checkboxes |
| Facing directions | `GET /meta/listing-facings` | Create/edit listing dropdown |
| Divisions | `GET /geo/divisions` | Location filter + create listing |
| Districts | `GET /geo/districts/{division_id}` | After division selected |
| Upazilas | `GET /geo/upazilas/{district_id}` | After district selected |
| Unions | `GET /geo/unions/{upazila_id}` | After upazila selected |

---

---

## QUICK ENDPOINT CHEAT SHEET

```
── AUTH ──────────────────────────────────────────────────────────
POST   /auth/register              Register new account (step 1)
POST   /auth/google                Google sign-in
POST   /auth/login                 Email/password login
POST   /auth/refresh               Refresh access token (silent)
POST   /auth/logout                Logout
DELETE /auth/account               Delete account
PATCH  /auth/register/details      Set role: owner/renter (step 2)
PATCH  /auth/register/avatar       Upload profile photo (step 3)

── REFERENCE DATA ────────────────────────────────────────────────
GET    /listing-types              Apartment / Bachelor / Sublet etc.
GET    /meta/listing-facings       North / South / East / West etc.
GET    /amenities                  WiFi / Parking / Gym etc.
GET    /geo/divisions              All divisions
GET    /geo/districts/{id}         Districts under a division
GET    /geo/upazilas/{id}          Upazilas under a district
GET    /geo/unions/{id}            Unions under an upazila

── LISTINGS (RENTER) ─────────────────────────────────────────────
GET    /listings                   Browse all (with filter params)
GET    /listings/nearby            Map view (coord_x, coord_y, radius)
GET    /listings/{id}              Full listing detail

── WISHLIST (RENTER) ─────────────────────────────────────────────
GET    /wishlist                   Saved listings
POST   /wishlist/{id}/toggle       Save / unsave a listing

── CREATE LISTING (OWNER WIZARD) ────────────────────────────────
POST   /listings                   Step 1: Create draft → get ID
POST   /listings/{id}/photos       Step 2: Upload photos
PATCH  /listings/{id}/location     Step 3: Location + GPS
PATCH  /listings/{id}/owner-info   Step 4: Owner contact info
POST   /listings/{id}/submit       Step 5: Submit for review

── OWNER LISTING MANAGEMENT ─────────────────────────────────────
GET    /owner/listings             My listings (paginated, filterable)
PATCH  /listings/{id}              Edit any listing field
POST   /listings/{id}/submit       Resubmit after rejection
POST   /listings/{id}/mark-rented  Mark as rented
DELETE /listings/{id}              Delete listing

── CHAT ──────────────────────────────────────────────────────────
GET    /chats                      All conversations
POST   /chats                      Start chat (renter → owner)
GET    /chats/{id}/messages        Message history
POST   /chats/{id}/messages        Send a message

── NOTIFICATIONS ─────────────────────────────────────────────────
GET    /notifications              Paginated list (?page=1)
GET    /notifications/unread-count Badge count
PATCH  /notifications/{id}/read    Mark one read
PATCH  /notifications/read-all     Mark all read
POST   /device/fcm-token           Register push token after login

── USER ──────────────────────────────────────────────────────────
PATCH  /user/location              Send GPS coordinates
```
