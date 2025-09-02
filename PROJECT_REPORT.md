Title: University Thesis Management System - Project Report
Author: Development Team
Department: Computer Science & Engineering
Institution: [University Name]
Date: January 2025
Version: 1.0.0

Abstract
This report presents the design, implementation, and evaluation of a University Thesis Management System built with Laravel. The system streamlines thesis group formation, supervisor assignment, meeting management, and performance monitoring for multiple user roles (Admin, Advisor, Supervisor, Student, Teacher). The work includes a production-grade supervisor assignment algorithm with fairness constraints, comprehensive security hardening with rate limiting across external API dependencies, and a high-quality professional user interface. The system has 34+ verified feature and security tests, complete documentation, and is ready for production deployment.

Keywords: Thesis Management, Supervisor Assignment, Laravel, Performance Monitoring, Rate Limiting, API Integration, Security, Testing

Acknowledgements
We acknowledge the faculty advisors, the IT department for API access, and the development and QA teams for their contributions.

Table of Contents
- Abstract
- Acknowledgements
- List of Abbreviations
- 1. Introduction
- 2. Literature Review & Related Work
- 3. System Requirements
- 4. System Design and Architecture
- 5. Implementation Details
- 6. Database Schema Overview
- 7. Testing and Quality Assurance
- 8. Deployment and Operations
- 9. Results and Evaluation
- 10. Project Management
- 11. Ethics and Data Privacy
- 12. Conclusion and Future Work
- References
- Appendices

List of Abbreviations
- AOI: Area of Interest
- API: Application Programming Interface
- DDD: Domain-Driven Design
- MFA: Multi-Factor Authentication (conceptual)
- MVC: Model-View-Controller
- RR: Round-Robin

1. Introduction
1.1 Background
Managing thesis projects at scale involves coordinating students, advisors, supervisors, and administrative workflows. Manual approaches lead to inefficiencies, uneven supervisor loads, and poor visibility into progress and performance. This system provides a robust, secure, and well-documented platform that centralizes thesis workflows and enforces fairness and capacity constraints.

1.2 Problem Statement
Universities need a reliable solution to manage thesis groups, ensure fair supervisor assignments considering capacity and expertise, integrate with existing university APIs, and provide monitoring and testing to guarantee reliability and security.

1.3 Objectives
- Build a production-ready thesis management platform for five user roles.
- Implement an intelligent supervisor assignment algorithm supporting AOI and rank-based fairness.
- Provide end-to-end documentation and comprehensive tests.
- Harden security with rate limiting, input validation, CSRF protection, and session best practices.
- Deliver professional, responsive UIs and operational monitoring.

1.4 Scope
Included: Multi-role access; group management; supervisor assignment; meeting management with PDF reports; API integration (students, teachers, batch, supervisors); performance monitoring; security hardening; testing and documentation.
Excluded: Full thesis document submission/review pipeline, notifications, and mobile apps (planned as future enhancements).

1.5 Contributions
- Production-grade supervisor assignment algorithm (three modes) with capacity and fairness guarantees.
- Comprehensive performance monitoring dashboard with sanity tests and metrics.
- Security hardening including rate limiting on 12+ endpoints.
- Complete developer, feature, setup, and testing documentation (200+ pages equivalent).
- 34+ passing tests validating security and critical features.

2. Literature Review & Related Work
Existing literature and systems emphasize fair allocation, capacity management, and AOI matching in supervision. Conventional approaches include greedy matching, round-robin assignment, and scoring functions. Our system implements a domain-specific, capacity-aware lottery with ranked round-robin fairness, AOI matching, and smart rotation to avoid consecutive assignments—offering practicality, transparency, and maintainability over more opaque optimization methods.

3. System Requirements
3.1 Functional Requirements
- FR1: Role-based dashboards (Admin, Advisor, Supervisor, Student, Teacher).
- FR2: Group creation, deletion, and student assignment with auto-detected advisor from student data.
- FR3: Supervisor management (sync/refresh) and batch management via external APIs.
- FR4: Supervisor assignment: manual and lottery modes (AOI-based, Ranking-based, Combined).
- FR5: Meeting management (create, edit, attendance, topics, outcomes) with student-side PDF reports.
- FR6: Excel integration for bulk student-group operations.
- FR7: Performance monitoring dashboard including API health and rate limiting status.

3.2 Non-Functional Requirements
- NFR1: Security: rate limiting, CSRF, secure sessions, sanitized logging.
- NFR2: Reliability: well-tested with 34+ passing tests; robust error handling.
- NFR3: Performance: efficient queries, indexing, and monitored API performance.
- NFR4: Maintainability: clean architecture with services, controllers, and models; comprehensive documentation.
- NFR5: Usability: responsive admin-style interface across devices.

4. System Design and Architecture
4.1 Technology Stack
- Backend: Laravel 11.x (PHP 8.2+)
- Frontend: Blade templates, Tailwind CSS, Alpine.js
- Database: MySQL 8+ / PostgreSQL 13+ / SQLite for testing
- Authentication: Laravel session auth, external API integration (students/teachers)
- Testing: PHPUnit with feature and security tests

4.2 Architecture Overview
- MVC with service layer for business logic (e.g., SupervisorAssignmentService).
- External API integration via configurable endpoints and rate limiters.
- Caching strategies for metrics; database views for optimized read models.
- Clear role-based routing and middleware protection.

4.3 Core Modules
- Authentication and Role Management
- Group Management and Student Assignment
- Supervisor Assignment (Manual and Lottery)
- Meeting Management with PDF export (student-side reports)
- Excel Integration (upload/download)
- Performance Monitoring (admin)

4.4 Design Principles
- SOLID and clean architecture patterns.
- Separation of concerns: controllers handle I/O, services encapsulate domain logic.
- Deterministic behavior where appropriate (ranking/combined modes), controlled randomness where desired (AOI mode).

5. Implementation Details
5.1 Supervisor Assignment Algorithm
Modes:
- AOI-based Lottery (Randomized): Matches by AOI and selects randomly (excludes last assigned if alternatives exist). Non-deterministic.
- Ranking-based Lottery (Deterministic RR): Ignores AOI; performs strict round-robin honoring academic rank and capacity.
- Combined (AOI + Rank): Ultra-fair within AOI, uses rank as tiebreaker, avoids consecutive assignments per AOI when possible. Deterministic given same inputs.

Key Features:
- Capacity-aware selection; never exceeds supervisor limits.
- Fair distribution across runs and AOIs.
- Preview mode (dry-run) and persistent run with audit history.
- Deterministic group order by numeric suffix for fairness and predictability.

5.2 Group Management Enhancements
- Admin-created groups track creator and support enhanced capacity (4 students vs 3 for advisor-created).
- Advisor auto-detection upon first student assignment from external student data.
- Visual distinction of admin-created groups in advisor UI; read-only protection.

5.3 Meeting Management
- Supervisors create/edit meetings; attendance, topics, outcomes tracked.
- Students view meeting history and download PDF reports.

5.4 Excel Integration
- Bulk upload for group assignment; validation and error reporting.
- Template download with current students; random group assignment for fairness while maintaining displayed ordering.

5.5 Performance Monitoring System
- Real-time dashboard for system, database, API, security, and error metrics.
- Component tests: database, cache, storage, external API health.
- Exportable JSON reports; cached metrics for trend analysis; manual and auto-refresh.

5.6 Security Hardening
- Rate limiting on 12+ endpoints (login, dashboards, API syncs, students, groups, etc.).
- CSRF protection; secure session configuration; session regeneration after login.
- Minimal, sanitized logging; HTTPS enforcement in production.
- External API calls via POST body with retries and conservative backoff.

6. Database Schema Overview
- Users: credentials, roles, and external identifiers; secure password hashing.
- Groups: name, batch, advisor, supervisor, areas of interest; creation metadata.
- GroupStudents: pivot table for student membership.
- AreasOfInterest: catalog with relationships to groups and supervisors.
- Supervisors: designation, rank priority, thesis limit, availability, activity status.
- Meetings and attendance records (with topics and outcomes fields).
- Auxiliary tables for performance monitoring (if applicable) and views for optimized reads.

7. Testing and Quality Assurance
7.1 Test Strategy
- Feature tests for authentication, dashboards, rate limiting, and core UIs.
- Security tests for rate limiting and session handling.
- Unit tests selectively for models and services.

7.2 Test Suite Summary
- Total working tests: 34+ (542 assertions)
- Security: login rate limiting; dashboard rate limiting; advisor endpoints throttling.
- UI/UX: Logout across all panels; student dashboard with group info.
- Profile management: updates, verification, deletion.

7.3 Representative Tests
- LogoutFunctionalityTest: presence of logout buttons in all panels and actual logout flow.
- StudentDashboardTest: group info and status rendering.
- ComprehensiveApiRateLimitingTest & ExternalApiRateLimitingTest: throttling behavior.

7.4 Results
- All targeted functional and security tests pass consistently.
- Deterministic algorithms verified for ranking/combined modes; AOI mode validated for randomness behavior and capacity constraints.

8. Deployment and Operations
8.1 Environment Requirements
- PHP 8.2+, Composer 2.x, Node 18+, MySQL/PostgreSQL.
- Production: APP_DEBUG=false; secure session and HTTPS enforced.

8.2 Setup Summary
- composer install, npm install, .env configuration, key generation, migrations/seeders, asset build, and php artisan serve.

8.3 Monitoring & Logs
- Admin performance dashboard; logs for component access and metrics.
- Exportable performance reports and configurable cache lifetimes.

9. Results and Evaluation
9.1 Feature Coverage
- Complete role-based features implemented; meeting management and PDF reports included.
- Supervisor assignment algorithm exhibits fair and capacity-aware assignments across scenarios.

9.2 Security & Reliability
- Strong rate limiting coverage; secure sessions; CSRF protection.
- Reliable test suite with 34+ tests; comprehensive verification of critical paths.

9.3 Usability & Performance
- Professional, responsive UI with admin-style layouts.
- Eager loading and indexing where needed; dashboard performance acceptable.

9.4 Limitations
- Some advanced unit tests would require additional model extensibility.
- External API availability affects certain flows; mitigated via retries and monitoring.

10. Project Management
- Milestones:
  1) Foundational setup and authentication
  2) Advisor/student data integration and dashboards
  3) Group management and Excel integration
  4) Supervisor assignment algorithm and UI
  5) Meeting management with PDF reports
  6) Performance monitoring and rate limiting
  7) Security hardening, testing, documentation, and cleanup

- Risks & Mitigations:
  - External API downtime → retries, rate limits, clear error handling.
  - Capacity misconfiguration → validations and monitoring.
  - Performance regressions → metrics, caching strategies, and tests.

11. Ethics and Data Privacy
- Protect user data through secure sessions and minimal logging.
- Respect API rate limits and terms; avoid storing sensitive API payloads.
- Employ role-based access and least-privilege design.

12. Conclusion and Future Work
This project delivers a production-ready, secure, and well-tested thesis management system with advanced supervisor assignment capabilities and comprehensive documentation. It significantly improves fairness, transparency, and operational efficiency in thesis management.

Future enhancements:
- Thesis document workflow (submission, review, plagiarism checks)
- Notifications and reminders
- Advanced analytics and capacity planning dashboards
- Background jobs for heavy operations
- Mobile applications and public APIs

References
- Laravel Documentation
- OWASP ASVS and Cheat Sheets
- University API Specifications (internal)
- Project documentation in /documentation

Appendices
A. API Endpoints (selected, rate-limited)
- POST /login (5/min)
- GET /student/dashboard (60/min)
- GET /advisor/dashboard (60/min)
- GET /advisor/students (60/min)
- POST /advisor/students/refresh (30/min)
- GET/POST /advisor/groups (120/min)
- POST /admin/supervisors/sync (10/5min)
- POST /admin/batches/sync (10/5min)

B. User Roles and Capabilities
- Admin: full system control, performance monitoring
- Advisor: group creation, student assignment, supervisor allocation
- Supervisor: group oversight, meeting management
- Student: dashboard, meeting history, PDF reports
- Teacher: combined functionality as applicable

C. Quick Start Commands
- composer install && npm install
- cp .env.example .env && php artisan key:generate
- Configure DB; php artisan migrate --seed
- npm run build && php artisan serve

D. Algorithm Modes Summary
- AOI-based: Randomized within AOI, avoids consecutive where possible
- Ranking-based: Deterministic round-robin by academic rank
- Combined: Ultra-fair within AOI, rank tiebreaker, avoids consecutive where possible
