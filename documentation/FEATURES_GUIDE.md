# Thesis Management System - Features Guide

This document catalogs all features, actors, endpoints, and rules implemented in the thesisrepo-backup application. It is derived from controllers, models, services, routes, and middleware in the codebase to serve as a comprehensive features notebook.

Last Updated: September 2025

---

## Table of Contents

1. Roles and Access Model
2. Authentication and Session
3. Dashboards by Role
4. Admin Features
5. Advisor Features
6. Supervisor Features
7. Co‑Supervisor Features
8. Panel Member Features
9. Teacher Features
10. Student Features
11. Reports and Lifecycle
12. Report Annotation (Feedback) System
13. Meetings Management and PDF Export
14. Group Management (Admin + Advisor)
15. Supervisor Assignment Algorithm
16. Excel Integration (Advisor)
17. Notifications System (UI + API)
18. Public Thesis Repository
19. Performance Monitoring (Admin)
20. Middleware & Access Control
21. Rate Limiting
22. Core Data Models (Quick Reference)
23. Security, Storage, and Logging
24. Support

---

## 1) Roles and Access Model

Actors and their scopes, as enforced by routes and middleware.

- Admin
  - Full administration of batches, supervisors, areas of interest, and groups
  - Performance monitoring and system tests
- Advisor (Faculty advisor)
  - Manages their students and their advisor-created groups (capacity 3)
  - Runs supervisor assignment (manual and lottery modes)
  - Excel-based bulk grouping
- Supervisor (Main supervisor)
  - Manages assigned groups, meetings, reports, approvals
  - Can allow/disallow co-supervisor meeting management per group
- Co-Supervisor
  - Access to co-supervised groups
  - Can manage meetings if permitted by main supervisor
  - Can review reports, annotate, and send feedback
- Panel Member
  - Reviewer for designated groups
  - Can view/annotate reports and send feedback
- Teacher
  - Umbrella role for faculty; can access supervisor, co-supervisor, and panel member panels
  - Can add comments on reports for groups where they are supervisor
- Student
  - Own dashboard, group and supervisor details
  - Access reports, submissions, annotations, meeting history and PDFs

Route middleware guards:
- auth (authenticated)
- admin, advisor, teacher, student role gates

---

## 2) Authentication and Session

- Local authentication for admin; external API auth for students and teachers
- Session regeneration on login, CSRF on forms, secure cookies
- Rate limiting on login and external API–bound pages
- Logout is CSRF-protected and terminates the session

---

## 3) Dashboards by Role

- Admin Dashboard: /admin/dashboard
- Advisor Dashboard: /advisor/dashboard (throttled against external API)
- Teacher Dashboard: /teacher/dashboard
- Supervisor Dashboard: /supervisor/dashboard
- Co-Supervisor Dashboard: /co-supervisor/dashboard
- Panel Member Dashboard: /panel-member/dashboard
- Student Dashboard: /student/dashboard (throttled)

Each dashboard aggregates quick navigation to the role’s primary features (groups, meetings, reports, monitoring, etc.).

---

## 4) Admin Features

Administrative features are implemented across:
- Admin\AreaOfInterestController
- Admin\SupervisorController
- Admin\BatchController
- Admin\GroupManagementController
- Admin\PerformanceController

Key capabilities:

A) Areas of Interest (AOI)
- List, paginate, create, edit, delete
- Bulk creation via newline-separated entries
- Toggle active/inactive
- Paths:
  - GET /admin/areas-of-interest
  - GET /admin/areas-of-interest/create
  - POST /admin/areas-of-interest (single)
  - POST /admin/areas-of-interest/bulk
  - GET /admin/areas-of-interest/{id}/edit
  - PUT /admin/areas-of-interest/{id}
  - DELETE /admin/areas-of-interest/{id}

B) Supervisors
- Sync from external API (throttled)
- Edit details: thesis_limit, is_active, AOIs
- Bulk thesis_limit updates for subsets
- Toggle is_active
- Toggle AOI membership per supervisor
- Refresh one supervisor from API (throttled)
- Paths:
  - GET /admin/supervisors
  - POST /admin/supervisors/sync
  - GET /admin/supervisors/{id}/edit
  - PUT /admin/supervisors/{id}
  - POST /admin/supervisors/bulk-limits
  - POST /admin/supervisors/{id}/toggle
  - POST /admin/supervisors/{id}/toggle-area
  - POST /admin/supervisors/{id}/refresh

C) Batches
- Sync from external API (throttled)
- Activate/deactivate one or many batches; activate-all/deactivate-all
- Compare local vs API
- Edit batch meta and status
- Delete batch
- Paths:
  - GET /admin/batches
  - POST /admin/batches/sync
  - POST /admin/batches/{id}/toggle
  - POST /admin/batches/bulk-action
  - GET /admin/batches/{id}/edit
  - PUT /admin/batches/{id}
  - DELETE /admin/batches/{id}
  - GET /admin/batches/compare
  - POST /admin/batches/activate-all
  - POST /admin/batches/deactivate-all

D) Groups (Admin-wide management)
- View all groups in a batch across advisors
- Create group with:
  - name
  - batch
  - up to many AOIs
  - optional pre-assigned supervisor (capacity validated)
  - Admin-created groups support up to 4 students
  - Advisor will be auto-detected when the first student is assigned (from external API)
- Assign/remove students to admin-created groups
  - Cross-batch student assignment allowed
  - Enforces same-advisor per group (auto-detected from API)
  - Auto-expands capacity to 4 when required
- Assign/unassign AOIs (multi-select)
- Assign/unassign supervisor (validates capacity)
- Assign/unassign co-supervisor (not equal to main supervisor, capacity validated)
- Assign/unassign panel members (no capacity constraints; reviewers only)
- Delete one group (no students) with automatic re-numbering of Group N labels
- Bulk delete multiple groups (no students) with re-numbering within affected batches
- Query available supervisors, optionally filtered by AOI
- Paths:
  - GET /admin/groups
  - POST /admin/groups/create
  - POST /admin/groups/assign-student
  - POST /admin/groups/remove-student
  - POST /admin/groups/assign-area-of-interest
  - POST /admin/groups/assign-supervisor
  - POST /admin/groups/unassign-supervisor
  - POST /admin/groups/assign-co-supervisor
  - POST /admin/groups/unassign-co-supervisor
  - POST /admin/groups/assign-panel-member
  - POST /admin/groups/unassign-panel-member
  - GET /admin/groups/available-supervisors
  - DELETE /admin/groups/{group}
  - POST /admin/groups/bulk-delete

E) Performance Monitoring
- Dashboard (metrics + health)
- AJAX endpoints for metrics, DB, API, security
- Clear performance cache
- Export metrics as JSON
- On-demand component tests (db, cache, storage, external API)
- Paths:
  - GET /admin/performance
  - GET /admin/performance/metrics
  - GET /admin/performance/health
  - GET /admin/performance/database
  - GET /admin/performance/api
  - GET /admin/performance/security
  - POST /admin/performance/clear-cache
  - GET /admin/performance/export
  - GET /admin/performance/test

---

## 5) Advisor Features

Controllers: Advisor\DashboardController, Advisor\StudentController, Advisor\GroupController, Advisor\SupervisorAssignmentController

A) Students
- List students assigned to advisor (via external API)
- Filter by batch; search by name or roll
- View student profile pulled from API
- Force data refresh (clears caches)
- Paths:
  - GET /advisor/students (throttled)
  - GET /advisor/students/{student} (throttled)
  - POST /advisor/students/refresh (throttled)

B) Groups (Advisor-created only; capacity 3)
- Batches showable are those where advisor has students (external API)
- Create groups automatically for a batch (size 3; number = ceil(students/3))
- Add additional group by name
- Assign/unassign students to/from advisor-created groups only
  - Prevents assigning same student to multiple groups across batches for this advisor
  - Student list sourced from external API across advisor’s batches
- Assign AOIs (multi-select) to groups
- Remove all AOIs within a batch (legacy single-AOI cleanup utility)
- Remove all advisor-created groups in a batch (and their assignments)
- Download Excel template for batch with current students and existing group list
- Upload Excel to bulk-assign groups
  - Auto-detects columns and header
  - Validates students belong to advisor and enforces group size ≤ 3
  - Creates additional groups as needed
  - Randomly maps Excel-defined groupings to actual group numbers to prevent bias
- Paths:
  - GET /advisor/groups (throttled)
  - POST /advisor/groups/create
  - POST /advisor/groups/add
  - POST /advisor/groups/assign-student (throttled)
  - POST /advisor/groups/remove-student
  - POST /advisor/groups/assign-area-of-interest
  - POST /advisor/groups/unassign-all-areas-of-interest
  - POST /advisor/groups/remove-all-groups
  - GET /advisor/groups/download-template (throttled)
  - POST /advisor/groups/upload-excel (throttled)

C) Supervisor Assignment
- Manual: choose eligible supervisor (matches any group AOI, has available slots, co-supervisor mismatch prevented)
- Lottery: three modes (AOI, ranking, combined/both); optional preview
- Unassign one or all (optional batch filter)
- Available supervisors endpoint supports multiple AOI filters and excludes current co-supervisor
- Paths:
  - GET /advisor/supervisor-assignment
  - POST /advisor/supervisor-assignment/assign-manual
  - POST /advisor/supervisor-assignment/unassign
  - POST /advisor/supervisor-assignment/unassign-all
  - POST /advisor/supervisor-assignment/run-lottery
  - GET /advisor/supervisor-assignment/preview-lottery
  - GET /advisor/supervisor-assignment/available-supervisors (throttled)

---

## 6) Supervisor Features

Controllers: Supervisor\DashboardController, Supervisor\GroupController, Supervisor\MeetingController, Supervisor\ReportController, Supervisor\ReportAnnotationController

A) Groups
- View groups where user is main supervisor, co-supervisor, or panel member (for visibility)
- Toggle permission allowing co-supervisor to manage meetings per group
- Paths:
  - GET /supervisor/groups
  - POST /supervisor/groups/toggle-co-supervisor-meetings

B) Meetings
- Create, view, edit meetings for groups where the user can manage meetings:
  - Main supervisor always allowed
  - Co-supervisor allowed only when permission is enabled on the group
- Filter meetings by group or date range
- Return group students for attendance UI via JSON
- Export a group’s meetings (with attendance) as PDF
- Paths:
  - GET /supervisor/meetings
  - POST /supervisor/meetings
  - GET /supervisor/meetings/students (JSON)
  - GET /supervisor/meetings/{meeting}
  - GET /supervisor/meetings/{meeting}/edit
  - PUT /supervisor/meetings/{meeting}
  - GET /supervisor/meetings/{group}/pdf

C) Reports
- Index of reports grouped by supervised groups
- Create report for any supervised group
  - Type: general or final
  - Optional supervisor message and extra input
  - AOI on report is auto-set to an intersection of supervisor AOIs and group AOIs if any
  - Notifies all group students (database notifications)
- View/edit/delete report (only for own groups)
- Mark under review (requires submissions)
- Finalize and approve final reports (sets title, abstract, keywords) → visible on public repository
- View/inline or download student submissions (PDF/PPT/PPTX uploads by students)
- Paths:
  - GET /supervisor/reports
  - GET /supervisor/reports/create
  - POST /supervisor/reports
  - GET /supervisor/reports/{report}
  - GET /supervisor/reports/{report}/edit
  - PUT /supervisor/reports/{report}
  - DELETE /supervisor/reports/{report}
  - POST /supervisor/reports/{report}/under-review
  - GET /supervisor/reports/{report}/finalize (form)
  - POST /supervisor/reports/{report}/finalize
  - GET /supervisor/reports/{report}/submissions/{submission}/view
  - GET /supervisor/reports/{report}/submissions/{submission}/download

D) Report Annotation (Supervisor)
- Annotate a PDF submission and save drafts
- Send feedback to all group members (notification dispatch)
- View annotation history per submission (versioned sessions)
- Paths:
  - GET /supervisor/reports/{report}/submissions/{submission}/annotate
  - POST /supervisor/reports/{report}/submissions/{submission}/annotations
  - POST /supervisor/reports/{report}/submissions/{submission}/annotations/{annotationSession}/send-feedback
  - GET /supervisor/reports/{report}/submissions/{submission}/annotations

---

## 7) Co‑Supervisor Features

Controllers: CoSupervisor\DashboardController, GroupController, MeetingController, ReportController, ReportAnnotationController

- View co-supervised groups; visibility into reports and submissions
- Manage meetings only when allowed by the main supervisor on that group
  - Create/edit meetings under permission
- Mark reports as “under review”
- View/inline or download student submissions
- Annotate submissions, save drafts, and send feedback (notifications)
- Paths:
  - GET /co-supervisor/dashboard
  - GET /co-supervisor/groups
  - Meetings:
    - GET /co-supervisor/meetings
    - POST /co-supervisor/meetings
    - GET /co-supervisor/meetings/{meeting}
    - GET /co-supervisor/meetings/{meeting}/edit
    - PUT /co-supervisor/meetings/{meeting}
  - Reports:
    - GET /co-supervisor/reports
    - GET /co-supervisor/reports/{report}
    - POST /co-supervisor/reports/{report}/under-review
    - GET /co-supervisor/reports/{report}/submissions/{submission}/view
    - GET /co-supervisor/reports/{report}/submissions/{submission}/download
  - Report annotations:
    - GET /co-supervisor/reports/{report}/submissions/{submission}/annotate
    - POST /co-supervisor/reports/{report}/submissions/{submission}/annotations
    - POST /co-supervisor/reports/{report}/submissions/{submission}/annotations/{annotationSession}/send-feedback
    - GET /co-supervisor/reports/{report}/submissions/{submission}/annotations

---

## 8) Panel Member Features

Controllers: PanelMember\DashboardController, GroupController, ReportController, ReportAnnotationController

- See groups where assigned as a panel member
- Review report details and submissions
- Mark reports “under review”
- Annotate submissions, save drafts, and send feedback (notifications)
- Paths:
  - GET /panel-member/dashboard
  - GET /panel-member/groups
  - Reports:
    - GET /panel-member/reports
    - GET /panel-member/reports/{report}
    - POST /panel-member/reports/{report}/under-review
    - GET /panel-member/reports/{report}/submissions/{submission}/view
    - GET /panel-member/reports/{report}/submissions/{submission}/download
  - Report annotations:
    - GET /panel-member/reports/{report}/submissions/{submission}/annotate
    - POST /panel-member/reports/{report}/submissions/{submission}/annotations
    - POST /panel-member/reports/{report}/submissions/{submission}/annotations/{annotationSession}/send-feedback
    - GET /panel-member/reports/{report}/submissions/{submission}/annotations

---

## 9) Teacher Features

Controllers: Teacher\DashboardController, Teacher\ReportCommentController

- Unified access for faculty to supervisor, co-supervisor, and panel member panels (when applicable)
- Add comments to reports for groups where the teacher is the main supervisor; notifies group students
- Paths:
  - GET /teacher/dashboard
  - POST /teacher/reports/{report}/comments

---

## 10) Student Features

Controllers: Student\DashboardController, Student\ReportController, Student\ReportSubmissionController, Student\ReportAnnotationController

A) Dashboard & Meetings
- Personalized dashboard with group membership, AOIs, supervisor details, and statistics
- View meeting list and details for the group
- Download group meeting report as PDF (from supervisor side)

B) Reports
- Index of group reports (creator, status, comments, approval badges for final approved)
- Show report with:
  - Group members
  - Teacher comments
  - Student’s own submission (if any)
  - Annotation sessions for the student’s latest submission
- Notifications: seen as dropdown and full page; can mark individually or all as read

C) Submissions
- Create one submission per report per student
- Edit or delete own submission
- Upload limits: PDF, PPT, PPTX up to 20MB
- Inline view and file downloads available to students (own) and faculty as permitted
- Paths:
  - GET /student/reports
  - GET /student/reports/{report}
  - GET /student/reports/{report}/submissions/create
  - POST /student/reports/{report}/submissions
  - GET /student/reports/{report}/submissions/{submission}
  - GET /student/reports/{report}/submissions/{submission}/edit
  - PUT /student/reports/{report}/submissions/{submission}
  - DELETE /student/reports/{report}/submissions/{submission}
  - GET /student/reports/{report}/submissions/{submission}/download

D) Annotation History (Read-only)
- View the history of annotation sessions for own submission
- View or download an annotated session when provided
- Paths:
  - GET /student/reports/{report}/submissions/{submission}/annotations
  - GET /student/reports/{report}/submissions/{submission}/annotations/{session}
  - GET /student/reports/{report}/submissions/{submission}/annotations/{session}/download

E) Notifications (UI)
- Pages/Endpoints:
  - GET /student/notifications
  - POST /student/notifications/{id}/mark-read
  - POST /student/notifications/mark-all-read

---

## 11) Reports and Lifecycle

Model: Report

Properties and statuses
- type: general | final
- status: draft | submitted | under_review | approved
- Finalization requires: type=final AND at least one student submission; on approval sets project_title, abstract_md, keywords, approved_at/by

Lifecycle summary
- Create (draft)
- Student(s) submit file(s)
- Mark “under review” (Supervisor, Co‑Supervisor, Panel Member)
- Finalize & Approve (Supervisor only) → Published to Public Repository

Supervisor and teacher actions generate notifications to group members:
- NewReportAssigned (on create)
- ReportUpdated (on update of meaningful fields)
- NewReportComment (on teacher comment)
- NewReportAnnotation (on sending feedback)

---

## 12) Report Annotation (Feedback) System

Controllers: Supervisor/Co‑Supervisor/Panel Member ReportAnnotationController
Model: ReportAnnotationSession

- Role-based access checks:
  - Supervisor: own supervised groups
  - Co‑Supervisor: groups where assigned as co-supervisor
  - Panel Member: groups where assigned as panel member
- Requirements: PDF submission must exist and be accessible in storage
- Features:
  - Versioned sessions (auto-increment version per submission)
  - Save draft (is_sent=false)
  - Send feedback (dispatch notifications to all group members; sets is_sent=true and sent_at)
  - Session metadata tracks created_by_type for co-supervisor/panel member origin
- Student read-only access to their own session history

---

## 13) Meetings Management and PDF Export

Models: Group, Meeting, MeetingAttendance

- Supervisor Meetings:
  - Create/edit meetings for groups user can manage (main supervisor by default; co‑supervisor with permission)
  - Capture meeting_date, discussed_topics, outcomes, and per-student attendance
  - List with filters (group, date range)
  - Export group’s entire meeting history as an A4 PDF including attendance summary and metadata
- Co‑Supervisor Meetings:
  - Same UI workflow available only when main supervisor enables permission on that group; otherwise read-only or blocked

---

## 14) Group Management (Admin + Advisor)

- Admin-created groups (max 4 students): cross-batch student assignment, advisor auto-detection on first student assignment, AOIs multi-select, supervisor/co-supervisor/panel members assignment, deletion with re-numbering, bulk deletes
- Advisor-created groups (max 3 students): restricted to advisor’s own batches/students via external API, cross-batch lookup for available students, Excel bulk grouping, AOIs multi-select, utilities to clear AOIs or wipe groups in batch
- Group AOIs:
  - Legacy single AOI maintained for backward compatibility; multiple AOIs now supported as pivot relation
  - Matched AOI is persisted when supervisor is assigned for traceability
- Co‑Supervisor Permissions:
  - Main supervisor can toggle the “co_supervisor_can_manage_meetings” flag per group to delegate meeting management

---

## 15) Supervisor Assignment Algorithm

See documentation/SUPERVISOR_ASSIGNMENT_ALGORITHM.md for flowcharts and detailed logic.

Modes (as implemented by Advisor SupervisorAssignmentController and SupervisorAssignmentService)
- AOI-based Lottery: prioritize AOI matches; random among matches
- Ranking Lottery: round-robin by academic rank priority
- Combined Mode: merge AOI fairness with ranking to balance expertise and distribution

Guarantees
- Supervisor capacity respected (thesis_limit, available_slots)
- Excludes co-supervisor as candidate for main-supervisor assignment
- Tracks assignment metadata (is_manual_assignment, matched_area_of_interest_id, assigned_at)
- Preview endpoint to simulate results without persistence

---

## 16) Excel Integration (Advisor)

- Accepts xlsx, xls, csv (≤ 2MB)
- Column auto-detection for Student ID and Group Name; header optional
- Validates students belong to advisor in that batch; skips/flags invalid rows
- Enforces group-size constraint (≤ 3)
- Auto-creates missing groups and randomly maps Excel-defined groupings to actual group numbers to avoid bias
- Template download includes current students and list of available groups for reference

---

## 17) Notifications System (UI + API)

User-triggered events generate database notifications for group members:
- NewReportAssigned (purple icon)
- NewReportComment (blue icon)
- ReportUpdated (orange icon; only when meaningful changes occur)
- NewReportAnnotation (feedback sent for a submission)

UI Features (Student)
- Bell icon with unread badge; dropdown with recent 10
- Full page history with pagination
- Mark single or all as read

API Endpoints (auth required; shared for all users)
- GET /api/notifications → last 10 + unread_count + total_count
- POST /api/notifications/{id}/mark-read
- POST /api/notifications/mark-all-read
- GET /api/notifications/unread-count → unread count

Delivery & Safety
- All notification sends happen within DB transactions where applicable
- Robust logging on notification fan-out and failures

---

## 18) Public Thesis Repository

Controller: HomeController

Publicly available, read-only catalog of approved final reports:
- Index: /
  - Search by title/abstract
  - Filter by year range, supervisor, and area of interest
  - Filter by keywords (comma-separated)
  - Sort by newest, oldest, or title
- Detail: /reports/{report}
  - Only accessible for approved final reports
  - Shows group members, approver, and submission history
- PDF View/Download for latest PDF submission
  - GET /reports/{report}/pdf/view
  - GET /reports/{report}/pdf/download

---

## 19) Performance Monitoring (Admin)

- Composite metrics for system, database, API, security, and user stats
- Health checks with an overall status
- Auto-refresh via AJAX; manual refresh endpoints
- Export JSON report with summary
- Component tests:
  - Database connectivity and query latency
  - Cache driver read/write/delete
  - Storage read/write/delete
  - External API reachability and latency

---

## 20) Middleware & Access Control

Role middleware implemented under app/Http/Middleware:
- EnsureUserIsAdmin: protects /admin/*
- EnsureUserIsAdvisor: protects /advisor/*
- EnsureUserIsTeacher: protects /teacher/* and teacher-accessible supervisor/co-supervisor/panel routes
- EnsureUserIsStudent: protects /student/*

Route groups also layer auth and per-route throttles for external API usage. Access checks exist in controllers and models for finer-grained enforcement:
- Group::canSupervisorManageMeetings(supervisorId)
- Group::canSupervisorApprove(supervisorId)
- Group::canSupervisorAccess(supervisorId)
- Report::canBeApproved(), hasSubmissions()

---

## 21) Rate Limiting

Applied via named throttles in routes:
- Student dashboard: throttle:external_api_student_dashboard
- Advisor: dashboard/students/groups/template/upload/available-supervisors with named throttles
- Admin: supervisors sync/refresh and batches sync/compare throttled
- General login attempt throttling configured in auth middleware

The limits are centrally configurable; check config and middleware definitions for per-environment values.

---

## 22) Core Data Models (Quick Reference)

Group
- Relationships: advisor(User), students(GroupStudent), meetings, reports, supervisor, coSupervisor, panelMembers (GroupPanelMember), panelSupervisors (Supervisor many-to-many), matchedAreaOfInterest (AOI), areasOfInterest (many-to-many)
- Flags & helpers: isFull, available_slots, hasSupervisor, isEligibleForAssignment, created_by_type (admin/advisor), advisor_auto_detected, co_supervisor_can_manage_meetings

Report
- Fields: group_id, area_of_interest_id, type (general/final), project_title, abstract_md, extra_input, supervisor_message, keywords (JSON or string), status, approved_at/by
- Scopes: approved, final, forGroup, ofType
- Helpers: isDraft/Submitted/UnderReview/Approved, canBeApproved, hasSubmissions, status badge helpers

Meeting
- Fields: group_id, meeting_date, discussed_topics, outcomes
- Relationships: group, attendances; computed: present_count, total_count

StudentReportSubmission
- One per student per report; stores file path, name, size, mime, student_id (user id), report_id
- Files stored in public disk under student-submissions/

ReportAnnotationSession
- Versioned annotation payload per submission
- Fields: report_id, submission_id, supervisor_id (user id of sender), version, message, annotations_json, annotated_file_path, is_sent, sent_at, created_by_type

Supervisor
- Faculty profile synchronized with external API; relates to AOIs and groups
- Rank priority used in ranking lottery
- Capacity via thesis_limit and computed available_slots

AreaOfInterest
- Active/inactive; linked to supervisors and groups

Batch
- batch_number, is_active, display_name/description; synced via API

GroupPanelMember
- Associates a supervisor with a group as a panel member (review-only role)

---

## 23) Security, Storage, and Logging

Security
- CSRF protection on all forms
- HTTPS enforced in production environment
- Session regeneration on login
- Passwords hashed (bcrypt)
- Role-based route guards + per-action authorization checks in controllers/models

Storage
- Public disk for submissions and PDFs
- Annotation sessions may persist additional generated files when applicable

Logging
- Extensive logs for:
  - Group operations (admin/advisor)
  - Notifications fan-out
  - Report lifecycle events
  - Meeting PDF generation
  - Performance export/tests

---

## 24) Support

- Refer to this features guide and related guides:
  - PERFORMANCE_MONITORING_GUIDE.md
  - SUPERVISOR_ASSIGNMENT_ALGORITHM.md
  - PROJECT_SETUP_GUIDE.md
- Common issues:
  - Login: verify credentials and API availability
  - Group assignment: confirm batch eligibility and AOIs
  - Excel upload: validate file format, headers, and advisor’s students
  - Performance: clear cache and refresh metrics

---

Document Version: 2.1
System Version: 1.0.0
