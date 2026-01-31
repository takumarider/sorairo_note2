# Copilot Instructions - Sorairo Note 2

## Project Context

**Sorairo Note 2** is an online reservation management system for salons built with Laravel 12 + Filament 3. Users select treatment menus and time slots to make reservations. Administrators manage menus, slots, and reservations through the Filament admin panel.

## Build, Test & Development Commands

### Docker Environment

```bash
# Start containers (from project root)
docker compose up -d

# Stop containers
docker compose down

# Execute commands in container
docker exec -it sorairo_app <command>

# View logs
docker compose logs -f app
```

### PHP/Laravel Commands

```bash
# Run migrations
docker exec -it sorairo_app php artisan migrate

# Fresh migration with seeding
docker exec -it sorairo_app php artisan migrate:fresh --seed

# Run all tests
docker exec -it sorairo_app php artisan test

# Run single test file
docker exec -it sorairo_app php artisan test --filter=TestClassName

# Code formatting
docker exec -it sorairo_app ./vendor/bin/pint

# Generate Filament resource
docker exec -it sorairo_app php artisan make:filament-resource ModelName --generate
```

### Frontend Commands

```bash
# Install dependencies (run from src/)
npm install

# Development server with hot reload
npm run dev

# Production build
npm run build
```

### Unified Development Server

```bash
# Start Laravel server + queue + logs + Vite (run from src/)
composer dev
```

This command starts all services concurrently with color-coded output.

## Architecture Overview

### Tech Stack

- **Backend**: Laravel 12, PHP 8.4, PostgreSQL 16
- **Admin Panel**: Filament 3.x
- **Frontend**: Blade templates, Tailwind CSS 4, jQuery 3, Alpine.js 3
- **Build**: Vite 7
- **Dev Environment**: Docker Compose (app, db, mailpit)

### Key Models & Relationships

```
users (is_admin flag)
├─── 1:N → reservations
│
menus (施術メニュー)
├─── 1:N → slots (時間枠)
└─── 1:N → reservations
│
slots (時間枠)
└─── 1:1 → reservation
```

**Reservation Flow**: User selects menu → picks available slot → confirms → reservation created

### Directory Structure

```
src/
├── app/
│   ├── Console/Commands/       # Artisan commands (CreateAdminUser)
│   ├── Filament/
│   │   ├── Resources/          # Admin CRUD (Menu, User, Note)
│   │   └── Widgets/            # Dashboard widgets
│   ├── Http/Controllers/       # Web controllers
│   └── Models/                 # Eloquent models (User, Menu, Note)
├── database/
│   ├── migrations/             # Schema definitions
│   └── seeders/                # Database seeders
├── resources/
│   ├── css/app.css             # Tailwind styles
│   ├── js/app.js               # jQuery + Alpine.js
│   └── views/                  # Blade templates
└── routes/
    ├── web.php                 # User routes
    └── auth.php                # Laravel Breeze auth routes
```

## Key Conventions

### Admin Access Control

- Users have an `is_admin` boolean flag in the database
- Admin panel at `/admin` is automatically protected by Filament
- To create admin users: `docker exec -it sorairo_app php artisan make:admin`
- Regular users see a "管理画面へ" button on dashboard if `is_admin = true`

### Database Naming

- **Japanese comments in migrations**: All table/column comments use Japanese for clarity
- **Table naming**: Plural English (users, menus, slots, reservations)
- **Timestamps**: All models use `timestamps()` (created_at, updated_at)
- **Indexes**: Add on foreign keys and frequently queried boolean columns (e.g., `is_active`, `is_reserved`)

### Model Conventions

- Use `$fillable` for mass assignment protection
- Define `casts()` method for type casting (booleans, dates, etc.)
- Define relationships explicitly (hasMany, belongsTo)
- Scope queries: Use named scopes for common filters (e.g., `availableSlots()` on Menu model)

### Filament Resources

- Resources are in `app/Filament/Resources/`
- Generate with: `php artisan make:filament-resource ModelName --generate`
- Use Japanese labels for form fields and table columns (`label()` method)
- Enable search on text columns: `->searchable()`
- Toggle visibility for timestamps: `->toggleable(isToggledHiddenByDefault: true)`

### Frontend Patterns

- **Blade templates**: All views in `resources/views/`
- **Layouts**: Use `<x-app-layout>` for authenticated pages (from Laravel Breeze)
- **Styles**: Tailwind utility classes only (no custom CSS unless necessary)
- **JS**: jQuery for DOM manipulation, Alpine.js for reactive components
- **Icons**: Use Heroicons (`heroicon-o-*` for outline, `heroicon-s-*` for solid)

### Reservation Business Logic

1. **One slot = one reservation** (first-come, first-served)
2. **Slot status**: `is_reserved` boolean on slots table
3. **Reservation status**: Enum (confirmed, canceled, completed)
4. **Cancel rules**: Users can cancel from "My Page"; admin can cancel from Filament
5. **Reserved slots cannot be deleted** by admin (must cancel reservation first)

### Environment Configuration

- Default admin credentials in `.env`:
  ```
  ADMIN_NAME="izumi"
  ADMIN_EMAIL=admin@example.com
  ADMIN_PASSWORD=sorairo_admin
  ```
- Database connection: PostgreSQL via `sorairo_db` container
- Mail testing: Mailpit at http://localhost:8025

## Implementation Status

### ✅ Completed

- Docker environment setup
- Laravel 12 + Filament 3 installation
- User authentication (Laravel Breeze)
- Admin identification system (is_admin flag)
- Admin dashboard access button
- Menu model & migration
- Basic Filament resources (User, Note)

### 🚧 In Progress / Planned

- Menu selection screen (user-facing)
- Calendar UI for slot selection
- Reservation confirmation & finalization
- My Page (user's reservation list)
- Slot management in Filament
- Reservation management in Filament
- Email notifications (reservation confirmed/canceled)

## Important Implementation Notes

### When Creating New Migrations

Always add comments in Japanese for clarity:

```php
$table->string('name');                        // メニュー名
$table->integer('price');                      // 料金（円）
$table->boolean('is_active')->default(true);   // 有効フラグ
```

### When Adding New Routes

User routes go in `routes/web.php`. Admin routes are auto-generated by Filament.

### When Working with Dates/Times

- Use `now()->toDateString()` for date comparisons
- Store dates as `date` type, times as `time` type in migrations
- Display format: `Y/m/d H:i` (2026/02/15 14:00)

### When Creating Blade Templates

- Responsive design: Mobile-first approach
- Use Tailwind responsive prefixes (`sm:`, `md:`, `lg:`)
- Keep templates simple; complex logic goes in controllers

### Container Names

- PHP app: `sorairo_app`
- PostgreSQL: `sorairo_db`
- Mailpit: `sorairo_mail`

Always use `docker exec -it sorairo_app` prefix for Laravel/PHP commands.

## Testing Strategy

- Feature tests for reservation flow
- Unit tests for model scopes and business logic
- Browser tests (if needed) using Laravel Dusk
- Run tests inside Docker container: `docker exec -it sorairo_app php artisan test`

---

*This file incorporates key information from AGENT.md and README.md. For complete project documentation, refer to src/README.md.*
