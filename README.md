# FRACA SERVCOM Inventory Management System

> Web-based inventory system for FRACA SERVCOM (furniture & hardware supplies)

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## 🚀 Quick Start

```bash
# 1. Clone repository
git clone https://github.com/PhilipLee2002/fraca-inventory-system.git
cd fraca-inventory-system

# 2. Install dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=fraca_inventory
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Run migrations
php artisan migrate

# 6. Start development server
php artisan serve
Access at: http://localhost:8000

📋 Project Features
Product Management – CRUD operations, barcode support, categories

Stock Control – Real-time tracking, low-stock alerts, audit logs

Sales & Purchases – Invoice generation, line items, stock auto-update

Reporting – Sales/Purchase/Stock reports, CSV/PDF exports

User Management – Role-based access (Admin/Staff), authentication

Alerts – Dashboard notifications, email alerts for low stock

🛠️ Tech Stack
Component	Technology
Backend	PHP Laravel 10+
Database	MySQL
Frontend	HTML, CSS, JavaScript
Templates	Laravel Blade
CSS Framework	(Optional: Tailwind CSS)
Development	XAMPP / Laravel Valet
📁 Project Structure
text
app/
├── Models/              # Eloquent models (Product, Sale, Purchase, etc.)
├── Http/
│   ├── Controllers/    # Application controllers
│   └── Middleware/     # Authentication & role middleware
database/
├── migrations/         # Database schema definitions
├── seeders/           # Test data population
resources/
├── views/             # Blade templates (.blade.php)
├── css/               # Stylesheets
└── js/                # JavaScript files
public/                # Web server root
🔧 Development Commands
bash
# Database
php artisan migrate                 # Run all migrations
php artisan migrate:refresh         # Reset and re-run migrations
php artisan db:seed                 # Populate with test data
php artisan make:model Product -m   # Create model with migration

# Code Generation
php artisan make:controller ProductController --resource
php artisan make:request StoreProductRequest
php artisan make:seeder ProductSeeder

# Development
php artisan serve                   # Start local server
php artisan tinker                  # Interactive PHP shell
php artisan route:list              # Show all routes

# Code Quality
vendor/bin/php-cs-fixer fix         # Format PHP code
📦 Installed Packages
Package	Purpose
laravel/breeze	Authentication scaffolding
barryvdh/laravel-dompdf	PDF report generation
league/csv	CSV import/export functionality
friendsofphp/php-cs-fixer	PHP code formatting standards
📄 Project Documentation
development_reference.md – Complete project specifications, feature list, and phased development plan

PROJECT_BOARD.md – Task tracking and progress monitoring

.env.example – Environment configuration template

🔄 Development Phases
Phase 1 – Database & Core Models

Phase 2 – Authentication & Authorization

Phase 3 – Core Business Logic (Sales/Purchases)

Phase 4 – API & Testing

Phase 5 – Frontend Implementation

Phase 6 – Notifications & Background Jobs

Phase 7 – Testing & QA

Phase 8 – Deployment & Handover

About Laravel
Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

Simple, fast routing engine.

Powerful dependency injection container.

Multiple back-ends for session and cache storage.

Expressive, intuitive database ORM.

Database agnostic schema migrations.

Robust background job processing.

Real-time event broadcasting.

Learning Laravel
Laravel has the most extensive and thorough documentation and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

🔒 Security Notes
Passwords are hashed using bcrypt

CSRF protection enabled on all forms

SQL injection prevention via Eloquent ORM

XSS protection via Blade templating

Role-based access control on all protected routes

📞 Support & Contact
Developer: Philip Lee (Phillee2003@gmail.com)

Client: FRACA SERVCOM

Repository: https://github.com/PhilipLee2002/fraca-inventory-system

License
The Laravel framework is open-sourced software licensed under the MIT license.

*FRACA Inventory System | Last Updated: 2025-01-21*

For complete project specifications and development plan, see [development_reference.md](development_reference.md).
