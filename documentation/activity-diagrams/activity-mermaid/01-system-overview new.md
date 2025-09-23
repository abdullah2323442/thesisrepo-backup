# System Overview Activity Diagram

```mermaid
flowchart TD
    Start([Start]) --> Admin[Admin: System Setup]
    Admin --> AdminTasks[Configure Batches, AOIs & Users]
    
    AdminTasks --> GroupCreation{Group Creation Method}
    GroupCreation -->|Admin Path| AdminGroups[Admin: Create Groups via AdminCreatedGroup]
    GroupCreation -->|Advisor Path| AdvisorGroups[Advisor/Teacher: Create Groups]
    
    AdminGroups --> AssignStudents[Assign GroupStudent Memberships]
    AdvisorGroups --> AssignStudents
    AssignStudents --> SetAOI[Set Group-AreaOfInterest Relations - Many-to-Many]
    SetAOI --> RunAssignment[Execute SupervisorAssignmentService]
    RunAssignment --> History[Log to AssignmentHistory]
    
    History --> Supervisors[Supervisor & Co-Supervisor: Receive Groups]
    Supervisors --> ReviewReports[Review Reports & StudentReportSubmissions]
    ReviewReports --> Annotate[Create ReportAnnotationSessions]
    Annotate --> Comments[Add ReportComments]
    Comments --> Meetings[Schedule Meetings with MeetingAttendance]
    Meetings --> Feedback[Provide Feedback via Notifications]
    
    Feedback --> Student[Student: Submit Reports]
    Student --> Attend[Record MeetingAttendance]
    Attend --> ViewFeedback[View Annotations & Comments]
    ViewFeedback --> Revise[Revise & Resubmit via StudentReportSubmission]
    
    Revise --> PanelReview[GroupPanelMember: Final Evaluation]
    PanelReview --> FinalAnnotations[Panel Annotations - created_by_type: panel_member]
    FinalAnnotations --> End([End])
    
    RunAssignment -.->|External APIs| APIs[BatchApiService / StudentApiService / SupervisorApiService]
    
    style Start fill:#4CAF50,color:#fff
    style End fill:#f44336,color:#fff
    style Admin fill:#E3F2FD
    style AdvisorGroups fill:#F3E5F5
    style Supervisors fill:#E8F5E9
    style Student fill:#FFF3E0
    style PanelReview fill:#EFEBE9
    style APIs fill:#E1F5FE
```

## Description
This diagram shows the high-level workflow of the thesis management system, accurately reflecting the models and services in the codebase.

## Key Actors
- **Admin**: System configuration, batch management, and user management
- **Advisor/Teacher**: Group creation and supervisor assignment
- **Supervisor & Co-Supervisor**: Report review and feedback
- **Student**: Report submission via StudentReportSubmission model
- **GroupPanelMember**: Final evaluation with panel-specific annotations

## Key Models & Services
- **Models**: User, Batch, AreaOfInterest, Group, GroupStudent, AdminCreatedGroup, Supervisor, Report, StudentReportSubmission, ReportAnnotationSession, ReportComment, Meeting, MeetingAttendance, GroupPanelMember, AssignmentHistory
- **Services**: SupervisorAssignmentService, BatchApiService, StudentApiService, SupervisorApiService
- **Notifications**: NewReportAssigned, NewReportAnnotation, NewReportComment, ReportUpdated