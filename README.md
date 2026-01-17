# University Thesis Management System

A comprehensive Laravel-based thesis management platform for academic institutions to manage student groups, supervisor assignments, report submissions, and thesis evaluations. The system integrates with an external university API for real-time authentication and data synchronization.

## 📋 Table of Contents
- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [Testing](#-testing)
- [Production Deployment](#-production-deployment)
- [API Integration](#-api-integration)
- [Security](#-security)
- [Documentation](#-documentation)
- [Contributing](#-contributing)

## ✨ Features

### Core Functionality
- **Multi-Role Authentication**: Admin, Advisor, Supervisor, Co-Supervisor, Panel Member, Student, and Teacher roles
- **Intelligent Supervisor Assignment**: Three lottery algorithms (AOI-based, Ranking-based, Combined) with fairness guarantees
- **Group Management**: Automated group creation with student assignments and area of interest matching
- **Report Management**: Draft, submission, review, and approval workflow with version control
- **PDF Annotation System**: Supervisors can annotate student submissions with feedback sessions
- **Meeting Scheduler**: Create, track, and export meetings with attendance management
- **Performance Monitoring**: Real-time system metrics, database health, and API performance tracking
- **Excel Integration**: Import/export group assignments with validation

### Role-Specific Capabilities
| Role | Key Features |
|------|-------------|
| **Admin** | Manage supervisors, batches, areas of interest, system monitoring |
| **Advisor** | Create groups, assign supervisors (manual/lottery), manage student assignments |
| **Supervisor** | Manage groups, create reports, annotate submissions, schedule meetings |
| **Co-Supervisor** | View groups, optionally manage meetings (permission-based) |
| **Panel Member** | Review and annotate reports without full supervisor privileges |
| **Teacher** | Multi-role access (can be supervisor + panel member simultaneously) |
| **Student** | Submit reports, view annotations, access meeting schedules |

## 🛠 Technology Stack

### Backend
- **Laravel 12** (PHP 8.2+)
- **Eloquent ORM** for database interactions
- **Laravel Breeze** for authentication scaffolding
- **Pest PHP 3.8** for testing
- **PDF Generation**: DomPDF and Spatie Laravel PDF
- **Excel**: Maatwebsite Excel 3.1
- **Queue Support**: Sync (development), Redis/Database (production)
- **Caching**: File (development), Redis (recommended for production)

### Frontend
- **Tailwind CSS 3.1** for styling
- **Alpine.js 3.4.2** for interactivity
- **Vite 6.2.4** for asset bundling
- **Axios 1.8.2** for API calls

### Database
- **SQLite** (development/testing)
- **MySQL 8.0+** or **PostgreSQL 13+** (production)
- **22 migrations** covering all entities

## 📋 Requirements

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ and NPM 9+
- MySQL 8.0+ or PostgreSQL 13+ (production)
- SQLite 3 (development)

### Required PHP Extensions
- PDO
- mbstring
- xml
- ctype
- json
- bcmath
- fileinfo
- tokenizer

## 🚀 Installation

### Development Setup

```bash
# Clone the repository
git clone <repository-url>
cd thesisrepo-backup

# Install dependencies
composer install
npm install

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database (for development)
touch database/database.sqlite

# Run migrations and seed data
php artisan migrate --seed

# Build frontend assets
npm run build

# Start development server
php artisan serve
```

Access the application at `http://localhost:8000`.

### Development with Hot Reload

For concurrent development with live reload:

```bash
composer dev
```

This runs the server, queue listener, logs, and Vite concurrently.

Alternatively, run separately:
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

## ⚙️ Configuration

### Environment Variables

Key configuration in `.env`:

```env
# Application
APP_NAME="Thesis Management System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (Development)
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

# Database (Production)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=thesis_management
# DB_USERNAME=root
# DB_PASSWORD=

# External API Configuration
EXTERNAL_API_BASE_URL=http://puc.ac.bd:8012/api
EXTERNAL_API_TIMEOUT=30
EXTERNAL_API_DEPARTMENT_ID=1
EXTERNAL_API_MAX_RETRIES=3
EXTERNAL_API_RETRY_DELAY=1000

# Cache (Production: use redis)
CACHE_STORE=file
# CACHE_STORE=redis

# Queue (Production: use redis or database)
QUEUE_CONNECTION=sync
# QUEUE_CONNECTION=redis

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
```

### Rate Limiting Configuration

The system implements rate limiting on external API calls:
- Student Dashboard: 10 requests/minute
- Advisor Dashboard: 20 requests/minute
- Batch Sync: 60 requests/hour
- Supervisor Sync: 120 requests/hour

Configure in `config/external_api.php`.

## 👤 Usage

### Default Accounts

Pre-seeded accounts for development (change passwords in production):

| Role | Email | Password | Access |
|------|-------|----------|--------|
| Admin | admin@example.com | password | Full system access |
| Advisor | advisor@example.com | password | Group and assignment management |
| Supervisor | supervisor@example.com | password | Report and meeting management |
| Student | john.student@example.com | password | Submissions and annotations |

### Common Tasks

**For Advisors:**
1. Navigate to `/advisor/students` to view synced students
2. Go to `/advisor/groups` to create groups
3. Use `/advisor/supervisor-assignment` to run the lottery
4. Preview assignments before confirming

**For Supervisors:**
1. Access `/supervisor/groups` to view assigned groups
2. Create reports at `/supervisor/reports/create`
3. Annotate submissions from group reports
4. Schedule meetings at `/supervisor/meetings`

**For Students:**
1. View assigned group at `/student/dashboard`
2. Submit reports at `/student/reports/{report}/submissions/create`
3. View annotations and feedback
4. Check meeting schedules

**For Admins:**
1. Sync supervisors: `/admin/supervisors` → "Sync from API"
2. Manage batches: `/admin/batches`
3. Monitor performance: `/admin/performance`
4. Configure areas of interest: `/admin/areas-of-interest`

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run with coverage (requires Xdebug/PCOV)
php artisan test --coverage

# Run specific test
php artisan test --filter=StudentDashboardTest

# Run tests in parallel
php artisan test --parallel
```

### Test Structure

- **40+ Feature Tests**: Authentication, dashboards, group management, assignments
- **9+ Unit Tests**: Business logic, services, algorithms
- **Security Tests**: CSRF, authorization, rate limiting
- **API Integration Tests**: External API calls with mocking

Tests use SQLite in-memory database and are reset between runs (`RefreshDatabase` trait).

## 🚀 Production Deployment

### Server Requirements

- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP 8.2+** with required extensions
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Redis** (recommended for cache and queues)
- **Supervisor** (for queue workers)
- **SSL Certificate** (Let's Encrypt recommended)

### Production Setup

```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Configure environment
cp .env.example .env.production
# Edit .env.production with production values

# Set production environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database configuration (MySQL example)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=thesis_production
DB_USERNAME=thesis_user
DB_PASSWORD=<secure-password>

# Cache and Queue
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1

# Run migrations
php artisan migrate --force

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Queue Worker Setup

Create supervisor configuration `/etc/supervisor/conf.d/thesis-worker.conf`:

```ini
[program:thesis-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
stopwaitsecs=3600
```

Reload supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start thesis-worker:*
```

### Web Server Configuration

**Nginx Example:**

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com;
    root /path/to/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Performance Optimization

**Database Indexing:**
Indexes are automatically created via migrations on:
- `groups.supervisor_id`, `groups.batch_number`
- `group_students.student_id`, `group_students.group_id`
- `reports.group_id`, `reports.status`
- `student_report_submissions.report_id`

**Caching Strategy:**
- Batch list: 60 minutes
- Student list: 30 minutes
- Supervisor list: 30 minutes
- Use Redis for production

**Query Optimization:**
Controllers use eager loading (`->with()`) to prevent N+1 queries.

## 🔌 API Integration

### External University API

The system integrates with `http://puc.ac.bd:8012/api` for:

**Authentication Endpoints:**
- `POST /Login/LoginAction` - Student login
- `POST /Teacher/Login` - Teacher login

**Data Sync Endpoints:**
- `GET /Teacher/TeacherList` - Fetch all teachers (supervisors)
- `GET /Student/batchwiseStudentList` - Fetch students by batch
- `GET /Student/programwiseBatch` - Fetch available batches

**Configuration:**
- Base URL: Configurable in `.env` (`EXTERNAL_API_BASE_URL`)
- Timeout: 30 seconds (configurable)
- Retries: 3 attempts with 1000ms delay
- Department ID: 1 (CSE, configurable)

**Response Caching:**
- Student list: 30 minutes
- Batch list: 60 minutes
- Prevents excessive API calls

**Error Handling:**
- Timeout errors logged and cached
- Failed syncs return graceful errors
- Retry logic for transient failures

### Services

- **`StudentApiService`**: Handles student data fetching and caching
- **`SupervisorApiService`**: Manages teacher/supervisor synchronization
- **`BatchApiService`**: Syncs batch information
- **`PerformanceMonitoringService`**: Tracks API performance metrics

## 🔒 Security

### Authentication
- External API validation for user credentials
- Secure password generation for synced users
- Laravel Breeze scaffolding with email verification
- Session-based authentication with secure cookies

### Authorization
- Role-based middleware: `admin`, `advisor`, `teacher`, `student`
- Permission checks in controllers and Blade templates
- CSRF protection on all state-changing requests
- Email verification required for sensitive actions

### Data Protection
- **Password Hashing**: Bcrypt with automatic salting
- **SQL Injection Prevention**: Eloquent ORM with parameter binding
- **XSS Protection**: Blade template escaping by default
- **File Upload Validation**: MIME type and size checks
- **Input Sanitization**: Laravel validation rules

### Rate Limiting
Configured throttling on API routes:
- Login: 5 attempts per minute
- External API calls: 10-120 requests per hour
- Student dashboard: 10 requests per minute
- Advisor operations: 20 requests per minute

### Security Headers
Configure in `config/cors.php` and web server:
- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `Content-Security-Policy` (configure as needed)

### Monitoring
Admin performance dashboard tracks:
- Failed login attempts
- Rate limit violations
- API errors and timeouts
- Database health and query performance

## 📖 Documentation

Comprehensive documentation available in the `/documentation` folder:
- **Setup Guide**: Detailed installation and configuration
- **Features Guide**: User manuals for each role
- **Developer Guide**: Code structure and API specifications
- **Testing Guide**: Test execution and coverage analysis
- **Architecture Diagrams**: PlantUML activity, sequence, and DFD diagrams

### Key Algorithms

**Supervisor Assignment Service** (`app/Services/SupervisorAssignmentService.php`):

1. **AOI-based Lottery**: Matches groups to supervisors based on Area of Interest, respects capacity limits
2. **Ranking-based Lottery**: Round-robin assignment by academic rank (Professor → Associate → Assistant → Lecturer)
3. **Combined Lottery**: Hybrid approach with AOI matching and ranking priorities

Features include deterministic ordering, dry-run previews, history logging, and manual overrides.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/new-feature`
3. Make changes and test thoroughly
4. Run code style fixer: `vendor/bin/pint`
5. Ensure tests pass: `php artisan test`
6. Commit with clear messages
7. Push and create a Pull Request

## 📄 License

Proprietary software for university use. All rights reserved.

## 💬 Support

For issues or questions:
- Check `/documentation` for detailed guides
- Review test files for usage examples
- Contact university IT department

---

**Version**: 1.0.0  
**Last Updated**: January 2026  
**Maintained By**: University IT Department
