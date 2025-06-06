# RealtyDemo - Etcetera Agency WP Developer Test Task

This repository contains the solution for the Strong Back WP Developer test task provided by Etcetera Agency. The project involves building a WordPress site to manage real estate properties, including custom post types, custom fields, a REST API, AJAX filtering, and Docker integration, with support for both MySQL/MariaDB and **PostgreSQL** databases.

## Project Overview

The goal of this task was to demonstrate skills in WordPress development, including theme and plugin development, API integration, database management (supporting multiple database types), and modern development workflows using Docker.

## Project Features

- **WordPress Setup:** Standard WordPress installation managed via Docker.
- **Database Support:** Configurable to run with either MySQL/MariaDB or **PostgreSQL**.
- **Base Theme:** Utilizes a Bootstrap-based theme (like Understrap or WP Bootstrap Starter).
- **Child Theme:** A custom child theme (`realtydemo-child`) for all customizations and templates.
- **Custom Post Type:** `realty_property` (Об'єкт нерухомості) for representing buildings.
- **Custom Taxonomy:** `realty_district` (Район) associated with the `realty_property` post type.
- **Advanced Custom Fields (ACF):** Used to define and manage property details:
  - Building Name (text input)
  - Coordinates (text input)
  - Number of Floors (select list, 1-20)
  - Building Type (radio: panel/brick/foam block)
  - Eco-Friendliness Rating (select list, 1-5)
  - Building Image (image field)
  - Premises Repeater Field:
    - Area (text input)
    - Number of Rooms (radio, 1-10)
    - Balcony (radio, yes/no)
    - Bathroom (radio, yes/no)
    - Premises Image (image field)
- **Custom Plugin (Core Functionality):** Initializes the CPT, Taxonomy, and potentially other core hooks.
- **Custom Plugin (API):** Implements a REST API endpoint for CRUD operations on `realty_property` posts.
  - `POST /wp-json/strongback/v1/objects`: Create a new property.
  - `GET /wp-json/strongback/v1/objects?house_name=...&paged=1`: Retrieve properties with filtering capabilities.
  - `PUT /wp-json/strongback/v1/objects/{id}`: Update an existing property.
  - `DELETE /wp-json/strongback/v1/objects/{id}`: Delete a property.
  - _(Bonus)_ Potential XML import functionality.
- **AJAX Filtering:** A shortcode and widget display a filter form on the frontend. Search results are loaded via AJAX below the filter, displaying 5 items per page with pagination.
- **Custom Query Sorting:** PHP class modifies the main query for `realty_property` archive/listing pages to sort by the "Eco-Friendliness Rating" ACF field.
- **Dockerized Environment:** The entire setup runs within Docker containers defined in `docker-compose.yml` and associated Dockerfiles.
- **(Deployment Config):** Includes `render.yaml` potentially for deployment configurations on Render.com.

---

## 🚀 Project Highlights

1. **Dockerized WP Stack**

   - WordPress (latest) containerized with Docker & docker-compose
   - Supports MySQL/MariaDB **and** PostgreSQL (via PG4WP driver)
   - `.env.example` + `docker-compose.yml` for easy “git clone → up” setup

2. **Custom Child Theme**

   - **Theme Name:** StrongBack Theme (child of Understrap / Bootstrap-based starter)
   - Templates:
     - `front-page.php` → latest posts + `[sb_filter]` filter form
     - `single-real_estate.php` → single CPT view with ACF fields & galleries
     - Archive fallback + standard `page.php` & `single.php`

3. **Custom Plugin: `strongback-realestate`**

   - **CPT:** `real_estate` (“Об’єкт нерухомості”)
   - **Taxonomy:** `district` (“Район”)
   - **ACF Fields:**
     - `house_name`, `location_coords`, `floors_count`, `building_type`, `ecological_rating`
     - Gallery `images[]`
     - Repeater `rooms[]` with sub-fields: `room_area`, `room_count`, `has_balcony`, `has_bathroom`, `room_images[]`
   - **REST API:**
     - Namespace: `/wp-json/strongback/v1/objects`
     - GET → list + filters
     - POST → create (authenticated)
     - PUT → update (authenticated)
     - DELETE → delete (authenticated)
   - **AJAX Filter:**
     - Shortcode `[sb_filter]` & sidebar Widget
     - jQuery form → `admin-ajax.php?action=sb_search`
     - Returns 5 items per page + manual pagination (MySQL & Postgres)

4. **Query Modifier**
   - Hooks `pre_get_posts` on CPT archive & taxonomy pages
   - Orders by `ecological_rating` DESC

---

## Technology Stack

- WordPress
- PHP
- Database: MySQL / MariaDB **or PostgreSQL** (via Docker)
- Docker & Docker Compose
- Advanced Custom Fields (ACF) Plugin
- JavaScript / jQuery (for AJAX)
- HTML5 / CSS3
- Bootstrap CSS Framework (via base theme)
- Git
- PG4WP (PostgreSQL for WordPress) plugin if using PostgreSQL.

## Project Structure

```bash
REALTYDEMO/
├── docker/
│ └── DockerFiles/
│ └── WordPress.Dockerfile # Custom WordPress Dockerfile
├── wp/ # WordPress root directory (volume mounted)
│ ├── wp-admin/
│ ├── wp-content/ # Themes, Plugins, Uploads
│ │ ├── plugins/ # Custom plugins reside here
│ │ └── themes/ # Base and Child themes reside here
│ ├── wp-includes/
│ ├── .htaccess
│ ├── index.php
│ ├── wp-config.php # WP Config (potentially generated or using wp-config-docker.php)
│ ├── wp-config-docker.php # Docker-specific WP config settings
│ └── ... (other WP core files)
├── .env # Environment variables for Docker Compose (GITIGNORED)
├── .env.example # Example environment variables
├── .dockerignore # Files/Dirs ignored by Docker build
├── .gitignore # Files/Dirs ignored by Git
├── docker-compose.yml # Docker Compose configuration
└── README.md # This file
```

## Prerequisites

- Docker
- Docker Compose
- Git
- Web Browser
- Code Editor (like VS Code)

## Installation and Setup

1.  **Clone the repository:**
    ```bash
    git clone <repository-url>
    cd realtydemo
    ```
2.  **Configure Environment:**
    - Copy the example environment file: `cp .env.example .env`
    - Edit the `.env` file and set the necessary environment variables (e.g., database credentials, ports if needed - refer to `docker-compose.yml` and `.env.example` for specifics).
3.  **Build and Run Containers:**
    ```bash
    cd docker
    docker-compose up -d --build
    ```
    This command will build the necessary images (if they don't exist) and start the WordPress, Database, and any other defined services in detached mode.
4.  **WordPress Installation:**
    - Access the site in your browser. The default URL is often `http://localhost` or `http://localhost:8080` (check the `ports` section in `docker-compose.yml` or your `.env` file).
    - Complete the standard WordPress installation process (language, site title, admin user, etc.) if it's not fully automated.
    - **Note:** Database details required during WP setup should match those defined for the database service in `docker-compose.yml` and potentially referenced via environment variables in `wp-config-docker.php`.
5.  **Activate Theme and Plugins:**
    - Log in to the WordPress admin panel (`/wp-admin`).
    - Navigate to `Appearance > Themes` and activate the Child Theme (`realtydemo-child` or similar).
    - Navigate to `Plugins` and activate the required plugins (ACF, Custom Functionality Plugin, Custom API Plugin).

## Usage

- **Admin Area:** Access `http://localhost/wp-admin` (or your configured URL).
- **Manage Properties:** Navigate to the "Real Estate Properties" section in the admin menu to add, edit, or delete building entries and assign them to "Districts". Fill in the ACF fields as required.
- **Frontend Filtering:** Visit the page where the filter shortcode/widget has been added (e.g., the homepage). Use the filter options and observe the AJAX-powered results below.
- **API Interaction:** Use tools like Postman, Insomnia, or `curl` to interact with the REST API endpoints listed in the "Features" section. Refer to the separate API documentation for detailed request/response formats and authentication methods (if any).

## API Documentation

Detailed API documentation outlining endpoints, request/response formats, filtering parameters, and authentication methods should be provided separately.

➡️ **[See Full API Documentation](./API_README.md)**

## Credentials for Review

- **Site URL:** `https://realtydemo.onrender.com`
- **WP Admin:** `https://realtydemo.onrender.com/wp-admin`
- **Username:** `Guest User`
- **Password:** `Zwl!dBpaSCr*rere9z85mFvg`

---

Thank you for the opportunity to complete this test task.
