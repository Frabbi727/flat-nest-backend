# FlatNest — Listing Contact & Location Access Control

This document explains the business logic, API endpoints, request/response formats, and frontend implementation guidance for the **Contact & Location Access Control** feature.

---

## 1. Overview

When a renter browses listings, **sensitive details are hidden** by default. To see the owner's contact information and the exact address of a property, the renter must send an **access request**. The owner then receives a push notification and can either **accept** or **reject** the request.

Only the specific renter whose request was accepted gains access. Other renters viewing the same listing still see no sensitive data.

---

## 2. What Is Hidden vs. What Is Always Visible

### Always visible (no login required)
| Field | Example |
|---|---|
| `title` | "3-Bed Apartment in Gulshan" |
| `price`, `deposit` | 25000, 50000 |
| `beds`, `baths`, `size`, `floor_no` | 3, 2, 1200, 5 |
| `listing_type`, `facing` | Apartment, North |
| `description` | "Spacious flat..." |
| `available_from` | "2026-07-01" |
| `division`, `district`, `upazila`, `union` | Dhaka, Dhaka, Gulshan, — |
| `area` | "Gulshan" |
| `photos`, `amenities` | [...] |
| `status` | active |

### Hidden until access is granted
| Field | Type |
|---|---|
| `owner_name` | Owner's full name |
| `owner_phone` | Primary phone number |
| `owner_alt_phone` | Alternate phone number |
| `owner_email` | Email address |
| `preferred_contact` | `call` / `whatsapp` / `both` |
| `road` | Road number/name |
| `house_name` | House name or number |
| `block` | Block identifier |
| `section` | Section identifier |
| `coord_x` | GPS longitude |
| `coord_y` | GPS latitude |
| `owner` | Owner profile object |

> **Owner exception:** The listing owner always sees all fields on their own listings. No request is needed.

---

## 3. The Complete Business Flow

```
RENTER                              OWNER
  |                                   |
  |  GET /v1/listings/{id}            |
  |  → sees listing, area shown       |
  |  → sensitive fields are absent    |
  |  → access_request_status: null    |
  |                                   |
  |  POST /v1/listings/{id}/          |
  |       request-access              |
  |  -------------------------------->|
  |                                   |  Push notification:
  |                                   |  "New Contact Request"
  |                                   |  "[Name] wants to see
  |                                   |   contact details for
  |                                   |   [Listing Title]"
  |                                   |
  |  GET /v1/listings/{id}            |
  |  → access_request_status: pending |
  |                                   |
  |              (Owner reviews)      |
  |                                   |
  |              POST /v1/owner/      |
  |              access-requests/{id}/|
  |              accept               |
  |  <--------------------------------|
  |                                   |
  |  Push notification:               |
  |  "Request Accepted"               |
  |  "Owner shared contact            |
  |   details for [Listing Title]"    |
  |                                   |
  |  GET /v1/listings/{id}            |
  |  → all sensitive fields visible   |
  |  → access_request_status: accepted|
```

---

## 4. `access_request_status` Field

Every listing detail response (`GET /v1/listings/{id}`) includes an `access_request_status` field. Use this to control what your UI shows.

| Value | Meaning | UI Suggestion |
|---|---|---|
| `null` | User is not logged in, or has never requested | Show "Request Contact Info" button |
| `"pending"` | Request submitted, waiting for owner | Show "Request Pending…" (disabled button) |
| `"accepted"` | Owner approved — sensitive fields are now in the response | Show all contact and location details |
| `"rejected"` | Owner declined | Show "Request Declined. Try again?" button |

---

## 5. API Endpoints

All endpoints require a **Bearer token** in the `Authorization` header:

```
Authorization: Bearer <access_token>
```

---

### 5.1 — Renter: Request Access

Renter sends a request to the owner to unlock contact and location details for a specific listing.

```
POST /api/v1/listings/{listingId}/request-access
```

**Auth:** Required (any authenticated user)

**URL Parameters**

| Parameter | Type | Description |
|---|---|---|
| `listingId` | UUID string | The ID of the listing |

**Request Body:** None

**Success Response — `201 Created`**

```json
{
  "success": true,
  "data": {
    "id": "req_uuid_here",
    "status": "pending"
  },
  "message": "Access request sent to the owner.",
  "errors": null
}
```

**Error Responses**

| HTTP | Message | Reason |
|---|---|---|
| `403` | "You cannot request access to your own listing." | Owner tried to request access to their own listing |
| `404` | "Listing not found." | Invalid listing ID |
| `422` | "You already have a pending request for this listing." | Duplicate pending request |
| `422` | "You already have access to this listing." | Request was already accepted |

---

### 5.2 — Owner: List Access Requests

Owner views all access requests for their listings. Can filter by status.

```
GET /api/v1/owner/access-requests
GET /api/v1/owner/access-requests?status=pending
GET /api/v1/owner/access-requests?status=accepted
GET /api/v1/owner/access-requests?status=rejected
```

**Auth:** Required (owner role only)

**Query Parameters**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `status` | string | No | Filter by status: `pending`, `accepted`, `rejected` |

**Success Response — `200 OK`**

```json
{
  "success": true,
  "data": [
    {
      "id": "req_uuid_here",
      "status": "pending",
      "listing": {
        "id": "listing_uuid_here",
        "title": "3-Bed Apartment in Gulshan"
      },
      "requester": {
        "id": "user_uuid_here",
        "name": "Ahmed Rahman",
        "avatar_url": "https://example.com/storage/avatars/ahmed.jpg"
      },
      "created_at": "2026-06-10T08:30:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  },
  "message": null,
  "errors": null
}
```

---

### 5.3 — Owner: Accept a Request

Owner grants access to a specific renter. After this, that renter can see sensitive fields.

```
POST /api/v1/owner/access-requests/{id}/accept
```

**Auth:** Required (owner role only)

**URL Parameters**

| Parameter | Type | Description |
|---|---|---|
| `id` | UUID string | The access request ID (from the list above) |

**Request Body:** None

**Success Response — `200 OK`**

```json
{
  "success": true,
  "data": {
    "id": "req_uuid_here",
    "status": "accepted"
  },
  "message": "Access granted.",
  "errors": null
}
```

**Error Responses**

| HTTP | Message | Reason |
|---|---|---|
| `403` | "You do not own this listing." | Trying to accept a request for another owner's listing |
| `404` | "Access request not found." | Invalid request ID |
| `422` | "This request has already been responded to." | Already accepted or rejected |

---

### 5.4 — Owner: Reject a Request

Owner denies access to a specific renter.

```
POST /api/v1/owner/access-requests/{id}/reject
```

**Auth:** Required (owner role only)

**URL Parameters**

| Parameter | Type | Description |
|---|---|---|
| `id` | UUID string | The access request ID |

**Request Body:** None

**Success Response — `200 OK`**

```json
{
  "success": true,
  "data": {
    "id": "req_uuid_here",
    "status": "rejected"
  },
  "message": "Access request declined.",
  "errors": null
}
```

**Error Responses** — same as Accept (5.3)

---

### 5.5 — Listing Detail: How the Response Changes

The listing detail endpoint response changes based on the logged-in user's access status.

```
GET /api/v1/listings/{id}
```

**Auth:** Required

---

#### Case A — No request made yet (`access_request_status: null`)

```json
{
  "success": true,
  "data": {
    "id": "listing_uuid",
    "title": "3-Bed Apartment in Gulshan",
    "price": 25000,
    "deposit": 50000,
    "beds": 3,
    "baths": 2,
    "size": 1200,
    "floor_no": 5,
    "area": "Gulshan",
    "division": { "id": 1, "name": "Dhaka" },
    "district": { "id": 5, "name": "Dhaka" },
    "upazila": { "id": 20, "name": "Gulshan" },
    "union": null,
    "photos": [...],
    "amenities": [...],
    "access_request_status": null,
    "status": "active",
    "views": 42,
    "created_at": "2026-06-01T10:00:00.000000Z",
    "updated_at": "2026-06-01T10:00:00.000000Z"
  },
  "message": null,
  "errors": null
}
```

> Notice: `road`, `house_name`, `block`, `section`, `coord_x`, `coord_y`, `owner_name`, `owner_phone`, `owner_alt_phone`, `owner_email`, `preferred_contact`, and `owner` are **not present** in the response.

---

#### Case B — Request is pending (`access_request_status: "pending"`)

Same as Case A, except:

```json
{
  "access_request_status": "pending"
}
```

---

#### Case C — Request was rejected (`access_request_status: "rejected"`)

Same as Case A, except:

```json
{
  "access_request_status": "rejected"
}
```

---

#### Case D — Access granted (`access_request_status: "accepted"`)

All sensitive fields are now included:

```json
{
  "success": true,
  "data": {
    "id": "listing_uuid",
    "title": "3-Bed Apartment in Gulshan",
    "price": 25000,
    "deposit": 50000,
    "beds": 3,
    "baths": 2,
    "size": 1200,
    "floor_no": 5,
    "area": "Gulshan",
    "road": "Road 12",
    "house_name": "Green Villa",
    "block": "B",
    "section": "2",
    "coord_x": 90.4128,
    "coord_y": 23.8118,
    "owner_name": "Karim Hossain",
    "owner_phone": "01711000000",
    "owner_alt_phone": "01811000000",
    "owner_email": "karim@email.com",
    "preferred_contact": "whatsapp",
    "owner": {
      "id": "owner_uuid",
      "name": "Karim Hossain",
      "phone": "01711000000",
      "avatar_url": "https://example.com/storage/avatars/karim.jpg"
    },
    "access_request_status": "accepted",
    "status": "active",
    "views": 43,
    "created_at": "2026-06-01T10:00:00.000000Z",
    "updated_at": "2026-06-01T10:00:00.000000Z"
  },
  "message": null,
  "errors": null
}
```

---

## 6. Push Notifications

All notifications follow the existing notification structure. Listen for `kind` values to handle them in your app.

### 6.1 — Owner receives: New Request

Triggered when a renter submits a request.

| Field | Value |
|---|---|
| `kind` | `contact_info_requested` |
| `title` | "New Contact Request" |
| `body` | "Ahmed Rahman wants to see contact details for \"3-Bed Apartment in Gulshan\"" |
| `reference_id` | The **access request ID** (use to navigate to the requests list) |

### 6.2 — Renter receives: Request Accepted

Triggered when the owner accepts.

| Field | Value |
|---|---|
| `kind` | `contact_info_granted` |
| `title` | "Request Accepted" |
| `body` | "Owner shared contact details for \"3-Bed Apartment in Gulshan\"" |
| `reference_id` | The **listing ID** (use to navigate back to the listing) |

### 6.3 — Renter receives: Request Rejected

Triggered when the owner rejects.

| Field | Value |
|---|---|
| `kind` | `contact_info_denied` |
| `title` | "Request Declined" |
| `body` | "Owner declined your request for \"3-Bed Apartment in Gulshan\"" |
| `reference_id` | The **listing ID** |

---

## 7. Frontend / Mobile Implementation Guide

### For the Renter (listing detail screen)

1. **On screen load**, call `GET /api/v1/listings/{id}` and check `access_request_status`:

| `access_request_status` | What to show |
|---|---|
| `null` | "Request Contact Info" button (active) |
| `"pending"` | "Request Pending…" button (disabled / loading indicator) |
| `"accepted"` | Show the full contact card + map with coordinates |
| `"rejected"` | "Request was declined. Send again?" button |

2. **On button tap** (when status is `null` or `rejected`), call `POST /v1/listings/{id}/request-access`. On success, update the local state to `pending`.

3. **On push notification** with `kind: contact_info_granted`, refresh the listing detail to show the newly unlocked data.

### For the Owner (notifications / requests screen)

1. **On push notification** with `kind: contact_info_requested`, show a badge on the "Requests" section and navigate there on tap (use `reference_id` as the request ID).

2. **Requests list screen**: call `GET /api/v1/owner/access-requests?status=pending` to show pending requests. Each card shows the requester's name, avatar, and the listing they're requesting for.

3. **Accept**: call `POST /api/v1/owner/access-requests/{id}/accept`. Remove the card from the pending list on success.

4. **Reject**: call `POST /api/v1/owner/access-requests/{id}/reject`. Remove the card from the pending list on success.

---

## 8. Re-Requesting After Rejection

If a renter was rejected, they can tap "Send Again" which calls the same endpoint:

```
POST /api/v1/listings/{listingId}/request-access
```

The system automatically resets the previous rejected record back to `pending` and re-notifies the owner. No special handling is needed on the frontend.

---

## 9. Key Rules Summary

- A renter can only have **one active access request per listing** at a time.
- A renter **cannot** request access to a listing they own.
- Access is **individual** — approving one renter does not expose data to others.
- The owner **always** sees full details of their own listings without needing approval.
- Once access is granted, it is **permanent** (does not expire).
- A rejected renter **can re-request** — the previous rejection is overwritten.
- A request that is already `pending` cannot be submitted again until the owner responds.
