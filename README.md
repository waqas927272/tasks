# Task Management System

A complete PHP MVC web application for managing tasks with role-based access control.

## Features

- **User Roles & Permissions**
  - Admin: Can create/manage users, assign tasks, view all tasks
  - CSM (Client Success Manager): Can update task statuses for assigned clients
  - Client: Can view and update only their own tasks

- **Task Management**
  - Full CRUD operations for tasks
  - Task fields: Client Name, CSM Name, Heading, Description, Date & Time, Status
  - Task history tracking with status and date changes logged
  - Status options: Pending, In Progress, Completed

- **Notifications**
  - Automatic notifications for task creation, status changes, and date changes
  - Real-time notification count updates
  - Mark notifications as read

- **Modern MVC Architecture**
  - Clean separation of concerns
  - Custom routing system
  - Database abstraction layer
  - Environment-based configuration

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- MAMP/XAMPP/WAMP or similar local server

## Installation

1. Clone or download this repository to your web server directory
2. Ensure Apache mod_rewrite is enabled
3. Navigate to `http://localhost/tasks/setup.php` in your browser
4. Follow the setup wizard:
   - Configure database connection
   - Run migrations to create tables
   - Seed the database with sample data

## Default Login Credentials

After running the setup wizard, you can login with these credentials:

- **Admin Account**
  - Email: admin@example.com
  - Password: admin123

- **CSM Account**
  - Email: john.csm@example.com
  - Password: csm123

- **Client Account**
  - Email: client1@example.com
  - Password: client123

## Project Structure

```
project-root/
│
├── app/
│   ├── Controllers/        # Application controllers
│   ├── Models/             # Data models
│   ├── Views/              # View templates
│   └── Core/               # Core framework classes
│
├── config/
│   └── database.php        # Database configuration
│
├── public/
│   ├── index.php           # Application entry point
│   ├── css/                # Stylesheets
│   ├── js/                 # JavaScript files
│   └── .htaccess           # URL rewriting rules
│
├── routes/
│   └── web.php             # Application routes
│
├── migrations/             # Database migrations
├── seeders/                # Database seeders
├── storage/                # Application storage
│
├── setup.php               # Setup wizard
├── .env.example            # Environment template
├── .htaccess               # Root directory rules
└── README.md               # This file
```

## Usage

### For Admins
- Create and manage user accounts
- Assign tasks to clients with specific CSMs
- View and manage all tasks in the system
- Access user management from the navigation menu

### For CSMs
- View tasks assigned to your clients
- Update task statuses
- Track task progress
- Receive notifications for task changes

### For Clients
- View your assigned tasks
- Update task statuses
- Track task history
- Receive notifications for task updates

## Security Features

- Password hashing using bcrypt
- Session-based authentication
- Role-based access control
- CSRF protection ready (can be implemented)
- SQL injection prevention through prepared statements

## Troubleshooting

### Setup Issues
- Ensure your database server is running
- Check that the database user has CREATE privileges
- Verify Apache mod_rewrite is enabled

### Login Issues
- Check that sessions are enabled in PHP
- Ensure cookies are enabled in your browser
- Verify the .env file has correct database credentials

### URL Routing Issues
- Confirm mod_rewrite is enabled: `a2enmod rewrite`
- Check that .htaccess files are present
- Ensure Apache AllowOverride is set to All

## Development

### Adding New Features
1. Create new controllers in `app/Controllers/`
2. Add models in `app/Models/`
3. Create views in `app/Views/`
4. Define routes in `routes/web.php`

### Database Changes
1. Create new migration files in `migrations/`
2. Run migrations through the setup wizard or manually

## License

This project is open source and available for educational and commercial use.