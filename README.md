# Civic Report / Belagavi Civic Issue Reporter (BCIR)

A PHP + MySQL civic issue reporting and management system for local government and municipal operations. Citizens can report problems like potholes, broken street lights, damaged roads, garbage issues, water supply problems, and other local civic concerns. Admins and field workers can review, assign, and close complaints with image proof.

This project is designed as a local web application for a city or municipality to streamline complaint handling and public service accountability.

## Project Overview

The system includes these core user roles:

- Citizen: register/login, submit complaints, view complaint history and status
- Admin: manage all complaints, assign work to departments, manage helper/field-worker accounts, search/filter data
- Helper / Field Worker: view assigned tasks, update task progress, upload proof image, mark issues as resolved

The public landing page also presents system-wide statistics and a map of reported issues.

## Features

- Public landing page with city issue summary and map
- Citizen registration and login
- Complaint submission with category, description, photos, and location fields
- Complaint tracking with status flow: Pending → In Progress → Resolved
- Admin dashboard with stats, filters, search, assignment tools, and employee management
- Helper dashboard with task list and completion workflow
- Proof-of-work image upload for resolved complaints
- MySQL database-based records for users and complaints
- Responsive Bootstrap interface
- Optional email notification workflow for resolved issues

## Tech Stack

- PHP 8.x
- MySQL / MariaDB
- Apache (via XAMPP/WAMP/LAMP)
- Bootstrap 5
- Leaflet.js for map rendering
- PHPMailer (included in project)

## Repository Structure

```text
civicreport/
├── index.php                  # Public landing page with stats and map
├── login.php                 # Login page
├── register.php              # Citizen registration
├── logout.php                # Logout handler
├── admin_dashboard.php       # Admin management panel
├── citizen_dashboard.php     # Citizen dashboard
├── emp_dashboard.php         # Helper / field worker dashboard
├── raise_complaint.php       # Complaint submission page
├── view_complaint.php        # Complaint detail view page
├── assign_task.php           # Task assignment logic/page
├── db_connect.php            # Database connection config
├── disaster_help_system.sql  # Database schema and seed data
├── mailer.php                # Email sending helper
├── usecase.puml              # UML use-case diagram
├── uploads/                  # Uploaded complaint/proof images
├── assets/                   # CSS and JS assets
├── includes/                 # Shared PHP include files
├── PHPMailer/                # Mailing library
├── .git/                     # Git repository metadata
└── README.md                 # Project documentation
```

## Database Schema

The application uses a MySQL database named `disaster_help_system`.

Main tables:

### users

- `id`
- `name`
- `email`
- `phone`
- `password`
- `role` (`admin`, `citizen`, `helper`)
- `created_at`

### complaints

- `id`
- `user_id`
- `category`
- `description`
- `photo_path`
- `latitude`
- `longitude`
- `address`
- `status` (`Pending`, `In Progress`, `Resolved`)
- `assigned_to`
- `proof_path`
- `created_at`

## Prerequisites

Before running this project, make sure you have:

- XAMPP / WAMP / LAMP installed
- Apache and MySQL running
- PHP enabled
- A browser with internet access for Bootstrap and Leaflet CDN assets

## Setup Instructions

### 1. Place the project in the web root

For XAMPP, place the project inside:

```text
C:\xampp\htdocs\civicreport
```

### 2. Import the database

- Open phpMyAdmin
- Create a database named `disaster_help_system`
- Import the file `disaster_help_system.sql`

If you are using command line MySQL:

```bash
mysql -u root -p
CREATE DATABASE disaster_help_system;
USE disaster_help_system;
SOURCE disaster_help_system.sql;
```

### 3. Configure database credentials

Open `db_connect.php` and confirm:

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "disaster_help_system";
```

Update these values if your local MySQL setup uses a different username/password.

### 4. Ensure upload folders are writable

The application stores complaint photos and proof images inside the `uploads/` folder. Make sure it is writable by the web server.

On Linux/macOS:

```bash
chmod -R 755 uploads
```

On Windows, ensure the folder permissions allow Apache to write to it.

### 5. Run the app

Open your browser and visit:

```text
http://localhost/civicreport/
```

Or open the landing page directly:

```text
http://localhost/civicreport/index.php
```

## Default Usage Flow

### Citizen flow

1. Go to `register.php` to create a citizen account
2. Log in at `login.php`
3. Open the dashboard and submit a complaint
4. Track status updates and resolution progress

### Admin flow

1. Log in using an admin account
2. View all issues in the admin dashboard
3. Search/filter complaints
4. Assign complaints to helper departments
5. Update complaint status and manage staff accounts

### Helper / Field Worker flow

1. Log in as a helper account
2. View assigned tasks
3. Open a task and upload proof image
4. Mark the complaint as resolved

## Security Notes

This project is a functional academic/demo civic management platform, not a hardened production system. Some areas could be improved for security and validation, including:

- stronger input sanitization across all forms
- role-based access hardening
- CSRF protection
- secure session management
- stronger password policies
- production-grade deployment configuration

## Notes

- The project uses remote CDN assets (Bootstrap, Font Awesome, Leaflet), so an internet connection is required when loading the page for the first time.
- The database seed script includes sample records and helper departments for demonstration purposes.
- The project includes a UML model in `usecase.puml` for understanding the system interactions.

## License

This project does not currently include a formal license file. Usage, modification, and distribution should be handled according to your local project requirements.

## Contact / Project Context

This application is intended for civic service management and local governance use cases in a Belagavi-style urban environment.
