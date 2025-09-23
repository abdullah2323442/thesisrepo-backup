# Authentication & Authorization Flow

```mermaid
flowchart TD
    Start([User Access]) --> LoginPage[Login Page]
    LoginPage --> EnterCreds[Enter Credentials]
    EnterCreds --> SubmitForm[Submit Login Form]
    
    SubmitForm --> ValidateInput{Validate Input}
    ValidateInput -->|Invalid| ShowErrors[Show Validation Errors]
    ShowErrors --> EnterCreds
    
    ValidateInput -->|Valid| AuthAttempt["Auth::attempt()"]
    AuthAttempt --> CheckUser{User Exists?}
    
    CheckUser -->|No| InvalidCreds[Invalid Credentials Error]
    InvalidCreds --> EnterCreds
    
    CheckUser -->|Yes| VerifyPassword[Verify Password Hash]
    VerifyPassword --> PasswordMatch{Password Correct?}
    
    PasswordMatch -->|No| InvalidCreds
    PasswordMatch -->|Yes| CreateSession[Create Session]
    
    CreateSession --> RegenerateToken[Regenerate CSRF Token]
    RegenerateToken --> CheckRole[Check User Role]
    
    CheckRole --> RoleType{User Role}
    
    RoleType -->|admin| AdminMiddleware[Apply Admin Middleware]
    RoleType -->|student| StudentMiddleware[Apply Student Middleware]
    RoleType -->|supervisor| SupervisorMiddleware[Apply Supervisor Middleware]
    RoleType -->|advisor| AdvisorMiddleware[Apply Advisor Middleware]
    RoleType -->|teacher| TeacherMiddleware[Apply Teacher Middleware]
    RoleType -->|co-supervisor| CoSupervisorMiddleware[Apply Co-Supervisor Middleware]
    RoleType -->|panel-member| PanelMiddleware[Apply Panel Member Middleware]
    
    AdminMiddleware --> AdminDashboard[Redirect to /admin/dashboard]
    StudentMiddleware --> CheckGroupAssignment{Has GroupStudent Entry?}
    SupervisorMiddleware --> SupervisorDashboard[Redirect to /supervisor/dashboard]
    AdvisorMiddleware --> AdvisorDashboard[Redirect to /advisor/dashboard]
    TeacherMiddleware --> TeacherDashboard[Redirect to /teacher/dashboard]
    CoSupervisorMiddleware --> CoSupervisorDashboard[Redirect to /co-supervisor/dashboard]
    PanelMiddleware --> PanelDashboard[Redirect to /panel-member/dashboard]
    
    CheckGroupAssignment -->|Yes| StudentDashboard[Redirect to /student/dashboard]
    CheckGroupAssignment -->|No| PendingAssignment[Show Pending Assignment View]
    
    AdminDashboard --> RouteProtection[Route Protection Active]
    StudentDashboard --> RouteProtection
    SupervisorDashboard --> RouteProtection
    AdvisorDashboard --> RouteProtection
    TeacherDashboard --> RouteProtection
    CoSupervisorDashboard --> RouteProtection
    PanelDashboard --> RouteProtection
    PendingAssignment --> RouteProtection
    
    RouteProtection --> AccessRequest[User Requests Route]
    AccessRequest --> CheckAuth{Authenticated?}
    
    CheckAuth -->|No| RedirectLogin[Redirect to Login]
    RedirectLogin --> LoginPage
    
    CheckAuth -->|Yes| CheckPermission{Has Permission?}
    CheckPermission -->|No| Show403[Show 403 Forbidden]
    CheckPermission -->|Yes| AllowAccess[Allow Route Access]
    
    AllowAccess --> End([Access Granted])
    Show403 --> End2([Access Denied])
    
    style Start fill:#4CAF50,color:#fff
    style End fill:#4CAF50,color:#fff
    style End2 fill:#f44336,color:#fff
    style ValidateInput fill:#FFE082
    style CheckUser fill:#FFE082
    style PasswordMatch fill:#FFE082
    style RoleType fill:#FFE082
    style CheckGroupAssignment fill:#FFE082
    style CheckAuth fill:#FFE082
    style CheckPermission fill:#FFE082
```

## Description
Complete authentication and authorization flow showing role-based access control and middleware protection.

## Key Components
- **Authentication**: Laravel Auth facade with session management
- **User Model**: Stores role field for authorization
- **Middleware**: Role-specific middleware in app/Http/Middleware
- **Routes**: Protected routes in routes/web.php and routes/auth.php

## User Roles
1. **admin**: Full system access
2. **student**: Access to reports, submissions, meetings
3. **supervisor**: Group management, report review
4. **advisor**: Group creation, student assignment
5. **teacher**: Similar to advisor role
6. **co-supervisor**: Limited supervisor access
7. **panel-member**: Evaluation access only

## Security Features
- CSRF token regeneration on login
- Password hashing via bcrypt
- Session-based authentication
- Route middleware protection
- 403 responses for unauthorized access