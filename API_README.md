# RealtyDemo REST API Documentation (Local Version)

This document describes how to use the custom REST API (Version 1) built into the `real-estate-manager` plugin for managing Real Estate objects (`real_estate` post type) in the **RealtyDemo** project.

---

## 📍 Base URL

All endpoints are located under the following base path:

http://localhost:8080/wp-json/realty/v1


> ℹ️ Port may vary depending on your local Docker or environment configuration.

---

## 🔐 Authentication

### ✅ Public Endpoints

These endpoints do **not require authentication**:

- `GET /objects` — Get all properties (with optional filters)
- `GET /objects/{id}` — Get single property by ID

### 🔒 Protected Endpoints

The following actions **require Basic Authentication** with an account that has proper WordPress capabilities (`edit_posts`, `delete_posts`):

- `POST /objects` — Create new property
- `PUT /objects/{id}` — Update property
- `DELETE /objects/{id}` — Delete property

### Test Credentials (Local Admin)

Username: admin
Password: I1dK)sZEOJu(kq!QZN6SrutF


---

## 📦 Data Format

- All requests must use `Content-Type: application/json`
- All responses are returned as `application/json`
- For fields:
  - ACF fields are passed inside a nested `"acf"` object.
  - Taxonomy terms are passed as arrays of slugs (e.g., `"districts": ["podil"]`)

---

## 🔁 Endpoints

---

### 1. GET `/objects`

Returns a list of all published real estate objects. You can filter by district.

- **Method:** `GET`
- **Authentication:** ❌ Not required
- **Query Parameters:**
  - `district` — Filter by district slug (e.g., `podil`)

#### ✅ Example:

```bash
curl -X GET "http://localhost:8080/wp-json/realty/v1/objects?district=podil"
```
### 2. GET /objects/{id}

Returns a single property by its WordPress post ID.

    Method: GET

    Authentication: ❌ Not required

    Path Param: id (integer)

### 3. POST /objects

Creates a new property with ACF and taxonomy data.

    Method: POST

    Authentication: ✅ Required (admin user)

    Headers:

        Content-Type: application/json

    Body (JSON):
  ```json
    {
  "title": "New Apartment on Podil",
  "content": "Spacious flat with eco materials.",
  "districts": ["podil"],
  "acf": {
    "eco_rating": 5,
    "rooms_0_area": 78,
    "rooms_0_room_count": 3,
    "building_type": "brick",
    "number_of_floors": 5
  }
}
```
```bash
curl -X POST http://localhost:8080/wp-json/realty/v1/objects \
  -u "admin:I1dK)sZEOJu(kq!QZN6SrutF" \
  -H "Content-Type: application/json" \
  -d "{\"title\":\"New Apartment on Podil\",\"content\":\"Spacious flat with eco materials.\",\"districts\":[\"podil\"],\"acf\":{\"eco_rating\":5,\"rooms_0_area\":78,\"rooms_0_room_count\":3,\"building_type\":\"brick\",\"number_of_floors\":5}}"
```
### 4. PUT /objects/{id}

Updates an existing real estate object. Only the fields passed will be updated.

    Method: PUT

    Authentication: ✅ Required

    Path Param: id (integer)

    Headers:

        Content-Type: application/json

    Body (JSON):
  ```json
{
  "title": "Updated Apartment Title",
  "content": "Updated description of the flat.",
  "districts": ["podil"],
  "acf": {
    "eco_rating": 4,
    "rooms_0_area": 82,
    "rooms_0_room_count": 4,
    "building_type": "panel",
    "number_of_floors": 9
  }
}
```
```bash
curl -X PUT http://localhost:8080/wp-json/realty/v1/objects/71 \
  -u "admin:I1dK)sZEOJu(kq!QZN6SrutF" \
  -H "Content-Type: application/json" \
  -d "{\"title\":\"Updated Apartment Title\",\"content\":\"Updated description of the flat.\",\"districts\":[\"podil\"],\"acf\":{\"eco_rating\":4,\"rooms_0_area\":82,\"rooms_0_room_count\":4,\"building_type\":\"panel\",\"number_of_floors\":9}}"
```
### 5. DELETE /objects/{id}

Permanently deletes a real estate object (bypasses trash).

    Method: DELETE

    Authentication: ✅ Required

    Path Param: id (integer)

✅ Example:
```bash
curl -X DELETE http://localhost:8080/wp-json/realty/v1/objects/71 \
  -u "admin:I1dK)sZEOJu(kq!QZN6SrutF"
```
## Summary 
| Method | Endpoint        | Auth | Description                    |
| ------ | --------------- | ---- | ------------------------------ |
| GET    | `/objects`      | ❌    | List properties (with filters) |
| GET    | `/objects/{id}` | ❌    | Get single property            |
| POST   | `/objects`      | ✅    | Create property                |
| PUT    | `/objects/{id}` | ✅    | Update property                |
| DELETE | `/objects/{id}` | ✅    | Delete property                |


🛠 Created for RealtyDemo test project