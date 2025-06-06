# RealtyDemo REST API Documentation (v1)

This document provides details on how to interact with the custom REST API (Version 1) for managing Real Estate Properties (`real_estate` post type) in the RealtyDemo project, as defined in the `strongback-realestate` plugin.

## Base URL

All API endpoints described here are relative to the `strongback/v1` namespace within the WordPress REST API base URL.

- **Deployed (Render Example):** `https://realtydemo.onrender.com/wp-json/strongback/v1`
- **Local (Example):** `http://localhost:8080/wp-json/strongback/v1` (Adjust port based on your `.env` configuration)

## Authentication

- **Public Endpoints:**
  - `GET /objects` (List Properties)
  - `GET /objects/{id}` (Retrieve Single Property)
  - These endpoints **do not require authentication** as their `permission_callback` is `__return_true`.
- **Protected Endpoints:**
  - `POST /objects` (Create Property) - Requires user capability: `edit_posts`.
  - `PUT /objects/{id}` (Update Property) - Requires user capability: `edit_posts`.
  - `DELETE /objects/{id}` (Delete Property) - Requires user capability: `delete_posts`.
  - These endpoints **require authentication**. For testing, **Basic Authentication** should be used with the following credentials:
    - **Username:** `Guest User`
    - **Password:** `mjcwn58nuHX2WLSfm6Xru0cn`

## Data Format

- Request bodies for `POST` and `PUT` methods **must** be sent as `application/json`.
- All successful responses will be returned as `application/json`.

## Endpoints

---

### 1. List Properties

Retrieves a list of all published `real_estate` properties.

**⚠️ Important Note:** Based on the current `sb_list_objects` function code, this endpoint retrieves **ALL** properties (`posts_per_page: -1`) and **does not currently support filtering or pagination parameters** (like `paged`, `per_page`, `search`, or custom filters) via query strings. Any such parameters sent will be ignored by this specific implementation.

- **Method:** `GET`
- **URL:** `/objects`
- **Authentication:** None required.
- **Success Response (200 OK):** An array of property objects.
  ```json
  [
    {
      "id": 123,
      "title": "Sample Property One",
      "content": "<p>Description...</p>",
      "district": ["Downtown"], // Array of term names
      "house_name": "Sample Property One",
      "location_coords": "50.4501, 30.5234",
      "floors_count": "10",
      "building_type": "brick",
      "ecological_rating": "4",
      "images": [
        /* ACF Image field data (array of IDs, URLs, or objects) */
      ],
      "rooms": [
        /* ACF Repeater field data (array of room objects) */
      ]
    },
    {
      "id": 124,
      "title": "Sample Property Two"
      // ... other fields
    }
    // ... more property objects
  ]
  ```

---

### 2. Retrieve Single Property

Gets the details for a specific property by its WordPress Post ID.

- **Method:** `GET`
- **URL:** `/objects/{id}` (e.g., `/objects/123`)
- **Authentication:** None required.
- **Path Parameter:**
  - `id` (integer, required): The ID of the property post.
- **Success Response (200 OK):** A single property object (same structure as in the list response).
  ```json
  {
    "id": 123,
    "title": "Sample Property One",
    "content": "<p>Description...</p>",
    "district": ["Downtown"],
    "house_name": "Sample Property One"
    // ... other fields
  }
  ```
- **Error Response:**
  - `404 Not Found`: If no `real_estate` post with the given ID exists.
  ```json
  {
    "code": "not_found",
    "message": "Object not found",
    "data": { "status": 404 }
  }
  ```

---

### 3. Create Property

Creates a new `real_estate` property post.

- **Method:** `POST`
- **URL:** `/objects`
- **Authentication:** **Required** (Basic Auth with `Guest User` / `mjcwn58nuHX2WLSfm6Xru0cn`). User must have `edit_posts` capability.
- **Headers:**
  - `Content-Type: application/json`
- **Request Body (JSON):** Must include `title` and `content`. ACF fields and `district` are optional but recommended.
  ```json
  {
    "title": "New Building From API",
    "content": "This is the description of the new building created via the API.",
    "district": "Uptown", // District name (string) - assumes single term assignment
    "house_name": "API House",
    "location_coords": "50.5000, 30.6000",
    "floors_count": "8",
    "building_type": "foam block",
    "ecological_rating": "5",
    "images": [
      /* Array of existing Attachment IDs, e.g., [25, 30] */
    ],
    "rooms": [
      {
        "room_area": "65",
        "room_count": "3",
        "has_balcony": "true",
        "has_bathroom": "true",
        "room_images": [
          /* Array of Attachment IDs */
        ]
      },
      {
        "room_area": "30",
        "room_count": "1",
        "has_balcony": "false",
        "has_bathroom": "true",
        "room_images": []
      }
    ]
  }
  ```
- **Success Response (200 OK - _Note: WP often returns 200, not 201 on core functions_):** The newly created property object (same structure as GET response).
- **Error Responses:**
  - `400 Bad Request`: If required fields (`title`, `content` based on route args) are missing or data is invalid (though the PHP code handles sanitization). Could also be triggered by `wp_insert_post` errors.
  - `401 Unauthorized`: Authentication failed.
  - `403 Forbidden`: Authenticated user lacks `edit_posts` capability.

---

### 4. Update Property

Updates an existing `real_estate` property.

- **Method:** `PUT` (Though the route registration uses `EDITABLE`, `PUT` is the standard REST verb implied for full/partial updates here).
- **URL:** `/objects/{id}` (e.g., `/objects/123`)
- **Authentication:** **Required** (Basic Auth with `Guest User` / `mjcwn58nuHX2WLSfm6Xru0cn`). User must have `edit_posts` capability.
- **Path Parameter:**
  - `id` (integer, required): The ID of the property to update.
- **Headers:**
  - `Content-Type: application/json`
- **Request Body (JSON):** Include _only_ the fields you want to change. Any fields omitted will remain unchanged.
  ```json
  {
    "title": "Updated Building Name",
    "acf": {
      "ecological_rating": "2", // Update only this ACF field
      "rooms": [
        // Replace the entire rooms repeater
        {
          "room_area": "70",
          "room_count": "3",
          "has_balcony": "true",
          "has_bathroom": "true",
          "room_images": []
        }
      ]
    }
  }
  ```
  _Note:_ The PHP code explicitly checks for `title`, `content`, `district` and specific ACF fields (`house_name`, `location_coords`, etc.) within the root level or nested `acf` level of the params _if_ using `get_json_params()`. The current code structure suggests sending ACF fields at the root level alongside `title`/`content`. **Verify actual behavior.** For safety, let's assume root level based on the loop in `sb_update_object`:
  ```json
  {
    "title": "Updated Building Name",
    "ecological_rating": "2", // Update ACF field directly
    "rooms": [
      /* New rooms array */
    ]
  }
  ```
- **Success Response (200 OK):** The updated property object.
- **Error Responses:**
  - `400 Bad Request`: Invalid data.
  - `401 Unauthorized`: Authentication failed.
  - `403 Forbidden`: User lacks `edit_posts` capability.
  - `404 Not Found`: Property with the specified ID doesn't exist or isn't a `real_estate` post type.

---

### 5. Delete Property

Permanently deletes a specific `real_estate` property.

**⚠️ Important Note:** The code uses `wp_delete_post($id, true)`, which **permanently deletes** the post, bypassing the trash.

- **Method:** `DELETE`
- **URL:** `/objects/{id}` (e.g., `/objects/123`)
- **Authentication:** **Required** (Basic Auth with `Guest User` / `mjcwn58nuHX2WLSfm6Xru0cn`). User must have `delete_posts` capability.
- **Path Parameter:**
  - `id` (integer, required): The ID of the property to delete.
- **Success Response (200 OK):**
  ```json
  {
    "deleted": true
  }
  ```
- **Error Responses:**
  - `401 Unauthorized`: Authentication failed.
  - `403 Forbidden`: User lacks `delete_posts` capability.
  - `404 Not Found`: Property with the specified ID doesn't exist or isn't a `real_estate` post type.

---

## Testing with Postman

1.  **Set Base URL (Optional but Recommended):** Create an Environment in Postman and set a variable like `baseUrl` to `https://realtydemo.onrender.com/wp-json/strongback/v1` (or your local URL).
2.  **Create Requests:**
    - **GET /objects:**
      - Method: `GET`
      - URL: `{{baseUrl}}/objects`
      - No Authorization needed.
      - Click "Send".
    - **GET /objects/{id}:**
      - Method: `GET`
      - URL: `{{baseUrl}}/objects/123` (Replace `123` with a valid ID)
      - No Authorization needed.
      - Click "Send".
    - **POST /objects:**
      - Method: `POST`
      - URL: `{{baseUrl}}/objects`
      - **Authorization Tab:**
        - Type: `Basic Auth`
        - Username: `Guest User`
        - Password: `mjcwn58nuHX2WLSfm6Xru0cn`
      - **Body Tab:**
        - Select `raw`.
        - Select `JSON` from the dropdown.
        - Paste the example JSON request body (from Endpoint 3 above) into the text area.
      - Click "Send".
    - **PUT /objects/{id}:**
      - Method: `PUT`
      - URL: `{{baseUrl}}/objects/123` (Replace `123` with the ID of the post you want to update)
      - **Authorization Tab:** Set Basic Auth as above.
      - **Body Tab:** Select `raw`, `JSON`, and paste the example JSON request body containing _only the fields to update_ (from Endpoint 4 above).
      - Click "Send".
    - **DELETE /objects/{id}:**
      - Method: `DELETE`
      - URL: `{{baseUrl}}/objects/123` (Replace `123` with the ID to delete)
      - **Authorization Tab:** Set Basic Auth as above.
      - Click "Send".
3.  **Check Results:** Observe the Status Code (e.g., 200 OK, 404 Not Found) and the Response Body for each request.

## Testing with `curl`

_(Replace `http://localhost:8080` with your actual WordPress URL https://realtydemo.onrender.com)_

- **List Objects (GET):**

  ```bash
  curl http://localhost:8080/wp-json/strongback/v1/objects
  ```

- **Get Single Object (GET):**

  ```bash
  curl http://localhost:8080/wp-json/strongback/v1/objects/123
  ```

- **Create Object (POST):**

  ```bash
  curl -X POST http://localhost:8080/wp-json/strongback/v1/objects \
  -u "Guest User:mjcwn58nuHX2WLSfm6Xru0cn" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "New Building via Curl",
    "content": "Curl request content.",
    "district": "West End",
    "house_name": "Curl House",
    "ecological_rating": "3"
  }' \
  -i # Optional: -i shows headers
  ```

- **Update Object (PUT):**

  ```bash
  curl -X PUT http://localhost:8080/wp-json/strongback/v1/objects/123 \
  -u "Guest User:mjcwn58nuHX2WLSfm6Xru0cn" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Updated content via curl.",
    "ecological_rating": "4"
  }' \
  -i
  ```

- **Delete Object (DELETE):**
  ```bash
  curl -X DELETE http://localhost:8080/wp-json/strongback/v1/objects/123 \
  -u "Guest User:mjcwn58nuHX2WLSfm6Xru0cn" \
  -i
  ```

---
