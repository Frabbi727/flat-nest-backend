# FlatNest Mobile App Integration Guide

This document provides instructions for integrating the FlatNest mobile application with the backend API.

## Base URL

All API endpoints are prefixed with `/api/v1`.

- **Development:** `http://localhost:8000/api/v1`
- **Production:** `https://your-production-domain.com/api/v1`

---

## Authentication

The API uses Sanctum for authentication. All protected endpoints require a `Authorization` header with a bearer token.

`Authorization: Bearer <token>`

### 1. Register

- **Endpoint:** `POST /auth/register`
- **Description:** Creates a new user account.
- **Body:**
  ```json
  {
    "name": "John Doe",
    "email": "john.doe@example.com",
    "password": "password",
    "password_confirmation": "password"
  }
  ```
- **Response:**
  ```json
  {
    "token": "your-auth-token",
    "user": { ... }
  }
  ```

### 2. Google Sign-In

- **Endpoint:** `POST /auth/google`
- **Description:** Authenticates a user with a Google ID token.
- **Body:**
  ```json
  {
    "id_token": "your-google-id-token"
  }
  ```
- **Response:**
  ```json
  {
    "token": "your-auth-token",
    "user": { ... }
  }
  ```

### 3. Login

- **Endpoint:** `POST /auth/login`
- **Description:** Authenticates a user with an email and password.
- **Body:**
  ```json
  {
    "email": "john.doe@example.com",
    "password": "password"
  }
  ```
- **Response:**
  ```json
  {
    "token": "your-auth-token",
    "user": { ... }
  }
  ```

### 4. Refresh Token

- **Endpoint:** `POST /auth/refresh`
- **Description:** Refreshes an expired token.
- **Response:**
  ```json
  {
    "token": "your-new-auth-token"
  }
  ```

### 5. Logout

- **Endpoint:** `POST /auth/logout`
- **Description:** Logs out the current user.
- **Authentication:** Required

---

## Listings

### 1. Get All Listings

- **Endpoint:** `GET /listings`
- **Description:** Retrieves a paginated list of all available listings.
- **Authentication:** Not required for browsing. **Required for filtering.**
- **Query Parameters:**
  - `page` (optional): The page number for pagination.
  - `search` (optional): A search term to filter listings by title or description.
  - `listing_type_id` (optional): The ID of the listing type.
  - `price_min`, `price_max` (optional): The price range.
  - `beds`, `baths` (optional): The number of bedrooms and bathrooms.
  - `facing_id` (optional): The ID of the facing direction.
  - `floor_min`, `floor_max` (optional): The floor range.
  - `size_min`, `size_max` (optional): The size range in square feet.
  - `available_from_start`, `available_from_end` (optional): The availability date range.
  - `amenities` (optional): A comma-separated list of amenity IDs.
  - `division_id`, `district_id`, `upazila_id`, `union_id` (optional): Location-based filtering.
  - `sort_by` (optional): `price_asc`, `price_desc`, `available_soon`.

### 2. Get Listing Details

- **Endpoint:** `GET /listings/{id}`
- **Description:** Retrieves the details of a specific listing.
- **Authentication:** Required.
- **Response:**
  ```json
  {
    "data": {
      "id": 1,
      "title": "...",
      "description": "...",
      // ... other listing details
    }
  }
  ```

---

## Messaging / Chat Feature

The messaging system in FlatNest is built around a **request-to-chat** model. Renters must request to chat, and owners must explicitly accept the request before open communication can begin.

### How the Messaging Flow Works

A chat goes through three possible statuses: `pending`, `accepted`, or `rejected`.

#### 1. Renter Starts a Chat Request (Pending State)
- Only a renter can initiate a chat from a listing detail page.
- When they send the first message, a chat room is created with the status `pending`.
- The owner receives a push notification: "New Chat Request".

#### 2. Owner Receives the Request
- The owner sees the chat in their inbox marked as "Pending".
- At this stage, neither the renter nor the owner can send any more messages.
- The owner can view the renter's initial message.

#### 3. Owner Accepts or Rejects
- **Accept:** If the owner accepts, the chat status changes to `accepted`. The renter gets a push notification, and both parties can now send messages freely.
- **Reject:** If the owner rejects, the chat status changes to `rejected`. The renter gets a notification, and the chat is closed for further communication.

---

### Endpoints for Messaging

#### 1. Get All Chats (Inbox)
- **Endpoint:** `GET /chats`
- **Description:** Returns all chats for the authenticated user (both owner and renter).
- **Important:** The response will include the `status` of each chat (`pending`, `accepted`, or `rejected`). Use this status to design your UI (e.g., show "Waiting for owner" if pending, or "Rejected" if declined).

#### 2. Start a Chat Request
- **Endpoint:** `POST /chats`
- **Description:** Sent by a renter to initiate contact with an owner.
- **Body:**
  ```json
  {
    "listing_id": "uuid-of-the-listing",
    "initial_message": "Hi, is this flat still available?"
  }
  ```
- **Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Chat request sent.",
    "data": {
      "chat_id": "uuid-of-the-chat"
    }
  }
  ```

#### 3. Get Messages in a Chat
- **Endpoint:** `GET /chats/{chat_id}/messages`
- **Description:** Gets the history of messages for a specific chat room.
- **Response:** Note that the chat object returned will include the current `status`.

#### 4. Accept a Chat Request (Owner Only)
- **Endpoint:** `POST /chats/{chat_id}/accept`
- **Description:** Approves a pending chat request.
- **Response:**
  ```json
  {
    "success": true,
    "message": "Chat request accepted.",
    "data": null
  }
  ```

#### 5. Reject a Chat Request (Owner Only)
- **Endpoint:** `POST /chats/{chat_id}/reject`
- **Description:** Declines a pending chat request.
- **Response:**
  ```json
  {
    "success": true,
    "message": "Chat request rejected.",
    "data": null
  }
  ```

#### 6. Send a Message
- **Endpoint:** `POST /chats/{chat_id}/messages`
- **Description:** Sends a new message in an active chat.
- **Important:** This endpoint will throw a `403 Forbidden` error if the chat status is not `accepted`. Do not show the message input box in your UI unless the status is `accepted`.
- **Body:**
  ```json
  {
    "text": "Yes, it is available from next month."
  }
  ```

---

### Mobile UI Implementation Guidelines

- **Renter's View (Pending):** When a renter opens a chat that is `pending`, hide the text input field. Display a message like: "Waiting for the owner to accept your chat request."
- **Owner's View (Pending):** When an owner opens a chat that is `pending`, hide the text input field. Show two prominent buttons: **Accept Request** and **Reject Request**. Display the renter's initial message above the buttons.
- **Renter/Owner View (Rejected):** If a chat is `rejected`, hide the text input field for both users. Display a message like: "This chat request was declined."
- **Renter/Owner View (Accepted):** Show the normal chat interface with the text input field active.
