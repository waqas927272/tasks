Build a complete plug-and-play web application using:
Frontend: HTML5, CSS3, JavaScript (modern responsive UI for all devices).
Backend: PHP (custom MVC structure, NOT Laravel).
Database: MySQL with migration and seeding.
Environment file: .env for configuration.

Required MVC Folder Structure

project-root/
│
├── app/
│   ├── Controllers/        # PHP controllers (TaskController, UserController, AuthController, etc.)
│   ├── Models/             # PHP models (Task.php, User.php, Client.php, CSM.php)
│   ├── Views/              # HTML/PHP view files for each page
│   ├── Core/               # Core classes (Database.php, Controller.php, Model.php, Router.php)
│
├── config/
│   ├── database.php        # Loads DB settings from .env
│
├── public/
│   ├── index.php           # Entry point (routes requests)
│   ├── css/                # CSS files
│   ├── js/                 # JavaScript files
│   ├── images/             # Static images/icons
│
├── routes/
│   ├── web.php             # Define routes for all controllers
│
├── storage/
│   ├── logs/               # Log files (optional)
│
├── migrations/
│   ├── create_users_table.php
│   ├── create_tasks_table.php
│   ├── create_notifications_table.php
│
├── seeders/
│   ├── seed_users.php
│   ├── seed_tasks.php
│
├── setup.php               # Setup wizard for DB config, migration, and seeding
├── .env.example            # Sample env file template
├── .htaccess               # For URL rewriting (clean URLs)
└── README.md

Features to Implement
✅ User Roles & Permissions

Admin: Can create/manage users, assign tasks, view all tasks.

CSM (Client Success Manager): Can update task statuses for assigned clients.

Client: Can view and update only their own tasks.

✅ Task Management

Task fields:

Client Name

CSM Name

Task Heading

Task Description

Date & Time

Status (Pending, In Progress, Completed)

Full CRUD (Create, Read, Update, Delete).

Task history (status & date changes logged).

✅ Notifications

Automatic notifications for:

Task created

Status changed

Date changed

✅ Setup & Deployment

setup.php should handle:

DB connection check

Running migrations

Running seeders

App must run seamlessly on localhost AND live hosting without editing URLs.

✅ Frontend UI

Clean, modern, responsive dashboard for all users.

Navigation for switching between views (Admin, CSM, Client).

✅ Technical Requirements

Use .env for DB settings.

Use .htaccess for clean URLs (no index.php?controller=task).

MVC structure must be clear and maintainable.

🚀 Deliverables
Full MVC PHP project.

SQL migrations and seeders.

setup.php script.

.env.example file.

Clean and responsive frontend design.