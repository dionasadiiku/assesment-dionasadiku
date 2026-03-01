
# DIONA-ASSESSMENT

## Project Overview
This project is a web application that allows users to upload videos, create annotations, and add bookmarks to specific timestamps. It includes authentication and role-based access control, with separate views for normal users and admins.

## Features

- **User authentication:** register, login, logout  
- **Video upload and playback**  
- **Annotations:** add timestamp and description to videos  
- **Bookmarks:** linked to video timestamps for easy navigation  
- **Admin view:** view all videos, annotations, and bookmarks  

## File Structure

```

DIONA-ASSESSMENT/
│
├─ auth/
│   ├─ login.php
│   ├─ logout.php
│   └─ register.php
│
├─ config/
│   └─ config.php
│
├─ controllers/
│   ├─ AnnotationController.php
│   ├─ AuthController.php
│   ├─ BookmarkController.php
│   └─ VideoController.php
│
├─ css/
│   ├─ admin.css
│   ├─ dashboard.css
│   ├─ login.css
│   ├─ register.css
│   ├─ upload.css
│   └─ watch.css
│
├─ database/
│   └─ schema.sql
│
├─ middleware/
│   ├─ AdminMiddleware.php
│   └─ AuthMiddleware.php
│
├─ models/
│   ├─ annotation.php
│   ├─ bookmark.php
│   ├─ user.php
│   └─ video.php
│
├─ pages/
│   ├─ admin.php
│   └─ dashboard.php
│
├─ uploads/
│   └─ (uploaded videos)
│
├─ videos/
│   ├─ upload.php
│   └─ watch.php
│
└─ index.php

````

## Technologies Used

- **Backend:** PHP (MVC-like structure)  
- **Frontend:** HTML, CSS  
- **Database:** MySQL  
- **Middleware:** Custom PHP middleware for authentication and admin access  

## Setup Instructions

1. Clone the repository:
```bash
git clone https://github.com/yourusername/assessment-yourfullname.git
cd assessment-yourfullname
````

2. Set up the database:

* Create a MySQL database
* Import `database/schema.sql`

3. Update database connection in `config/config.php`:

```php
<?php
$host = "localhost";
$db_name = "your_db_name";
$username = "your_db_user";
$password = "your_db_password";

$pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
?>
```

4. Run the application on a local server (e.g., XAMPP, WAMP, or PHP built-in server):

```bash
php -S localhost:8000
```

5. Navigate in your browser:

* `http://localhost:8000/index.php` → main page
* `http://localhost:8000/pages/dashboard.php` → user dashboard after login
* `http://localhost:8000/pages/admin.php` → admin view

## Assumptions / Limitations

* Only MP4 videos are supported for upload
* Annotations are text-based; geometric/free-hand shapes are simplified
* Users must be logged in to create annotations or bookmarks
* Admin view shows all videos, annotations, and bookmarks (no editing yet)
* No email verification for registration

## How It Works

1. Users register and log in.
2. Logged-in users can upload videos via `videos/upload.php`.
3. Users watch videos using `videos/watch.php`.
4. While watching, users can add annotations (timestamp + description) or bookmarks (timestamp + title).
5. Admins can view all videos, annotations, and bookmarks on `pages/admin.php`.

## Notes

* Commit history shows step-by-step progress
* Focus is on backend logic, authentication, and role-based access control
* Possible future improvements:

  * Real-time annotations
  * Drag-and-drop annotation positioning
  * Video editing capabilities
  * Responsive design and mobile support



