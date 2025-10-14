# Agent Guidelines for Thesis Management System

## Build/Test/Lint Commands
- **Install**: `composer install && npm install`
- **Dev Server**: `composer dev` (runs server, queue, logs, and vite concurrently) OR `php artisan serve` + `npm run dev`
- **Build Assets**: `npm run build`
- **Run All Tests**: `php artisan test` OR `composer test`
- **Run Single Test**: `php artisan test --filter=TestClassName` (e.g., `php artisan test --filter=StudentDashboardTest`)
- **Lint/Format PHP**: `vendor/bin/pint` (Laravel Pint - PSR-12 standard)
- **Database Migration**: `php artisan migrate --seed`
- **Clear Config**: `php artisan config:clear`

## Architecture Overview
- **Stack**: Laravel 12 (PHP 8.2+), MySQL/SQLite, Blade + Tailwind CSS + Alpine.js, Vite
- **Pattern**: MVC with Service Layer (`app/Services/` for business logic)
- **Key Services**: `SupervisorAssignmentService`, `StudentApiService`, `SupervisorApiService`, `PerformanceMonitoringService`
- **Database**: SQLite (testing), MySQL (production); use Eloquent ORM exclusively
- **External API**: `http://puc.ac.bd:8012/api` for student/teacher data sync
- **Testing**: PHPUnit/Pest with `RefreshDatabase` trait, factories in `database/factories/`

## Code Style & Conventions
- **Indentation**: 4 spaces, LF line endings (see `.editorconfig`)
- **PHP Standard**: PSR-12, enforced by Laravel Pint
- **Imports**: Group by type (Laravel, third-party, app) with blank lines between
- **Naming**: PascalCase for classes, camelCase for methods/variables, snake_case for DB columns
- **Models**: Use Eloquent relationships (e.g., `hasMany`, `belongsTo`, `belongsToMany`)
- **Controllers**: Thin controllers - delegate business logic to Services
- **Validation**: Use Form Request classes or controller validation methods
- **Error Handling**: Return JSON for API routes, redirect with errors for web routes
- **Security**: ALWAYS use CSRF tokens, parameterized queries via Eloquent, rate limiting on sensitive routes
