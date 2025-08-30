@echo off
echo ========================================
echo    COMPREHENSIVE TEST SUITE RUNNER
echo ========================================
echo.

echo Starting comprehensive test execution...
echo.

echo [1/6] Running System Integration Tests...
php artisan test tests/Feature/ComprehensiveSystemTest.php --verbose
if %errorlevel% neq 0 (
    echo ERROR: System tests failed!
    pause
    exit /b 1
)
echo.

echo [2/6] Running Model Unit Tests...
php artisan test tests/Unit/ComprehensiveModelTest.php --verbose
if %errorlevel% neq 0 (
    echo ERROR: Model tests failed!
    pause
    exit /b 1
)
echo.

echo [3/6] Running API Integration Tests...
php artisan test tests/Feature/ComprehensiveApiTest.php --verbose
if %errorlevel% neq 0 (
    echo ERROR: API tests failed!
    pause
    exit /b 1
)
echo.

echo [4/6] Running Security Tests...
php artisan test tests/Feature/ComprehensiveSecurityTest.php --verbose
if %errorlevel% neq 0 (
    echo ERROR: Security tests failed!
    pause
    exit /b 1
)
echo.

echo [5/6] Running Existing Feature Tests...
php artisan test tests/Feature/ --exclude-group=comprehensive --verbose
if %errorlevel% neq 0 (
    echo ERROR: Feature tests failed!
    pause
    exit /b 1
)
echo.

echo [6/6] Running Existing Unit Tests...
php artisan test tests/Unit/ --exclude-group=comprehensive --verbose
if %errorlevel% neq 0 (
    echo ERROR: Unit tests failed!
    pause
    exit /b 1
)
echo.

echo ========================================
echo    ALL TESTS COMPLETED SUCCESSFULLY!
echo ========================================
echo.
echo Test Coverage Summary:
echo - Authentication & Authorization
echo - User Role Management
echo - Admin Panel Functionality
echo - Advisor Operations
echo - Supervisor Management
echo - Student Dashboard
echo - Group Management
echo - Area of Interest CRUD
echo - Batch Management
echo - Meeting Management
echo - File Upload/Download
echo - Excel Import/Export
echo - PDF Generation
echo - Performance Monitoring
echo - Security Validations
echo - Rate Limiting
echo - Database Integrity
echo - Model Relationships
echo - API Endpoints
echo - Middleware Protection
echo.

echo Running full test suite with coverage...
php artisan test --coverage --min=80
if %errorlevel% neq 0 (
    echo WARNING: Test coverage below 80%
)

echo.
echo Test execution completed!
pause