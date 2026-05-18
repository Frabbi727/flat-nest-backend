# Mobile Auth Integration Guide

**Base URL:** `https://your-api-domain.com/api/v1`

All requests and responses use JSON. All protected endpoints require a Bearer token in the `Authorization` header.

---

## Response Envelope

Every response has the same wrapper:

```json
{
  "success": true | false,
  "data":    { ... } | null,
  "message": "Human readable string" | null,
  "errors":  { ... } | null,
  "code":    "ERROR_CODE" | null
}
```

- On success: `success = true`, payload is in `data`
- On error: `success = false`, reason is in `message`, machine-readable code is in `code`
- Validation errors: `success = false`, field errors are in `errors` (422 status)

---

## Token Management

| Token | Where | Lifetime | Purpose |
|---|---|---|---|
| `access_token` | `Authorization: Bearer <token>` | Until revoked | All protected API calls |
| `refresh_token` | Store securely (Keychain / Keystore) | 30 days | Get a new `access_token` |

- Store both tokens securely after login/register/Google sign-in
- When an API call returns `401`, use the refresh endpoint to get a new `access_token`
- If refresh also returns `401`, the session has expired — send user back to login screen

---

## Registration Flow

Registration is **3 steps**. After step 1 (or Google Sign-In), you get tokens. Steps 2 and 3 are protected and use those tokens.

```
Step 1 → POST /auth/register   (or POST /auth/google)
Step 2 → PATCH /auth/register/details
Step 3 → PATCH /auth/register/avatar
```

Use `registration_step` in the response to know where to navigate:

| `registration_step` | Navigate to |
|---|---|
| `2` | Role selection screen |
| `3` | Avatar upload screen |

After step 3 completes, the user's `is_complete` becomes `true` — navigate to the home screen.

---

## Endpoints

---

### GET /meta/roles

Fetch the list of available roles to display on the role selection screen. Call this once and cache it.

**Auth:** None

**Response 200:**
```json
{
  "success": true,
  "data": [
    { "value": "renter", "label": "Renter" },
    { "value": "owner",  "label": "Owner"  }
  ],
  "message": null,
  "errors": null
}
```

Use `value` when sending the role to the backend. Use `label` for display.

---

### POST /auth/register

Step 1 of registration using email and password.

**Auth:** None

**Request:**
```json
{
  "name":     "John Doe",
  "email":    "john@example.com",
  "password": "secret123",
  "phone":    "01712345678"
}
```

| Field | Rules |
|---|---|
| `name` | Required, max 100 characters |
| `email` | Required, valid email, not already registered |
| `password` | Required, min 8 characters |
| `phone` | Required, Bangladeshi format: `01[3-9]XXXXXXXX` (11 digits), not already registered |

**Response 201:**
```json
{
  "success": true,
  "data": {
    "access_token":      "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "refresh_token":     "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
    "user": {
      "id":           "uuid",
      "name":         "John Doe",
      "email":        "john@example.com",
      "phone":        "01712345678",
      "role":         "renter",
      "date_of_birth": null,
      "avatar_url":   null,
      "is_complete":  false
    },
    "registration_step": 2
  }
}
```

→ Save tokens, navigate to **Role Selection** screen.

**Error 422 — Validation:**
```json
{
  "success": false,
  "message": "An account with this email already exists. Please log in, or use Google Sign-In if you registered with Google.",
  "errors": {
    "email": ["An account with this email already exists. Please log in, or use Google Sign-In if you registered with Google."]
  }
}
```

---

### POST /auth/google

Sign in or register using a Google ID token. Works for both new and returning users.

**Auth:** None

**Request:**
```json
{
  "id_token": "<Google ID token from Google Sign-In SDK>"
}
```

> Get this token by calling Google Sign-In on the device. Use your **Web Client ID** (`638776596608-n6qd2nk8pu2jmoobko04kdoa18kr3564.apps.googleusercontent.com`) in `requestIdToken()`.

**Response 200:**
```json
{
  "success": true,
  "data": {
    "access_token":      "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "refresh_token":     "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
    "user": {
      "id":           "uuid",
      "name":         "John Doe",
      "email":        "john@gmail.com",
      "phone":        null,
      "role":         "renter",
      "date_of_birth": null,
      "avatar_url":   null,
      "is_complete":  false
    },
    "registration_step": 2
  }
}
```

**Navigation logic:**

| `registration_step` | `is_complete` | Navigate to |
|---|---|---|
| `2` | `false` | Role Selection screen |
| `3` | `true` | Home screen |

**Error 401:**
```json
{
  "success": false,
  "message": "Invalid Google token",
  "code": "INVALID_GOOGLE_TOKEN"
}
```

**What the backend does automatically:**
- New Google user → account created, `registration_step = 2` → go to Role Selection
- Returning Google user → logged in, `registration_step = 2 or 3` based on `is_complete`
- Existing email/password user with same email → Google ID is silently linked to their account, they stay as the same user with the same role, `registration_step` reflects their actual completion state — no action needed from the app

---

### PATCH /auth/register/details

Step 2 of registration. Set the user's role.

**Auth:** `Bearer <access_token>` (required)

**Request:**
```json
{
  "role": "renter"
}
```

| Field | Rules |
|---|---|
| `role` | Required, must be a value from `GET /meta/roles` |

**Response 200:**
```json
{
  "success": true,
  "data": {
    "registration_step": 3
  },
  "message": "Details saved"
}
```

→ Navigate to **Avatar Upload** screen.

**Error 409 — Role already locked:**
```json
{
  "success": false,
  "message": "Your account role is already set and cannot be changed.",
  "code": "ROLE_LOCKED"
}
```

> Role is locked permanently after registration is complete (`is_complete = true`). One email = one role, always.

---

### PATCH /auth/register/avatar

Step 3 of registration. Upload a profile photo. Send as `multipart/form-data`.

**Auth:** `Bearer <access_token>` (required)

**Request:** `Content-Type: multipart/form-data`

| Field | Rules |
|---|---|
| `avatar` | Required, image file, formats: jpg / jpeg / png, max 2 MB |

**Response 200:**
```json
{
  "success": true,
  "data": {
    "avatar_url": "https://your-storage-url/avatars/filename.jpg"
  },
  "message": "Registration complete"
}
```

→ Registration complete. Navigate to **Home** screen. The user's `is_complete` is now `true`.

---

### POST /auth/login

Login with email and password.

**Auth:** None

**Request:**
```json
{
  "email":    "john@example.com",
  "password": "secret123"
}
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "access_token":  "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "refresh_token": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
    "user": {
      "id":            "uuid",
      "name":          "John Doe",
      "email":         "john@example.com",
      "phone":         "01712345678",
      "role":          "renter",
      "date_of_birth": null,
      "avatar_url":    "https://...",
      "is_complete":   true
    }
  }
}
```

> Note: `registration_step` is NOT included in login response — only in register/Google responses. Use `is_complete` to determine if the user needs to finish registration.

**Navigation after login:**

| `is_complete` | Navigate to |
|---|---|
| `true` | Home screen |
| `false` | Role Selection screen (step 2) |

**Error 401 — Wrong password:**
```json
{
  "success": false,
  "message": "Invalid credentials",
  "code": "INVALID_CREDENTIALS"
}
```

**Error 401 — Account was created with Google (no password):**
```json
{
  "success": false,
  "message": "This account uses Google Sign-In. Please sign in with Google.",
  "code": "USE_GOOGLE_SIGN_IN"
}
```

When you receive `USE_GOOGLE_SIGN_IN`: hide the password field and highlight the "Sign in with Google" button.

---

### POST /auth/refresh

Get a new `access_token` using the `refresh_token`. Call this when any API returns `401`.

**Auth:** None

**Request:**
```json
{
  "refresh_token": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
}
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "access_token": "1|yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy"
  }
}
```

→ Replace stored `access_token`, retry the original request.

**Error 401:**
```json
{
  "success": false,
  "message": "Invalid or expired refresh token",
  "code": "INVALID_REFRESH_TOKEN"
}
```

→ Refresh token expired (after 30 days). Clear stored tokens, navigate to Login screen.

---

### POST /auth/logout

**Auth:** `Bearer <access_token>` (required)

**Request:** No body

**Response 200:**
```json
{
  "success": true,
  "data": null,
  "message": "Logged out"
}
```

→ Delete both tokens from storage, navigate to Login screen.

---

## Error Code Reference

| Code | HTTP | Meaning | What to do |
|---|---|---|---|
| `INVALID_CREDENTIALS` | 401 | Wrong email or password | Show "Wrong email or password" |
| `USE_GOOGLE_SIGN_IN` | 401 | Account has no password — was registered with Google | Hide password field, show "Sign in with Google" button |
| `INVALID_GOOGLE_TOKEN` | 401 | Google `id_token` was invalid or expired | Ask user to try Google Sign-In again |
| `INVALID_REFRESH_TOKEN` | 401 | Refresh token expired or invalid | Clear tokens, redirect to Login screen |
| `ROLE_LOCKED` | 409 | User tried to change role after registration is complete | Show message, do not retry |
| *(none)* | 422 | Validation failed | Show field errors from `errors` object next to each form field |
| *(none)* | 401 | `access_token` missing or invalid on a protected route | Try refreshing token, then re-login if that fails |

---

## Recommended Token Refresh Flow

```
API call fails with 401
        ↓
POST /auth/refresh with refresh_token
        ↓
Success? → Update stored access_token → Retry original request
        ↓
Fail?   → Clear all tokens → Navigate to Login screen
```

---

## Google Sign-In Setup (Quick Reference)

Use your **Web Client ID** for `requestIdToken()`:
```
638776596608-n6qd2nk8pu2jmoobko04kdoa18kr3564.apps.googleusercontent.com
```

**Android:**
```kotlin
GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN)
    .requestIdToken("638776596608-n6qd2nk8pu2jmoobko04kdoa18kr3564.apps.googleusercontent.com")
    .requestEmail()
    .build()
// account.idToken → send to POST /auth/google
```

**Flutter:**
```dart
GoogleSignIn googleSignIn = GoogleSignIn(scopes: ['email', 'profile']);
final account = await googleSignIn.signIn();
final auth = await account!.authentication;
// auth.idToken → send to POST /auth/google
```

**React Native:**
```js
GoogleSignin.configure({
  webClientId: '638776596608-n6qd2nk8pu2jmoobko04kdoa18kr3564.apps.googleusercontent.com',
});
const userInfo = await GoogleSignin.signIn();
// userInfo.idToken → send to POST /auth/google
```
