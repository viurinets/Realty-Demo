# RealtyDemo – Etcetera Agency WP Developer Test Task

This repository contains the solution for the WordPress Developer test task provided by Etcetera Agency. The project demonstrates how to build a WordPress-based real estate management platform using a custom plugin, REST API integration, AJAX filters, Advanced Custom Fields, and Docker setup with PostgreSQL support.

---

## 📋 Project Overview

The goal of this project was to demonstrate strong WordPress development skills by implementing a custom plugin, REST API, AJAX frontend functionality, and a Docker-based development workflow. The project manages real estate listings with custom post types, fields, taxonomies, and dynamic filtering.

---

## 🎯 Project Features

- **WordPress Setup:** Runs inside Docker containers for isolated development.
- **Database Support:** Uses PostgreSQL as the database (via PG4WP).
- **Custom Post Type:** `real_estate` (Real estate object).
- **Custom Taxonomy:** `district` (related to real estate location).
- **Advanced Custom Fields (ACF):** Used to define custom metadata for properties:
  - `house_name` (Text)
  - `location_coords` (Numeric)
  - `floors_count` (Select: 1–20)
  - `building_type` (Radio: panel/brick/foam block)
  - `eco_rating` (Select: 1–5)
  - `main-image[]` (Image)
  - `rooms[]` (Repeater):
    - `room_area` (Numeric)
    - `room_count` (Numeric)
    - `has_balcony` (Radio: yes/no)
    - `has_bathroom` (Radio: yes/no)
    - `room_image[]` (Image)
- **Custom Plugin: Real Estate Manager**
  - Registers CPT, Taxonomy, and custom REST API routes
  - AJAX filter with shortcode
  - Archive sorting by eco rating
- **REST API Endpoints:**
  - `GET /wp-json/realty/v1/objects`
  - `POST /wp-json/realty/v1/objects`
  - `PUT /wp-json/realty/v1/objects/{id}`
  - `DELETE /wp-json/realty/v1/objects/{id}`
- **AJAX Filter Shortcode:** `[real_estate_filter]` renders a form for filtering properties with AJAX-loaded results.
- **Sorting:** Custom query modification to sort archive pages by the ACF field `eco_rating`.
- **Docker Environment:** Configured with nginx, PHP, PostgreSQL, and WordPress in containers.

---

## 🚀 Highlights

1. **Dockerized Development Stack**

   - Containerized WordPress + PostgreSQL + nginx
   - Easy local setup with `docker-compose`
   - Uses PG4WP plugin to enable PostgreSQL compatibility

2. **Custom Plugin: `real-estate-manager`**

   - Located in `wp/wp-content/plugins/real-estate-manager/`
   - Handles CPT `real_estate` and taxonomy `district`
   - Adds ACF fields 
   - Exposes REST API endpoints for full CRUD
   - Implements AJAX filter with pagination & sorting

3. **AJAX Filter**

   - Frontend filter form via `[real_estate_filter]`
   - Built-in pagination (5 items per page)

4. **ACF Integration**

   - Structured ACF group for buildings and rooms
   - Easily expandable

---

## 🧱 Tech Stack

- WordPress
- PHP 8.x
- PostgreSQL (via PG4WP)
- Advanced Custom Fields Pro
- Docker & Docker Compose
- JavaScript / jQuery (AJAX)
- HTML5 / CSS3
- Git

---

## 📁 Project Structure

```bash
realtydemo/
├── docker/                       # Docker-related files
│   └── docker-compose.yml       # Container definitions
│
├── wp/                          # WordPress root (volume mounted)
│   ├── wp-config.php            # PG4WP-compatible config
│   └── wp-content/
│       ├── plugins/
│       │   └── real-estate-manager/  # Custom plugin source
│       └── themes/              # Theme/child theme 
│
├── .env.example                 # Example environment vars
├── .gitignore
├── README.md                    # This file
```
## Prerequisites

- Docker
- Docker Compose
- Git
- Web Browser
- Code Editor

## Installation and Setup

1.  **Clone the repository:**
    

```bash
    git clone <repository-url>
    cd realtydemo-main
```

2.  **Configure Environment:**
    - Copy the example environment file: cp .env.example .env
    - Edit the .env file and set the necessary environment variables (e.g., database credentials, ports if needed - refer to docker-compose.yml and .env.example for specifics).
3.  **Build and Run Containers:**
    

```bash
    cd docker
    docker-compose up -d --build
```

    This command will build the necessary images (if they don't exist) and start the WordPress, Database, and any other defined services in detached mode.
4.  **WordPress Installation:**
    - Access the site in your browser. The default URL is often http://localhost or http://localhost:8080 (check the ports section in docker-compose.yml or your .env file).
    - Complete the standard WordPress installation process (language, site title, admin user, etc.) if it's not fully automated.
    - **Note:** Database details required during WP setup should match those defined for the database service in docker-compose.yml and potentially referenced via environment variables in wp-config-docker.php.
5.  **Activate Theme and Plugins:**
    - Log in to the WordPress admin panel (/wp-admin).
    - Navigate to Appearance > Themes and activate the Child Theme (realty-child-theme or similar).
    - Navigate to Plugins and activate the required plugins (ACF, Custom Functionality Plugin, Custom API Plugin).
    ## Credentials for Review
    - http://localhost:8080/wp-admin
    - username: admin 
    - password: I1dK)sZEOJu(kq!QZN6SrutF