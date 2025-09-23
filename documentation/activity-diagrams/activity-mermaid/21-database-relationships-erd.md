# Database Entity Relationship Diagram

```mermaid
erDiagram
    User ||--o{ GroupStudent : "has many"
    User ||--o| Supervisor : "can be"
    User ||--o{ Notification : "receives"
    User ||--o{ ReportComment : "creates"
    
    Batch ||--o{ Group : "contains"
    Batch ||--o{ User : "has students"
    
    Group ||--o{ GroupStudent : "has members"
    Group }o--|| Supervisor : "assigned to"
    Group }o--o| Supervisor : "co-supervisor"
    Group }o--o{ AreaOfInterest : "has areas"
    Group ||--o{ Report : "submits"
    Group ||--o{ Meeting : "schedules"
    Group ||--o{ GroupPanelMember : "evaluated by"
    Group ||--o| AdminCreatedGroup : "admin created"
    
    GroupStudent }o--|| User : "is student"
    GroupStudent }o--|| Group : "belongs to"
    
    Supervisor }o--|| User : "is user"
    Supervisor }o--o{ AreaOfInterest : "specializes in"
    Supervisor ||--o{ AssignmentHistory : "has history"
    
    AreaOfInterest }o--o{ Group : "linked to"
    AreaOfInterest }o--o{ Supervisor : "expertise"
    
    Report ||--o{ StudentReportSubmission : "has submissions"
    Report ||--o{ ReportComment : "has comments"
    Report ||--o{ ReportAnnotationSession : "has annotations"
    Report }o--|| Group : "submitted by"
    
    StudentReportSubmission }o--|| Report : "for report"
    StudentReportSubmission }o--|| User : "submitted by"
    
    ReportAnnotationSession ||--o{ ReportComment : "contains"
    ReportAnnotationSession }o--|| Report : "annotates"
    ReportAnnotationSession }o--|| User : "created by"
    
    ReportComment }o--|| Report : "on report"
    ReportComment }o--|| User : "commented by"
    ReportComment }o--o| ReportAnnotationSession : "in session"
    
    Meeting }o--|| Group : "for group"
    Meeting ||--o{ MeetingAttendance : "has attendees"
    
    MeetingAttendance }o--|| Meeting : "attends"
    MeetingAttendance }o--|| User : "attendee"
    
    GroupPanelMember }o--|| Group : "evaluates"
    GroupPanelMember }o--|| User : "is panel member"
    
    AssignmentHistory }o--|| Group : "assignment for"
    AssignmentHistory }o--|| Supervisor : "assigned to"
    
    User {
        bigint id PK
        string name
        string email UK
        string password
        string role
        string registration_number UK
        timestamp email_verified_at
        timestamps created_updated
    }
    
    Batch {
        bigint id PK
        string name
        string session
        boolean is_active
        timestamps created_updated
    }
    
    Group {
        bigint id PK
        string name
        bigint batch_id FK
        bigint supervisor_id FK
        bigint co_supervisor_id FK
        string status
        timestamps created_updated
    }
    
    GroupStudent {
        bigint id PK
        bigint group_id FK
        bigint student_id FK
        timestamps created_updated
    }
    
    Supervisor {
        bigint id PK
        bigint user_id FK
        string designation
        integer max_groups
        integer current_load
        timestamps created_updated
    }
    
    AreaOfInterest {
        bigint id PK
        string name
        string description
        timestamps created_updated
    }
    
    Report {
        bigint id PK
        bigint group_id FK
        string title
        text description
        string status
        date deadline
        timestamps created_updated
    }
    
    StudentReportSubmission {
        bigint id PK
        bigint report_id FK
        bigint student_id FK
        string file_path
        string file_type
        integer version
        timestamps created_updated
    }
    
    ReportAnnotationSession {
        bigint id PK
        bigint report_id FK
        bigint created_by FK
        string created_by_type
        text annotations_data
        timestamps created_updated
    }
    
    ReportComment {
        bigint id PK
        bigint report_id FK
        bigint user_id FK
        bigint session_id FK
        text comment
        integer page_number
        timestamps created_updated
    }
    
    Meeting {
        bigint id PK
        bigint group_id FK
        string title
        datetime scheduled_at
        string location
        string meeting_type
        timestamps created_updated
    }
    
    MeetingAttendance {
        bigint id PK
        bigint meeting_id FK
        bigint user_id FK
        boolean attended
        text notes
        timestamps created_updated
    }
    
    GroupPanelMember {
        bigint id PK
        bigint group_id FK
        bigint panel_member_id FK
        string role
        timestamps created_updated
    }
    
    AssignmentHistory {
        bigint id PK
        bigint group_id FK
        bigint supervisor_id FK
        string assignment_method
        bigint assigned_by FK
        timestamps created_updated
    }
```

## Description
Complete entity relationship diagram showing all database tables and their relationships.

## Key Relationships
- **Many-to-Many**: Group ↔ AreaOfInterest, Supervisor ↔ AreaOfInterest
- **One-to-Many**: Batch → Group, Group → Report, Report → StudentReportSubmission
- **Polymorphic**: Notifications (notifiable_type, notifiable_id)
- **Self-referential**: User table with multiple roles

## Junction Tables
- **group_area_of_interest**: Links groups to multiple areas
- **supervisor_area_of_interest**: Links supervisors to expertise areas
- **GroupStudent**: Links students to groups
- **GroupPanelMember**: Links panel members to groups

## Audit Tables
- **AssignmentHistory**: Tracks all supervisor assignments
- **notifications**: Stores all system notifications
- **AdminCreatedGroup**: View for admin-created groups