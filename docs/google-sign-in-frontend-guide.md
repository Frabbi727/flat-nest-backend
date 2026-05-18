# Google Sign-In — Frontend Integration Guide

## Overview

The backend has a single new endpoint for Google Sign-In. Your mobile app handles the Google popup/flow, gets an `id_token` from Google, and sends it to this endpoint. The backend verifies it, creates or finds the user, and returns your normal auth tokens.

---

## The New Endpoint

```
POST /api/v1/auth/google
```

**No Authorization header needed** (public endpoint).

### Request

```http
POST /api/v1/auth/google
Content-Type: application/json

{
  "id_token": "<Google ID token from Google Sign-In SDK>"
}
```

### Success Response — `200 OK`

```json
{
  "success": true,
  "data": {
    "access_token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "refresh_token": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
    "user": {
      "id": "uuid-here",
      "name": "John Doe",
      "email": "john@gmail.com",
      "phone": null,
      "role": "renter",
      "date_of_birth": null,
      "avatar_url": null,
      "is_complete": false
    },
    "registration_step": 2
  }
}
```

> `registration_step` will be `2` for new/incomplete users, `3` for fully registered users.

### Error Response — `401 Unauthorized`

```json
{
  "success": false,
  "message": "Invalid Google token",
  "error_code": "INVALID_GOOGLE_TOKEN"
}
```

### Validation Error — `422 Unprocessable Entity`

```json
{
  "message": "The id_token field is required.",
  "errors": {
    "id_token": ["The id_token field is required."]
  }
}
```

---

## How to Get the `id_token`

### Android (Kotlin)

```kotlin
// 1. Build the GoogleSignInOptions with your Web Client ID
val gso = GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN)
    .requestIdToken("YOUR_WEB_CLIENT_ID.apps.googleusercontent.com")
    .requestEmail()
    .build()

// 2. Create client and launch sign-in intent
val googleSignInClient = GoogleSignIn.getClient(this, gso)
startActivityForResult(googleSignInClient.signInIntent, RC_SIGN_IN)

// 3. In onActivityResult, get the id_token
override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
    super.onActivityResult(requestCode, resultCode, data)
    if (requestCode == RC_SIGN_IN) {
        val task = GoogleSignIn.getSignedInAccountFromIntent(data)
        val account = task.getResult(ApiException::class.java)
        val idToken = account.idToken  // <-- send this to your backend
        sendToBackend(idToken)
    }
}
```

### Flutter

```dart
// pubspec.yaml: add google_sign_in: ^6.x.x

final GoogleSignIn _googleSignIn = GoogleSignIn(
  scopes: ['email', 'profile'],
);

Future<void> signInWithGoogle() async {
  final account = await _googleSignIn.signIn();
  final auth = await account!.authentication;
  final idToken = auth.idToken; // <-- send this to your backend
  await sendToBackend(idToken);
}

Future<void> sendToBackend(String idToken) async {
  final response = await http.post(
    Uri.parse('https://your-api.com/api/v1/auth/google'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({'id_token': idToken}),
  );
  // handle response...
}
```

### React Native

```javascript
// Install: @react-native-google-signin/google-signin

import { GoogleSignin } from '@react-native-google-signin/google-signin';

GoogleSignin.configure({
  webClientId: 'YOUR_WEB_CLIENT_ID.apps.googleusercontent.com',
});

async function signInWithGoogle() {
  await GoogleSignin.hasPlayServices();
  const userInfo = await GoogleSignin.signIn();
  const idToken = userInfo.idToken; // <-- send this to your backend

  const response = await fetch('https://your-api.com/api/v1/auth/google', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id_token: idToken }),
  });
  const data = await response.json();
  // handle data...
}
```

---

## After Getting the Response — Navigation Logic

```
registration_step == 2  →  Go to "Complete Profile" screen (role + date of birth)
registration_step == 3  →  Go to Home screen (fully registered user)
```

Use `access_token` as a Bearer token for all subsequent API calls:

```
Authorization: Bearer <access_token>
```

Use `refresh_token` to get a new `access_token` when it expires — same as your existing flow (`POST /api/v1/auth/refresh`).

---

## What Happens on the Backend (for your understanding)

1. You send the `id_token` → backend calls Google's API to verify it is real.
2. Backend reads the user's `google_id`, `email`, and `name` from the verified token.
3. **New user**: account is created with name + email. No password, phone is empty.
4. **Existing Google user**: logged in directly.
5. **Existing email/password user (same email)**: Google ID is linked to their account automatically — they can now use either method.
6. Your normal `access_token` + `refresh_token` are issued and returned.

---

## Important: Which Client ID to Use

You need a **Web Client ID** (not Android/iOS client ID) for the `requestIdToken()` call, because the backend verifies against the Web Client ID.

In Google Cloud Console:
- Go to **APIs & Services → Credentials**
- Create an OAuth 2.0 Client ID of type **Web application**
- Copy that client ID → give it to your backend developer for `GOOGLE_CLIENT_ID` in `.env`
- Use the same Web Client ID in `requestIdToken()` / `webClientId` in your app

You can also create Android/iOS client IDs for the native sign-in flow — but the `id_token` you get must be from the **Web client ID**.

---

## Existing Endpoints — Unchanged

All existing endpoints work exactly as before. Google Sign-In is just a new entry point.

| Endpoint | Description |
|---|---|
| `POST /api/v1/auth/register` | Normal email + password registration |
| `POST /api/v1/auth/login` | Normal email + password login |
| `POST /api/v1/auth/google` | **New** — Google Sign-In |
| `PATCH /api/v1/auth/register/details` | Step 2: role + date of birth (unchanged) |
| `PATCH /api/v1/auth/register/avatar` | Step 3: upload avatar (unchanged) |
| `POST /api/v1/auth/logout` | Logout (unchanged) |
| `POST /api/v1/auth/refresh` | Refresh access token (unchanged) |
