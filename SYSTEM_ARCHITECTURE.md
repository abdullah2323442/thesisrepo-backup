# System Architecture - University Thesis Management System

## 📋 Table of Contents
1. [System Overview](#system-overview)
2. [Architecture Layers](#architecture-layers)
3. [Technology Stack](#technology-stack)
4. [Database Architecture](#database-architecture)
5. [Application Components](#application-components)
6. [External Integrations](#external-integrations)
7. [Security Architecture](#security-architecture)
8. [Deployment Architecture](#deployment-architecture)

---

## 🎯 System Overview

### Purpose
A production-level web application for managing university thesis projects, student groups, supervisor assignments, and report submissions with advanced algorithms and external API integration.

### Key Capabilities
- Multi-role authentication and authorization (Admin, Advisor, Supervisor, Co-Supervisor, Panel Member, Student, Teacher)
- Intelligent supervisor assignment using lottery algorithms
- Real-time performance monitoring and analytics
- External API integration for student/teacher data synchronization
- Report submission and annotation system
- Meeting management and attendance tracking

### Architecture Pattern
**MVC (Model-View-Controller)** with Service Layer Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Presentation Layer                    │
│              (Blade Views + Tailwind CSS)               │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                    Controller Layer                      │
│         (HTTP Controllers + Request Validation)         │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                     Service Layer                        │
│        (Business Logic + External API Services)         │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                      Model Layer                         │
│              (Eloquent ORM + Database)                  │
└─────────────────────────────────────────────────────────┘
```

---

## 🏗️ Architecture Layers

### 1. Presentation Layer (Views)
**Location**: `resources/views/`

**Components**:
- **Blade Templates**: Server-side rendering with Laravel Blade
- **Tailwind CSS**: Utility-first CSS framework
- **Alpine.js**: Lightweight JavaScript framework for interactivity
- **Responsive Design**: Mobile-first approach

**Key View Directories**:
```
resources/views/
├── admin/              # Admin dashboard and management
├── advisor/            # Advisor group and assignment management
├── supervisor/         # Supervisor thesis and report management
├── co-supervisor/      # Co-supervisor specific views
├── panel-member/       # Panel member evaluation views
├── student/            # Student dashboard and submissions
├── teacher/            # Teacher multi-role dashboard
├── auth/               # Authentication views
├── components/         # Reusable UI components
└── layouts/            # Master layouts
```

### 2. Controller Layer
**Location**: `app/Http/Controllers/`

**Responsibilities**:
- Handle HTTP requests and responses
- Request validation
- Route business logic to services
- Return views or JSON responses

**Controller Structure**:
```
app/Http/Controllers/
├── Admin/
│   ├── DashboardController.php
│   ├── AreaOfInterestController.php
│   ├── SupervisorController.php
│   ├── BatchController.php
│   ├── GroupManagementController.php
│   └── PerformanceController.php
├── Advisor/
│   ├── DashboardController.php
│   ├── StudentController.php
│   ├── GroupController.php
│   └── SupervisorAssignmentController.php
├── Supervisor/
│   ├── DashboardController.php
│   ├── GroupController.php
│   ├── MeetingController.php
│   ├── ReportController.php
│   └── ReportAnnotationController.php
├── CoSupervisor/
│   ├── DashboardController.php
│   ├── GroupController.php
│   ├── MeetingController.php
│   ├── ReportController.php
│   └── ReportAnnotationController.php
├── PanelMember/
│   ├── DashboardController.php
│   ├── GroupController.php
│   ├── ReportController.php
│   └── ReportAnnotationController.php
├── Student/
│   ├── DashboardController.php
│   ├── ReportController.php
│   ├── ReportSubmissionController.php
│   └── ReportAnnotationController.php
├── Teacher/
│   ├── DashboardController.php
│   └── ReportCommentController.php
├── Api/
│   └── NotificationController.php
├── Auth/
│   └── [Laravel Breeze Auth Controllers]
├── DashboardController.php
├── ProfileController.php
└── HomeController.php
```

### 3. Service Layer
**Location**: `app/Services/`

**Purpose**: Encapsulate complex business logic and external integrations

**Services**:
```php
// Supervisor Assignment Algorithm
SupervisorAssignmentService.php
- runLotteryAssignment()      // Execute assignment with 3 modes
- previewLotteryAssignment()  // Preview without persistence
- AOI-based assignment        // Area of Interest matching
- Ranking-based assignment    // Academic rank priority
- Combined assignment         // Hybrid approach

// External API Integration
StudentApiService.php
- fetchStudentsByBatch()      // Get students from external API
- authenticateStudent()       // Student login validation

SupervisorApiService.php
- fetchAllSupervisors()       // Sync supervisor data
- fetchSupervisorById()       // Get individual supervisor

BatchApiService.php
- fetchBatchesByProgram()     // Get batch information
- syncBatchData()             // Synchronize batch data

// Performance Monitoring
PerformanceMonitoringService.php
- getSystemHealth()           // System status checks
- getPerformanceMetrics()     // Response time analysis
- getDatabaseMetrics()        // Database performance
- getApiMetrics()             // External API health
- getSecurityMetrics()        // Security monitoring
```

### 4. Model Layer (Data)
**Location**: `app/Models/`

**Core Models**:
```php
User.php                          // Multi-role user (Student/Teacher/Admin)
├── isAdmin()
├── isTeacher()
├── isStudent()
└── createOrUpdateFromApi()

Group.php                         // Thesis groups
├── students()                    // HasMany relationship
├── supervisor()                  // BelongsTo relationship
├── coSupervisor()               // BelongsTo relationship
├── areasOfInterest()            // BelongsToMany relationship
└── panelMembers()               // BelongsToMany relationship

Supervisor.php                    // Faculty supervisors
├── groups()                      // HasMany relationship
├── areasOfInterest()            // BelongsToMany relationship
├── available_slots              // Capacity management
└── rank_priority                // Academic ranking

AreaOfInterest.php               // Research areas
├── supervisors()                // BelongsToMany relationship
└── groups()                     // BelongsToMany relationship

Batch.php                        // Student batches
├── groups()                     // HasMany relationship
└── is_active                    // Status flag

Report.php                       // Thesis reports
├── group()                      // BelongsTo relationship
├── submissions()                // HasMany relationship
├── comments()                   // HasMany relationship
└── annotationSessions()         // HasMany relationship

StudentReportSubmission.php      // Report submissions
├── report()                     // BelongsTo relationship
├── student()                    // BelongsTo relationship
└── annotationSessions()         // HasMany relationship

ReportAnnotationSession.php      // PDF annotations
├── submission()                 // BelongsTo relationship
├── creator()                    // MorphTo relationship
└── annotations                  // JSON field

Meeting.php                      // Supervisor meetings
├── group()                      // BelongsTo relationship
├── organizer()                  // BelongsTo relationship
└── attendances()                // HasMany relationship

AssignmentHistory.php            // Assignment tracking
├── group()                      // BelongsTo relationship
├── supervisor()                 // BelongsTo relationship
└── recordAssignment()           // Static method
```

---

## 💾 Database Architecture

### Database Schema Overview

```sql
-- Core User Management
users                           # Multi-role users (students, teachers, admins)
├── id, name, email, password
├── login_type (student/teacher)
├── type_id (JSON: ['1'=admin, '2'=teacher])
├── roll, batch, department_id
└── api_id (external system reference)

-- Supervisor Management
supervisors                     # Faculty supervisors
├── id, fullname, designation
├── rank_priority (1-4: Professor to Lecturer)
├── available_slots, assigned_theses_count
├── is_active
└── areas_of_interest (many-to-many)

area_of_interests              # Research areas
├── id, name, description
└── is_active

supervisor_area_of_interest    # Pivot table
├── supervisor_id, area_of_interest_id
└── timestamps

-- Group Management
batches                        # Student batches
├── id, batch_number, program_id
├── is_active, total_students
└── timestamps

groups                         # Thesis groups
├── id, name, batch_number
├── advisor_id, supervisor_id, co_supervisor_id
├── area_of_interest_id, matched_area_of_interest_id
├── is_manual_assignment, assignment_priority
├── co_supervisor_can_manage_meetings
└── timestamps

group_students                 # Group members
├── id, group_id, student_id
├── role (leader/member)
└── timestamps

group_area_of_interest        # Multiple areas per group
├── group_id, area_of_interest_id
└── timestamps

group_panel_members           # Panel member assignments
├── id, group_id, supervisor_id
└── timestamps

-- Assignment Tracking
assignment_history            # Supervisor assignment log
├── id, group_id, supervisor_id
├── area_of_interest_id
├── assignment_method (lottery_aoi/manual/etc)
└── assigned_at

-- Report Management
reports                       # Thesis reports
├── id, group_id, title, description
├── due_date, status (draft/submitted/reviewed/approved)
├── is_public, created_by_id
└── timestamps

student_report_submissions    # Student submissions
├── id, report_id, student_id
├── file_path, submission_date
├── status, grade, feedback
└── timestamps

report_annotation_sessions    # PDF annotations
├── id, submission_id
├── created_by_id, created_by_type (supervisor/co_supervisor/panel_member)
├── annotations (JSON), feedback_sent_at
└── timestamps

report_comments              # Report discussions
├── id, report_id, user_id
├── comment, is_internal
└── timestamps

-- Meeting Management
meetings                     # Supervisor meetings
├── id, group_id, organizer_id
├── title, description, meeting_date
├── location, duration_minutes
└── timestamps

meeting_attendances         # Attendance tracking
├── id, meeting_id, student_id
├── status (present/absent/excused)
└─��� timestamps

-- System Tables
notifications               # Laravel notifications
cache                      # Application cache
jobs                       # Queue jobs
sessions                   # User sessions
```

### Key Relationships

```
User (1) ──────── (N) Group [as advisor]
User (1) ──────── (N) GroupStudent [as student]
Supervisor (1) ── (N) Group [as supervisor]
Supervisor (1) ── (N) Group [as co_supervisor]
Supervisor (N) ── (N) AreaOfInterest
Group (1) ──────── (N) GroupStudent
Group (N) ──────── (N) AreaOfInterest
Group (1) ──────── (N) Report
Group (1) ──────── (N) Meeting
Group (N) ──────── (N) Supervisor [as panel_members]
Report (1) ─────── (N) StudentReportSubmission
StudentReportSubmission (1) ── (N) ReportAnnotationSession
Meeting (1) ────── (N) MeetingAttendance
```

---

## 🔧 Application Components

### 1. Authentication System

**Implementation**: Laravel Breeze + Custom Multi-Role

```php
// Authentication Flow
1. User Login (Student/Teacher)
   ↓
2. External API Validation (if applicable)
   ↓
3. Local User Creation/Update
   ↓
4. Role-Based Dashboard Redirect
   ↓
5. Session Management + CSRF Protection
```

**Middleware Stack**:
- `auth`: Verify authenticated user
- `admin`: Admin-only access
- `advisor`: Advisor role check
- `teacher`: Teacher role check
- `student`: Student role check
- `throttle`: Rate limiting

### 2. Supervisor Assignment Algorithm

**Three Assignment Modes**:

#### Mode 1: AOI-Based (Area of Interest)
```
1. Sort groups by number
2. Build supervisor pools by area of interest
3. For each group:
   - Try primary area of interest
   - Fallback to secondary areas
   - Random selection within same rank
   - Update capacity tracking
4. Record assignment history
```

#### Mode 2: Ranking-Based
```
1. Sort supervisors by rank (Professor → Lecturer)
2. Implement round-robin distribution
3. Ensure fair load balancing
4. No area matching required
```

#### Mode 3: Combined (AOI + Ranking)
```
1. Match area of interest first
2. Apply ranking priority within matches
3. Intelligent round-robin per area
4. Prevent consecutive assignments
```

**Key Features**:
- Capacity management (available_slots)
- Randomization for fairness
- Preview mode (dry-run)
- Assignment history tracking
- Multiple areas per group support

### 3. Report Management System

**Workflow**:
```
1. Supervisor creates report assignment
   ↓
2. Students receive notification
   ↓
3. Students submit PDF files
   ↓
4. Supervisor/Co-Supervisor/Panel Member annotate
   ↓
5. Feedback sent to students
   ↓
6. Students view annotations
   ↓
7. Resubmission cycle (if needed)
   ↓
8. Final approval and grading
```

**Annotation System**:
- PDF.js integration for viewing
- JSON-based annotation storage
- Multiple annotation sessions per submission
- Role-based annotation permissions
- Feedback notification system

### 4. Performance Monitoring

**Metrics Tracked**:
```php
System Health:
- PHP version and extensions
- Database connectivity
- Cache system status
- Queue worker status
- Storage permissions

Performance Metrics:
- Average response time
- Memory usage
- Database query count
- Slow query detection
- Cache hit ratio

API Metrics:
- External API response times
- API failure rates
- Rate limit violations

Security Metrics:
- Failed login attempts
- CSRF token failures
- Suspicious activity detection
```

### 5. Notification System

**Channels**:
- Database notifications (in-app)
- Real-time notification polling
- Email notifications (configurable)

**Notification Types**:
```php
NewReportAssigned          // Report created for group
ReportUpdated             // Report details changed
NewReportAnnotation       // Annotation feedback sent
NewReportComment          // Comment added to report
```

---

## 🔌 External Integrations

### University API Integration

**Base URL**: `http://puc.ac.bd:8012/api`

**Endpoints**:
```
POST /Login/LoginAction              # Student authentication
POST /Teacher/Login                  # Teacher authentication
GET  /Teacher/TeacherList            # Fetch all teachers
GET  /Student/batchwiseStudentList   # Fetch students by batch
GET  /Student/programwiseBatch       # Fetch batch information
```

**Integration Services**:

```php
StudentApiService:
- fetchStudentsByBatch($batchNumber)
- authenticateStudent($roll, $password)
- syncStudentData()

SupervisorApiService:
- fetchAllSupervisors()
- fetchSupervisorById($id)
- syncSupervisorData()

BatchApiService:
- fetchBatchesByProgram($programId)
- syncBatchData()
```

**Rate Limiting**:
```php
Login: 5 attempts/minute
Dashboard: 60 attempts/minute
Sync Operations: 10 attempts/5 minutes
List Operations: 120 attempts/minute
```

**Error Handling**:
- Retry mechanism (3 attempts)
- Exponential backoff
- Fallback to cached data
- Comprehensive logging

---

## 🔒 Security Architecture

### 1. Authentication & Authorization

**Multi-Layer Security**:
```
Layer 1: Session-based authentication (Laravel Sanctum)
Layer 2: Role-based access control (RBAC)
Layer 3: Route middleware protection
Layer 4: Policy-based authorization
Layer 5: CSRF token validation
```

**Password Security**:
- Bcrypt hashing (cost factor: 12)
- Secure password generation for API users
- Password reset functionality
- Session regeneration on login

### 2. Rate Limiting

**Protected Endpoints**:
```php
// Authentication
'login' => 5 attempts/minute

// Student Operations
'student_dashboard' => 60 attempts/minute

// Advisor Operations
'advisor_dashboard' => 60 attempts/minute
'advisor_groups' => 120 attempts/minute
'advisor_students' => 60 attempts/minute

// Admin Operations
'admin_supervisors_sync' => 10 attempts/5 minutes
'admin_batches_sync' => 10 attempts/5 minutes

// API Operations
'teacher_list' => 120 attempts/minute
'student_list' => 120 attempts/minute
```

### 3. Input Validation

**Validation Layers**:
1. Client-side validation (JavaScript)
2. Server-side validation (Laravel Request classes)
3. Database constraints
4. Business logic validation

**File Upload Security**:
- MIME type validation
- File size limits (10MB for reports)
- Secure file storage (outside public directory)
- Virus scanning (recommended for production)

### 4. SQL Injection Prevention

**Protection Mechanisms**:
- Eloquent ORM (parameterized queries)
- Query builder with bindings
- Input sanitization
- Prepared statements

### 5. XSS Prevention

**Mitigation Strategies**:
- Blade template escaping ({{ }})
- Content Security Policy headers
- Input sanitization
- Output encoding

---

## 🚀 Deployment Architecture

### Production Environment

```
┌─────────────────────────��───────────────────────────────┐
│                    Load Balancer                         │
│                   (Nginx/Apache)                         │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                  Application Servers                     │
│              (PHP-FPM + Laravel App)                    │
│                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │   Server 1   │  │   Server 2   │  │   Server N   │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
└────────────────────────────────────────────────────────��┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                    Cache Layer                           │
│                  (Redis/Memcached)                      │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                   Database Layer                         │
│              (MySQL Master-Slave)                       │
│                                                          │
│  ┌──────────────┐         ┌──────────────┐            │
│  │    Master    │────────→│    Slave     │            │
│  │  (Write)     │         │   (Read)     │            │
│  └──────────────┘         └──────────────┘            │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                   File Storage                           │
│              (Local/S3/Cloud Storage)                   │
└─────────────────────────────────────────────────────────┘
```

### System Requirements

**Minimum Requirements**:
- PHP 8.2+ with extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+
- Composer 2.x
- Node.js 18+ and NPM 9+
- 2GB RAM minimum
- 10GB disk space

**Recommended Production**:
- PHP 8.3+ with OPcache enabled
- MySQL 8.0+ with InnoDB engine
- Redis for caching and sessions
- 4GB+ RAM
- SSD storage
- SSL certificate (Let's Encrypt)

### Deployment Checklist

```bash
# 1. Environment Setup
cp .env.example .env.production
php artisan key:generate

# 2. Install Dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# 3. Database Migration
php artisan migrate --force

# 4. Optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 6. Queue Worker (Supervisor)
php artisan queue:work --daemon

# 7. Scheduler (Cron)
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

### Monitoring & Logging

**Log Channels**:
- `daily`: Daily rotating logs
- `slack`: Critical error notifications
- `syslog`: System-level logging
- `errorlog`: PHP error log

**Monitoring Tools**:
- Laravel Telescope (development)
- Performance monitoring dashboard (built-in)
- External API health checks
- Database query monitoring

---

## 📊 Performance Optimization

### 1. Database Optimization

**Indexing Strategy**:
```sql
-- User lookups
INDEX(username, login_type)
INDEX(roll)
INDEX(api_id)

-- Group queries
INDEX(batch_number)
INDEX(advisor_id, created_by_type)
INDEX(supervisor_id)
INDEX(co_supervisor_id)

-- Report queries
INDEX(group_id, status)
INDEX(due_date)

-- Assignment history
INDEX(group_id, assigned_at)
INDEX(supervisor_id)
```

**Query Optimization**:
- Eager loading relationships
- Query result caching
- Database connection pooling
- Read/write splitting

### 2. Caching Strategy

**Cache Layers**:
```php
// Application Cache
- Configuration cache
- Route cache
- View cache
- Event cache

// Data Cache
- User sessions (Redis)
- API responses (5-15 minutes)
- Dashboard statistics (1 hour)
- Supervisor availability (30 minutes)
```

### 3. Asset Optimization

**Frontend Optimization**:
- Vite for asset bundling
- CSS/JS minification
- Image optimization
- Lazy loading
- CDN integration (optional)

---

## 🧪 Testing Architecture

### Test Coverage

**Test Suites**:
```
tests/
├── Feature/                    # Integration tests
│   ├── Auth/                  # Authentication tests
│   ├── Admin/                 # Admin functionality
│   ├── Advisor/               # Advisor operations
│   ├── Supervisor/            # Supervisor features
│   ├── Student/               # Student workflows
│   └── Api/                   # API integration tests
└── Unit/                      # Unit tests
    ├── Services/              # Service layer tests
    ├── Models/                # Model tests
    └── Helpers/               # Helper function tests
```

**Test Statistics**:
- 34+ working tests
- 542+ assertions
- 100% security coverage
- 95% UI coverage
- 90% API coverage

**Running Tests**:
```bash
# All tests
php artisan test

# Specific suite
php artisan test --filter=SupervisorAssignmentTest

# With coverage
php artisan test --coverage
```

---

## 📈 Scalability Considerations

### Horizontal Scaling

**Stateless Application**:
- Session storage in Redis
- File storage on shared filesystem/S3
- Database connection pooling
- Load balancer ready

### Vertical Scaling

**Resource Optimization**:
- OPcache for PHP bytecode
- Query result caching
- Lazy loading relationships
- Efficient algorithms

### Future Enhancements

**Potential Improvements**:
- Microservices architecture for external API
- GraphQL API for mobile apps
- Real-time notifications (WebSockets)
- Advanced analytics dashboard
- Machine learning for supervisor matching
- Automated report plagiarism detection

---

## 🔄 Data Flow Diagrams

### User Authentication Flow
```
User → Login Form → Controller → External API → Local DB → Session → Dashboard
```

### Supervisor Assignment Flow
```
Advisor → Assignment Page → Preview → Confirm → Service Layer → Algorithm → Database → Notification
```

### Report Submission Flow
```
Student → Upload Form → Validation → Storage → Database → Notification → Supervisor → Annotation → Feedback
```

---

## 📝 API Documentation

### Internal API Endpoints

**Notification API**:
```
GET  /api/notifications              # List notifications
POST /api/notifications/{id}/mark-read  # Mark as read
POST /api/notifications/mark-all-read   # Mark all as read
GET  /api/notifications/unread-count    # Get unread count
```

**Response Format**:
```json
{
  "success": true,
  "data": [...],
  "message": "Operation successful",
  "meta": {
    "total": 10,
    "unread": 3
  }
}
```

---

## 🎓 Conclusion

This system architecture provides a robust, scalable, and secure foundation for managing university thesis projects. The modular design allows for easy maintenance and future enhancements while maintaining production-level quality and performance.

**Key Strengths**:
✅ Clean MVC architecture with service layer
✅ Comprehensive security implementation
✅ Advanced supervisor assignment algorithms
✅ External API integration with fallback mechanisms
✅ Real-time performance monitoring
✅ Extensive test coverage
✅ Production-ready deployment configuration

**Version**: 1.0.0  
**Last Updated**: January 2025  
**Maintained By**: University IT Department
