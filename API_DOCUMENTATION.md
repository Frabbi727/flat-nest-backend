# Flat Nest Complete API Documentation

## Table of Contents
1. [Global Standards](#global-standards)
2. [Public API & Authentication](#public-api--authentication)
3. [Renter API](#renter-api)
4. [Business (Owner) API](#business-owner-api)
5. [Admin API](#admin-api)

---

## Global Standards

*   **Base URL:** `https://your-domain.com/api/v1` (or local equivalent)
*   **Authentication:** Bearer Token via Headers (`Authorization: Bearer <token>`)
*   **Content-Type:** `application/json` (Use `multipart/form-data` for files)

### Standard Response Structure

**Success (20x):**
```json
{
  "success": true,
  "message": "Operation successful.",
  "data": { ... } // Or an array []
}
```

**Error (40x, 50x):**
```json
{
  "success": false,
  "message": "Error description.",
  "code": "ERROR_CODE",
  "errors": {
    "field_name": ["Validation message"]
  }
}
```

---

## Public API & Authentication

These routes do not require any authentication (Token).

### Authentication

*   **Login:** `POST /auth/login`
    *   **Request:** `{ "email": "user@example.com", "password": "password123" }`
    *   **Response:** User object + Access Token
*   **Register:** `POST /auth/register`
    *   **Request:** `{ "name": "Jane Doe", "email": "jane@example.com", "password": "password123", "password_confirmation": "password123" }`
*   **Google Sign-In:** `POST /auth/google`
*   **Refresh Token:** `POST /auth/refresh`

### Public Listings (Browsing without Contact Info)

*   **Browse Listings:** `GET /listings`
    *   *Filters available via query params (type, min_price, max_price, etc.)*

### Public Metadata (Useful for Dropdowns)

*   **Geo Divisions:** `GET /geo/divisions`
*   **Geo Districts:** `GET /geo/districts/{division_id}`
*   **Geo Upazilas:** `GET /geo/upazilas/{district_id}`
*   **Geo Unions:** `GET /geo/unions/{upazila_id}`
*   **Roles:** `GET /meta/roles`
*   **Listing Types:** `GET /meta/listing-types`
*   **Listing Facings:** `GET /meta/listing-facings`
*   **Links:** `GET /meta/links`
*   **Amenities:** `GET /amenities`
*   **Listing Types:** `GET /listing-types`

---

## Renter API
**(Prefix: `/api/v1/renter` | Auth: Required)**

### Account & Auth
*   **Logout:** `POST /auth/logout`
*   **Delete Account:** `DELETE /auth/account`
*   **Update Details:** `PATCH /auth/register/details`
*   **Update Avatar:** `PATCH /auth/register/avatar`

### Listings
*   **Nearby Listings:** `GET /listings/nearby`
*   **Listing Details:** `GET /listings/{id}`
    *   *Note: This authenticated route returns owner contact information.*

### Wishlist
*   **Get Wishlist:** `GET /wishlist`
*   **Toggle Wishlist:** `POST /wishlist/{listing_id}/toggle`

### Chat
*   **Get My Chats:** `GET /chats`
*   **Start Chat:** `POST /chats` (Request body: `listing_id`, `message`)
*   **Get Messages:** `GET /chats/{id}/messages`
*   **Send Message:** `POST /chats/{id}/messages` (Request body: `message`)
*   **Accept Request:** `POST /chats/{id}/accept`
*   **Reject Request:** `POST /chats/{id}/reject`

### Notifications & Devices
*   **Register FCM Token:** `POST /device/fcm-token`
*   **Get Devices/Sessions:** `GET /device/sessions`
*   **Update Location:** `PATCH /user/location`
*   **Get Notifications:** `GET /notifications`
*   **Unread Count:** `GET /notifications/unread-count`
*   **Mark All Read:** `PATCH /notifications/read-all`
*   **Mark Single Read:** `PATCH /notifications/{id}/read`

---

## Business (Owner) API
**(Prefix: `/api/v1/business` | Auth: Required | Role: `owner`)**

### Manage Listings
*   **Get My Listings:** `GET /owner/listings`
*   **Create Listing:** `POST /owner/listings`
*   **Upload Photos:** `POST /owner/listings/{id}/photos` (Requires `multipart/form-data`)
*   **Update Location:** `PATCH /owner/listings/{id}/location`
*   **Submit for Approval:** `POST /owner/listings/{id}/submit`
*   **Mark as Rented:** `POST /owner/listings/{id}/mark-rented`
*   **Update Owner Info on Listing:** `PATCH /owner/listings/{id}/owner-info`
*   **Update Listing Info:** `PATCH /owner/listings/{id}`
*   **Delete Listing:** `DELETE /owner/listings/{id}`

*(Note: Owners also use Renter API routes for Chats, Notifications, and general Account operations).*

---

## Admin API
**(Prefix: `/api/v1/admin` | Auth: Required | Role: `admin`)**

### Dashboard & Users
*   **Dashboard Stats:** `GET /admin/dashboard`
*   **All Users:** `GET /admin/users`
*   **Update User:** `PATCH /admin/users/{id}`
*   **Delete User:** `DELETE /admin/users/{id}`
*   **All Global Sessions:** `GET /admin/sessions`
*   **Specific User Sessions:** `GET /admin/users/{id}/sessions`

### Manage Listings
*   **All Listings:** `GET /admin/listings`
*   **Listing Details:** `GET /admin/listings/{id}`
*   **Approve Listing:** `POST /admin/listings/{id}/approve`
*   **Reject Listing:** `POST /admin/listings/{id}/reject`
*   **Update Listing:** `PATCH /admin/listings/{id}`
*   **Delete Listing:** `DELETE /admin/listings/{id}`

### Manage System Entities
*   **Create Amenity:** `POST /amenities`
*   **Update Amenity:** `PATCH /amenities/{id}`
*   **Delete Amenity:** `DELETE /amenities/{id}`
*   **Create Listing Type:** `POST /listing-types`
*   **Update Listing Type:** `PATCH /listing-types/{id}`
*   **Delete Listing Type:** `DELETE /listing-types/{id}`
