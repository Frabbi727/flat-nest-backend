# Notifications & Push Notification Integration Guide

**For mobile app developers (Flutter / Android / iOS)**

---

## Overview

The backend has two parallel notification systems:

| System | What it does | How you use it |
|---|---|---|
| **In-app notifications** | Stored in DB, fetched on demand | Call REST API to list / mark read |
| **FCM push notifications** | Delivered in real-time via Firebase | Register device token once after login |

Both fire at the same time for every notification event. You need to implement both.

---

## Part 1 — Setup After Login (Required for Push)

### Step 1: Register FCM token

Immediately after every login (email/password OR Google Sign-In), call:

```
POST /api/v1/device/fcm-token
Authorization: Bearer <access_token>
Content-Type: application/json
```

```json
{
  "fcm_token": "<FCM registration token from Firebase>",
  "device_type": "android",
  "device_model": "Samsung Galaxy S24"
}
```

| Field | Type | Required | Values |
|---|---|---|---|
| `fcm_token` | string | Yes | From `FirebaseMessaging.instance.getToken()` |
| `device_type` | string | No | `android`, `ios`, `web` |
| `device_model` | string | No | Device name, max 100 chars |

**Response (200):**
```json
{ "success": true, "data": null, "message": "Device registered" }
```

### Step 2: Send user location (for renter nearby alerts)

After login, also send the user's GPS coordinates so the backend can notify them when a new listing appears nearby:

```
PATCH /api/v1/user/location
Authorization: Bearer <access_token>
Content-Type: application/json
```

```json
{
  "lat": 23.8103,
  "lng": 90.4125
}
```

| Field | Type | Required | Range |
|---|---|---|---|
| `lat` | number | Yes | -90 to 90 |
| `lng` | number | Yes | -180 to 180 |

**Response (200):**
```json
{ "success": true, "data": null, "message": "Location updated" }
```

> Call this again whenever the user's location changes significantly (e.g. on app resume, or when the device location updates by more than 1km). Only relevant for **renters** — owners won't receive nearby notifications but the call is harmless for them.

### Step 3: Re-register when Firebase rotates the token

```dart
// On login
Future<void> registerFcmToken(String accessToken) async {
  final token = await FirebaseMessaging.instance.getToken();
  if (token == null) return;

  await http.post(
    Uri.parse('$baseUrl/api/v1/device/fcm-token'),
    headers: {
      'Authorization': 'Bearer $accessToken',
      'Content-Type': 'application/json',
    },
    body: jsonEncode({
      'fcm_token': token,
      'device_type': 'android',
      'device_model': await getDeviceModel(),
    }),
  );
}


// When Firebase rotates the token
FirebaseMessaging.instance.onTokenRefresh.listen((newToken) async {
  final accessToken = await storage.read(key: 'access_token');
  if (accessToken != null) await registerFcmToken(accessToken);
});
```

### Step 4: Logout — everything cleared automatically

Calling `POST /api/v1/auth/logout` automatically clears the FCM token and device session on the backend. No push notifications will be sent to that device after logout.

For Google Sign-In users, also call `GoogleSignIn().signOut()` on the client side.

---

## Part 2 — Handling Incoming Push Messages

Push messages arrive with `title` and `body` matching the in-app notification.

**Flutter — foreground:**
```dart
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  final n = message.notification;
  if (n != null) {
    showLocalNotification(title: n.title ?? '', body: n.body ?? '');
  }
});
```

**Flutter — background / terminated:**
```dart
FirebaseMessaging.onBackgroundMessage(_backgroundHandler);

@pragma('vm:entry-point')
Future<void> _backgroundHandler(RemoteMessage message) async {
  // System shows the notification automatically
}
```

**On notification tap — navigate based on kind:**
```dart
void handleNotificationTap(Map<String, dynamic> notification) {
  final kind        = notification['kind'] as String;
  final referenceId = notification['reference_id'] as String?;

  switch (kind) {
    // Owner notifications
    case 'listing_approved':
      Navigator.pushNamed(context, '/listing/$referenceId');
      break;
    case 'listing_submitted':
    case 'listing_rejected':
    case 'listing_review':
      Navigator.pushNamed(context, '/owner/listings/$referenceId');
      break;

    // Renter notifications
    case 'nearby_listing':
      Navigator.pushNamed(context, '/listing/$referenceId');
      break;
    case 'wishlist_listing_rented':
      Navigator.pushNamed(context, '/wishlist');
      break;
  }
}
```

---

## Part 3 — Notification Kinds (Complete List)

`reference_id` is always the relevant listing UUID.

### Owner notifications

| `kind` | When it fires | Navigate to |
|---|---|---|
| `listing_submitted` | Owner submits a listing for review | Owner listing detail |
| `listing_approved` | Admin approves the listing | Public listing detail |
| `listing_rejected` | Admin rejects the listing | Owner listing detail (show rejection reason) |
| `listing_review` | Owner edits an active listing — goes back for re-approval | Owner listing detail |

### Renter notifications

| `kind` | When it fires | Navigate to |
|---|---|---|
| `nearby_listing` | A new listing is approved within 10km of renter's saved location | Public listing detail |
| `wishlist_listing_rented` | An owner marks a wishlisted listing as rented | Wishlist screen |

---

## Part 4 — In-App Notification API

All endpoints require `Authorization: Bearer <access_token>`.

### List notifications (paginated)

```
GET /api/v1/notifications?page=1
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "kind": "nearby_listing",
      "title": "New listing near you!",
      "body": "2BHK Flat in Mirpur is now available nearby.",
      "time": "5 minutes ago",
      "is_unread": true,
      "reference_id": "listing-uuid"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 15,
    "total": 20,
    "unread_count": 3
  }
}
```

> `unread_count` is always in the meta — no need for a separate call when loading the notification screen.

### Get unread count only (for badge)

```
GET /api/v1/notifications/unread-count
```

```json
{ "success": true, "data": { "unread_count": 3 } }
```

### Mark one notification as read

```
PATCH /api/v1/notifications/{id}/read
```

```json
{ "success": true, "data": null, "message": "Marked as read" }
```

### Mark all as read

```
PATCH /api/v1/notifications/read-all
```

```json
{ "success": true, "data": null, "message": "All marked as read" }
```

---

## Part 5 — Recommended UI Flow

### Badge (on app launch / resume)
```
App opens or resumes
    ↓
GET /notifications/unread-count
    ↓
Show badge if unread_count > 0
Also send PATCH /user/location (if renter, with current GPS)
```

### Notification screen
```
User opens notification screen
    ↓
GET /notifications?page=1   → unread_count is in meta
    ↓
Render list, highlight is_unread = true items
    ↓
User taps a notification
    ↓
PATCH /notifications/{id}/read
Navigate based on kind + reference_id
```

### Mark all read button
```
User taps "Mark all read"
    ↓
PATCH /notifications/read-all
    ↓
Set all is_unread = false locally, set badge to 0
```

---

## Part 6 — Notification Field Reference

| Field | Type | Description |
|---|---|---|
| `id` | UUID string | Unique notification ID |
| `kind` | string | See Part 3 for all values |
| `title` | string | Short heading |
| `body` | string | Full message |
| `time` | string | Human-readable relative time ("5 minutes ago") |
| `is_unread` | boolean | `true` = not yet read |
| `reference_id` | UUID string | Related listing ID |

---

## Quick Reference — All Endpoints

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/api/v1/device/fcm-token` | Yes | Register / update FCM token after login |
| `PATCH` | `/api/v1/user/location` | Yes | Update renter's GPS location |
| `GET` | `/api/v1/notifications` | Yes | List notifications (paginated) |
| `GET` | `/api/v1/notifications/unread-count` | Yes | Badge count only |
| `PATCH` | `/api/v1/notifications/read-all` | Yes | Mark all as read |
| `PATCH` | `/api/v1/notifications/{id}/read` | Yes | Mark one as read |
