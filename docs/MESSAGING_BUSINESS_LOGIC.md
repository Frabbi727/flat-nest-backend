# FlatNest — Messaging Request Flow Implementation

This document outlines the business logic for the new chat request feature, where renters must request to chat, and owners must accept before open communication begins.

## 1. Database Changes

You will need to create a new migration to update the `chats` table:

```bash
php artisan make:migration add_status_to_chats_table
```

In the migration, add an `enum` column for the chat status:

```php
Schema::table('chats', function (Blueprint $table) {
    $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending')->after('listing_id');
});
```

## 2. The New Flow

### Step 1: Renter Sends a Chat Request

- **Action:** A renter views a listing and clicks "Contact Owner". They send an initial message.
- **Endpoint:** `POST /api/v1/chats` (Existing)
- **Logic Updates (in `ChatService@startChat`):**
  - Create the chat with `status` = `pending`.
  - Save the initial message.
  - Dispatch a push notification to the **Owner** with kind `chat_request`. Title: "New Chat Request", Body: "[Renter Name] wants to chat about [Listing Title]".

### Step 2: Owner Sees the Request

- **Action:** The owner receives the push notification or checks their inbox.
- **Endpoint:** `GET /api/v1/chats` (Existing)
- **Logic Updates (in `ChatResource`):**
  - Ensure the `status` field is returned in the API response so the frontend knows if a chat is pending, accepted, or rejected.
  - Owners will see the chat marked as `pending`. They can see the initial message but cannot reply yet.

### Step 3: Owner Accepts or Rejects

We need two new endpoints in `routes/api.php` under the `auth:sanctum` group (ideally protected by owner middleware or ensuring only the owner of the listing can call them).

```php
Route::post('/chats/{id}/accept', [ChatController::class, 'acceptRequest']);
Route::post('/chats/{id}/reject', [ChatController::class, 'rejectRequest']);
```

#### A. Accepting the Request

- **Endpoint:** `POST /api/v1/chats/{id}/accept`
- **Logic (in `ChatService@acceptChat`):**
  - Verify the user making the request is the `owner_id` of the chat.
  - Update chat `status` to `accepted`.
  - Dispatch a push notification to the **Renter** with kind `chat_accepted`. Title: "Chat Request Accepted", Body: "[Owner Name] accepted your request. You can now chat."

#### B. Rejecting the Request

- **Endpoint:** `POST /api/v1/chats/{id}/reject`
- **Logic (in `ChatService@rejectChat`):**
  - Verify the user making the request is the `owner_id` of the chat.
  - Update chat `status` to `rejected`.
  - Dispatch a push notification to the **Renter** with kind `chat_rejected`. Title: "Chat Request Declined", Body: "[Owner Name] declined your request."

### Step 4: Open Messaging (or Blocked)

- **Action:** Either user tries to send a subsequent message.
- **Endpoint:** `POST /api/v1/chats/{id}/messages` (Existing)
- **Logic Updates (in `ChatService@sendMessage`):**
  - **Crucial Check:** Before allowing the message to be saved, verify the chat's `status`.
  - If `status !== 'accepted'`, throw a `403 Forbidden` exception (e.g., "This chat request has not been accepted yet.").

## 3. Required API Changes Summary

1.  **Migration:** Add `status` column to `chats` table.
2.  **Routes:** Add `/chats/{id}/accept` and `/chats/{id}/reject`.
3.  **Controllers:** Add `acceptRequest` and `rejectRequest` methods to `ChatController`.
4.  **Services (`ChatService`):**
    *   Update `startChat` to set status to `pending` and send specific `chat_request` notification.
    *   Create `acceptChat` method (updates status, notifies renter).
    *   Create `rejectChat` method (updates status, notifies renter).
    *   Update `sendMessage` to block messages if status is not `accepted`.
5.  **Resources (`ChatResource`):** Expose the `status` attribute.

## 4. Frontend Implementation Notes

- **Renter UI:** When viewing a pending chat, show a banner: "Waiting for owner to accept your request." Hide the message input field.
- **Owner UI:** When viewing a pending chat, hide the message input field. Show two prominent buttons: "Accept Request" and "Reject Request".
- **Rejected UI (Both):** If status is `rejected`, show a banner: "This chat request was declined." Hide the message input field completely.
