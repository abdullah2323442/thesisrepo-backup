# University Thesis Repository - Complete Setup & Running Guide

## 📋 Table of Contents

1. [System Requirements](#system-requirements)
2. [Installation Steps](#installation-steps)
3. [Environment Configuration](#environment-configuration)
4. [Database Setup](#database-setup)
5. [External API Configuration](#external-api-configuration)
6. [Running the Application](#running-the-application)
7. [Testing](#testing)
8. [User Accounts & Demo Data](#user-accounts--demo-data)
9. [Troubleshooting](#troubleshooting)
10. [Production Deployment](#production-deployment)

## 🔧 System Requirements

### Minimum Requirements
- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Node.js**: 18.x or higher
- **NPM**: 9.x or higher
- **Database**: MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.8+
- **Web Server**: Apache 2.4+ / Nginx 1.18+

### Recommended Development Environment
- **PHP**: 8.3
- **Memory**: 512MB minimum, 1GB recommended
- **Storage**: 2GB free space
- **Extensions**: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

### Required PHP Extensions
```bash
# Check if extensions are installed
php -m | grep -E "(bcmath|ctype|fileinfo|json|mbstring|openssl|pdo|tokenizer|xml)"
```

## 🚀 Installation Steps

### 1. Clone the Repository
```bash
git clone <repository-url>
cd thesisrepo
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node.js Dependencies
```bash
npm install
```

### 4. Copy Environment File
```bash
# Windows
copy .env.example .env

# Linux/Mac
cp .env.example .env
```

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Create Storage Link
```bash
php artisan storage:link
```

## ⚙️ Environment Configuration

### Basic Configuration (.env)

```env
# Application
APP_NAME="University Thesis Repository"
APP_ENV=local
APP_KEY=base64:your-generated-key-here
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thesis_repo
DB_USERNAME=root
DB_PASSWORD=your_password_here

# Mail Configuration (for password reset)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

# Cache Configuration
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### External API Configuration (.env)

```env
# External API Endpoints
EXTERNAL_API_BASE_URL=https://your-university-api.edu
EXTERNAL_API_LOGIN_URL=https://your-university-api.edu/api/Login/LoginAction
EXTERNAL_API_TEACHER_LOGIN_URL=https://your-university-api.edu/api/Teacher/Login
EXTERNAL_API_STUDENT_URL=https://your-university-api.edu/api/Student
EXTERNAL_API_BATCH_URL=https://your-university-api.edu/api/Batch

# API Rate Limiting (requests per minute)
EXTERNAL_API_LOGIN_MAX_ATTEMPTS=5
EXTERNAL_API_STUDENT_DASHBOARD_MAX_ATTEMPTS=60
EXTERNAL_API_ADVISOR_DASHBOARD_MAX_ATTEMPTS=60
EXTERNAL_API_ADVISOR_STUDENTS_MAX_ATTEMPTS=60
EXTERNAL_API_ADMIN_SUPERVISORS_SYNC_MAX_ATTEMPTS=10
```

## 🗄️ Database Setup

### 1. Create Database
```sql
-- MySQL
CREATE DATABASE thesis_repo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- PostgreSQL
CREATE DATABASE thesis_repo WITH ENCODING 'UTF8';
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Seed Database (Optional)
```bash
# Seed with demo data
php artisan db:seed

# Or seed specific seeders
php artisan db:seed --class=StudentDashboardDemoSeeder
```

### 4. Alternative: Fresh Migration with Seeding
```bash
php artisan migrate:fresh --seed
```

## 🔌 External API Configuration

### Setting Up External API Integration

1. **Contact your university IT department** for API endpoints and credentials
2. **Update .env file** with correct API URLs
3. **Test API connectivity**:

```bash
# Test API endpoints
php artisan tinker
>>> Http::get(env('EXTERNAL_API_BASE_URL') . '/health')
```

### API Endpoint Structure
The application expects these external API endpoints:

- **Student Login**: `POST /api/Login/LoginAction`
- **Teacher Login**: `POST /api/Teacher/Login`
- **Student Data**: `GET /api/Student/{id}`
- **Batch Data**: `GET /api/Batch`
- **Supervisor Data**: `GET /api/Supervisor`

## 🏃‍♂️ Running the Application

### Development Server

#### Option 1: Laravel Artisan (Recommended for Development)
```bash
# Start the development server
php artisan serve

# Custom host and port
php artisan serve --host=0.0.0.0 --port=8080
```

#### Option 2: Build Assets and Serve
```bash
# Build frontend assets
npm run build

# Start server
php artisan serve
```

#### Option 3: Development with Hot Reload
```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite dev server (in another terminal)
npm run dev
```

### Production Server

#### Build for Production
```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Build optimized assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Web Server Configuration

**Apache (.htaccess)**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**Nginx**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/thesisrepo/public;

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
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Only Working Tests (Recommended)
```bash
php artisan test tests/Feature/LogoutFunctionalityTest.php tests/Feature/StudentDashboardTest.php tests/Feature/ComprehensiveApiRateLimitingTest.php tests/Feature/ExternalApiRateLimitingTest.php tests/Feature/ProfileTest.php
```

### Run Authentication Tests
```bash
php artisan test tests/Feature/Auth/EmailVerificationTest.php tests/Feature/Auth/PasswordConfirmationTest.php tests/Feature/Auth/PasswordResetTest.php tests/Feature/Auth/PasswordUpdateTest.php tests/Feature/Auth/RegistrationTest.php
```

### Run Tests with Coverage
```bash
php artisan test --coverage
```

### Test Database Setup
```bash
# Create test database
php artisan migrate --env=testing

# Run tests with fresh database
php artisan test --recreate-databases
```

## 👥 User Accounts & Demo Data

### Default Admin Account
```
Email: admin@example.com
Password: password
Role: Administrator
```

### Demo Student Accounts
```
Email: john.student@example.com
Password: password
Role: Student
Group: Group 1 (with Jane and Mike)

Email: jane.student@example.com
Password: password
Role: Student
Group: Group 1 (with John and Mike)

Email: sarah.student@example.com
Password: password
Role: Student
Group: Group 2 (solo)
```

### Demo Teacher Accounts
```
Email: teacher@example.com
Password: password
Role: Teacher/Advisor

Email: supervisor@example.com
Password: password
Role: Supervisor
```

### Creating New Users
```bash
# Create user via tinker
php artisan tinker
>>> User::factory()->create(['email' => 'new@example.com', 'login_type' => 'student'])
```

## 🔧 Troubleshooting

### Common Issues

#### 1. Permission Errors
```bash
# Fix storage permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Windows (run as administrator)
icacls storage /grant Everyone:F /T
icacls bootstrap/cache /grant Everyone:F /T
```

#### 2. Database Connection Issues
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo()

# Check database configuration
php artisan config:show database
```

#### 3. External API Connection Issues
```bash
# Test API connectivity
php artisan tinker
>>> Http::timeout(10)->get(env('EXTERNAL_API_BASE_URL'))

# Check SSL certificate issues
>>> Http::withoutVerifying()->get(env('EXTERNAL_API_BASE_URL'))
```

#### 4. Asset Compilation Issues
```bash
# Clear npm cache
npm cache clean --force

# Reinstall node modules
rm -rf node_modules package-lock.json
npm install

# Rebuild assets
npm run build
```

#### 5. Laravel Cache Issues
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Clear compiled views
php artisan view:clear
```

### Debug Mode
```env
# Enable debug mode in .env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Log Files
```bash
# View logs
tail -f storage/logs/laravel.log

# Clear logs
> storage/logs/laravel.log
```

## 🚀 Production Deployment

### Pre-Deployment Checklist

1. **Environment Configuration**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   ```

2. **Security Settings**
   ```env
   SESSION_SECURE_COOKIE=true
   SESSION_SAME_SITE=strict
   SANCTUM_STATEFUL_DOMAINS=your-domain.com
   ```

3. **Database Optimization**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

4. **Performance Optimization**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   npm run build
   ```

### Deployment Steps

1. **Upload Files**
   ```bash
   # Upload to server (excluding development files)
   rsync -av --exclude 'node_modules' --exclude '.git' ./ user@server:/path/to/app/
   ```

2. **Set Permissions**
   ```bash
   chmod -R 755 /path/to/app
   chmod -R 775 /path/to/app/storage
   chmod -R 775 /path/to/app/bootstrap/cache
   ```

3. **Configure Web Server**
   - Point document root to `/path/to/app/public`
   - Configure SSL certificate
   - Set up proper redirects

4. **Set Up Cron Jobs**
   ```bash
   # Add to crontab
   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
   ```

5. **Configure Queue Workers** (if using queues)
   ```bash
   # Install supervisor
   sudo apt install supervisor

   # Configure worker
   sudo nano /etc/supervisor/conf.d/laravel-worker.conf
   ```

### Health Checks

```bash
# Check application status
curl -I https://your-domain.com

# Check database connectivity
php artisan tinker
>>> DB::connection()->getPdo()

# Check external API connectivity
>>> Http::get(env('EXTERNAL_API_BASE_URL'))
```

### Monitoring

1. **Log Monitoring**
   - Set up log rotation
   - Monitor error logs
   - Set up alerts for critical errors

2. **Performance Monitoring**
   - Monitor response times
   - Track database query performance
   - Monitor external API response times

3. **Security Monitoring**
   - Monitor failed login attempts
   - Track rate limiting violations
   - Monitor for suspicious activity

## 📚 Additional Resources

### Documentation Files
- **[README.md](README.md)**: Project overview and quick start
- **[FINAL_TEST_REPORT.md](FINAL_TEST_REPORT.md)**: Comprehensive test coverage report
- **[COMPREHENSIVE_TEST_SUMMARY.md](COMPREHENSIVE_TEST_SUMMARY.md)**: Detailed testing documentation
- **[API_RATE_LIMITING_SUMMARY.md](API_RATE_LIMITING_SUMMARY.md)**: Rate limiting configuration
- **[EXCEL_UPLOAD_GUIDE.md](EXCEL_UPLOAD_GUIDE.md)**: Excel file upload instructions
- **[supervisor_assignment_algo.md](supervisor_assignment_algo.md)**: Supervisor assignment algorithm
- **[secure.md](secure.md)**: Security hardening guide

### Support Channels
- **GitHub Issues**: Report bugs and request features
- **Documentation**: Comprehensive guides and API documentation
- **Testing**: Run test suite for validation
- **Community**: Laravel community resources

### Development Tools
```bash
# Laravel Telescope (for debugging)
composer require laravel/telescope --dev
php artisan telescope:install

# Laravel Debugbar (for development)
composer require barryvdh/laravel-debugbar --dev

# IDE Helper (for better IDE support)
composer require barryvdh/laravel-ide-helper --dev
php artisan ide-helper:generate
```

---

**🎯 Quick Start Summary:**
1. `git clone <repo> && cd thesisrepo`
2. `composer install && npm install`
3. `cp .env.example .env && php artisan key:generate`
4. Configure database in `.env`
5. `php artisan migrate --seed`
6. `npm run build && php artisan serve`
7. Visit `http://localhost:8000`

**✅ Verification:**
- Run tests: `php artisan test tests/Feature/LogoutFunctionalityTest.php tests/Feature/StudentDashboardTest.php tests/Feature/ComprehensiveApiRateLimitingTest.php tests/Feature/ExternalApiRateLimitingTest.php tests/Feature/ProfileTest.php`
- Login with demo accounts
- Check all user panels work correctly