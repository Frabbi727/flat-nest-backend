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
