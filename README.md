# Water Sports Rental Management System

A web-based application for managing water sports equipment rentals, including jet skis and tourist boats.

## Features
- Client Management
- Jet Ski Management
- Tourist Boat Management
- Reservation System
- Admin Dashboard
- PDF Invoice Generation
- Email Notifications
- Responsive Design

## Requirements
- PHP 7.4+
- MySQL 5.7+
- Web Server (Apache/Nginx)
- Modern Web Browser

## Installation
1. Clone this repository
2. Import the database schema from `database/schema.sql`
3. Configure database connection in `config/database.php`
4. Configure your web server to point to the `public` directory
5. Access the application through your web browser

## Project Structure
```
├── config/             # Configuration files
├── database/          # Database schema and migrations
├── includes/          # PHP class files and utilities
├── public/            # Publicly accessible files
│   ├── css/          # Stylesheets
│   ├── js/           # JavaScript files
│   ├── images/       # Image assets
│   └── index.php     # Main entry point
├── templates/         # HTML templates
└── vendor/           # Third-party dependencies
``` 