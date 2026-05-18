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

## Part 1 — FCM Push Notifications

### Step 1: Register the FCM token after every login

Immediately after a successful login (email/password OR Google Sign-In), call this endpoint with the Firebase FCM token from the device:

```
POST /api/v1/device/fcm-token
Authorization: Bearer <access_token>
Content-Type: application/json
```

**Request body:**
```json
{
  "fcm_token": "<FCM registration token from Firebase>",
  "device_type": "android",
  "device_model": "Samsung Galaxy S24"
}
```

| Field | Type | Required | Values |
|---|---|---|---|
| `fcm_token` | string | Yes | Token from `FirebaseMessaging.instance.getToken()` |
| `device_type` | string | No | `android`, `ios`, `web` |
| `device_model` | string | No | Device name, max 100 chars |

**Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Device registered"
}
```

### Step 2: Re-register when Firebase rotates the token

Firebase can rotate the FCM token at any time. Listen for token refresh and call the same endpoint again:

**Flutter:**
```dart
// On login — register token
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
      'device_type': 'android', // or 'ios'
      'device_model': await getDeviceModel(),
    }),
  );
}

// On token refresh
FirebaseMessaging.instance.onTokenRefresh.listen((newToken) async {
  final accessToken = await storage.read(key: 'access_token');
  if (accessToken != null) {
    await registerFcmToken(accessToken);
  }
});
```

### Step 3: Handle incoming push messages

Push messages arrive with `title` and `body` matching the in-app notification content.

**Flutter — foreground messages:**
```dart
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  final notification = message.notification;
  if (notification != null) {
    showLocalNotification(
      title: notification.title ?? '',
      body: notification.body ?? '',
    );
  }
});
```

**Flutter — background / terminated:**
```dart
FirebaseMessaging.onBackgroundMessage(_backgroundHandler);

@pragma('vm:entry-point')
Future<void> _backgroundHandler(RemoteMessage message) async {
  // System handles display automatically — add custom logic here if needed
}
```

### Step 4: Logout — token is cleared automatically

When the user logs out (`POST /api/v1/auth/logout`), the backend automatically:
- Sets `logged_out_at` on the device session
- Clears the stored FCM token

After logout, **no more push notifications** will be sent to that device. No action needed on the mobile side.

---

## Part 2 — In-App Notification API

All endpoints require authentication: `Authorization: Bearer <access_token>`

---

### List notifications (paginated)

```
GET /api/v1/notifications?page=1
Authorization: Bearer <access_token>
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "kind": "listing_submitted",
      "title": "Listing submitted for review",
      "body": "\"My Apartment\" has been submitted and is awaiting admin review.",
      "time": "2 minutes ago",
      "is_unread": true,
      "reference_id": "listing-uuid"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42,
    "unread_count": 5
  }
}
```

> `unread_count` is included in every list response so you can update the badge in one call.

---

### Get unread count only

Use this to update the notification badge without fetching the full list (e.g., on app resume).

```
GET /api/v1/notifications/unread-count
Authorization: Bearer <access_token>
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "unread_count": 5
  }
}
```

---

### Mark one notification as read

```
PATCH /api/v1/notifications/{id}/read
Authorization: Bearer <access_token>
```

**Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Marked as read"
}
```

---

### Mark all notifications as read

```
PATCH /api/v1/notifications/read-all
Authorization: Bearer <access_token>
```

**Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "All marked as read"
}
```

---

## Part 3 — Notification Kinds

Use the `kind` field to decide what to do when a notification is tapped (navigate to the correct screen). `reference_id` is always the relevant listing UUID.

| `kind` | When it fires | Navigate to |
|---|---|---|
| `listing_submitted` | Owner submits a listing for review | Owner's listing detail screen |
| `listing_approved` | Admin approves the listing | Public listing detail screen |
| `listing_rejected` | Admin rejects the listing | Owner's listing detail screen (show rejection reason) |
| `listing_review` | Owner edits an active listing — it goes back for re-approval | Owner's listing detail screen |

**Flutter — tap handler example:**
```dart
void handleNotificationTap(Map<String, dynamic> notification) {
  final kind = notification['kind'] as String;
  final referenceId = notification['reference_id'] as String?;

  switch (kind) {
    case 'listing_approved':
      Navigator.pushNamed(context, '/listing/$referenceId');
      break;
    case 'listing_submitted':
    case 'listing_rejected':
    case 'listing_review':
      Navigator.pushNamed(context, '/owner/listings/$referenceId');
      break;
  }
}
```

---

## Part 4 — Recommended UI Flow

### Notification bell / badge

```
App launches or resumes
        ↓
GET /notifications/unread-count
        ↓
Show badge if unread_count > 0
```

### Notification screen

```
User opens notification screen
        ↓
GET /notifications?page=1    (unread_count is in meta)
        ↓
Show list, highlight unread items
        ↓
User taps a notification
        ↓
PATCH /notifications/{id}/read
Navigate based on `kind` + `reference_id`
```

### "Mark all as read" button

```
User taps "Mark all read"
        ↓
PATCH /notifications/read-all
        ↓
Refresh list (or set all is_unread = false locally)
Set badge to 0
```

---

## Part 5 — Notification field reference

| Field | Type | Description |
|---|---|---|
| `id` | UUID string | Unique notification ID |
| `kind` | string | One of the kinds listed in Part 3 |
| `title` | string | Short heading for the notification |
| `body` | string | Full message content |
| `time` | string | Human-readable relative time ("2 minutes ago") |
| `is_unread` | boolean | `true` = not yet read by the user |
| `reference_id` | UUID string | ID of the related listing |

---

## Quick Reference — All Endpoints

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/api/v1/device/fcm-token` | Yes | Register / update FCM token |
| `GET` | `/api/v1/notifications` | Yes | List notifications (paginated) |
| `GET` | `/api/v1/notifications/unread-count` | Yes | Get unread badge count |
| `PATCH` | `/api/v1/notifications/read-all` | Yes | Mark all as read |
| `PATCH` | `/api/v1/notifications/{id}/read` | Yes | Mark one as read |
