# Supervisor Use Case Diagram

## Overview
Supervisors are the main thesis guides responsible for managing groups, conducting meetings, creating reports, and providing final thesis approval.

## Use Case Diagram (Reference Design Style)

```mermaid
graph LR
    %% Actor
    actor Supervisor
    
    %% System Boundary
    subgraph System["Thesis Management System"]
        %% Use Cases
        UC1[Meeting Management]
        UC2[Report Annotation]
        UC3[Report Management]
        UC4[Thesis Approval]
    end
    
    %% Connections
    Supervisor ---|pink| UC1
    Supervisor ---|pink| UC2
    Supervisor ---|pink| UC3
    Supervisor ---|pink| UC4
    
    %% Styling
    classDef system fill:#4dabf7,stroke:#000,stroke-width:2px
    classDef usecase fill:#ffffff,stroke:#000,stroke-width:1px
    
    class System system
    class UC1,UC2,UC3,UC4 usecase
    
    style Supervisor fill:#e91e63,color:#fff,stroke:#000,stroke-width:2px
```

## Detailed Use Case Diagram

```mermaid
graph TB
    %% Actor
    actor Supervisor as "👤 Supervisor"
    
    %% System Boundary
    subgraph System["Thesis Management System - Supervisor Module"]
        %% Group Management
        subgraph Groups["Group Management"]
            UC1["View Assigned Groups"]
            UC2["View as Main Supervisor"]
            UC3["View as Co-Supervisor"]
            UC4["View as Panel Member"]
            UC5["Toggle Co-Supervisor Permissions"]
        end
        
        %% Meeting Management
        subgraph Meetings["Meeting Management"]
            UC6["Create Meeting"]
            UC7["View Meetings"]
            UC8["Edit Meeting"]
            UC9["Filter by Group"]
            UC10["Filter by Date"]
            UC11["Fetch Group Students"]
            UC12["Export Meetings as PDF"]
            UC13["Track Attendance"]
        end
        
        %% Report Management
        subgraph Reports["Report Management"]
            UC14["Create Report"]
            UC15["View Reports"]
            UC16["Edit Report"]
            UC17["Delete Report"]
            UC18["Mark Under Review"]
            UC19["Finalize Report"]
            UC20["Approve Final Thesis"]
            UC21["Publish to Repository"]
        end
        
        %% Submission Review
        subgraph Submissions["Submission Review"]
            UC22["View Student Submissions"]
            UC23["Download Submissions"]
            UC24["View Inline"]
        end
        
        %% Report Annotations
        subgraph Annotations["Report Annotations"]
            UC25["Annotate PDF"]
            UC26["Save Draft Annotations"]
            UC27["Send Feedback"]
            UC28["Notify Students"]
            UC29["View Annotation History"]
        end
    end
    
    %% Connections
    Supervisor --> UC1
    Supervisor --> UC2
    Supervisor --> UC3
    Supervisor --> UC4
    Supervisor --> UC5
    Supervisor --> UC6
    Supervisor --> UC7
    Supervisor --> UC8
    Supervisor --> UC9
    Supervisor --> UC10
    Supervisor --> UC11
    Supervisor --> UC12
    Supervisor --> UC13
    Supervisor --> UC14
    Supervisor --> UC15
    Supervisor --> UC16
    Supervisor --> UC17
    Supervisor --> UC18
    Supervisor --> UC19
    Supervisor --> UC20
    Supervisor --> UC21
    Supervisor --> UC22
    Supervisor --> UC23
    Supervisor --> UC24
    Supervisor --> UC25
    Supervisor --> UC26
    Supervisor --> UC27
    Supervisor --> UC28
    Supervisor --> UC29
    
    %% Include relationships
    UC20 -.includes.-> UC21
    UC27 -.includes.-> UC28
    UC12 -.includes.-> UC13
    
    %% Styling
    classDef actor fill:#fff3e0,stroke:#e65100,stroke-width:3px
    classDef usecase fill:#e8f5e9,stroke:#2e7d32,stroke-width:1px
    classDef subsystem fill:#f5f5f5,stroke:#616161,stroke-width:2px
    
    class Supervisor actor
    class UC1,UC2,UC3,UC4,UC5,UC6,UC7,UC8,UC9,UC10,UC11,UC12,UC13,UC14,UC15,UC16,UC17,UC18,UC19,UC20,UC21,UC22,UC23,UC24,UC25,UC26,UC27,UC28,UC29 usecase
```

## Simplified Version for Better Readability

```mermaid
graph LR
    %% Actor
    actor Supervisor
    
    %% System Boundary
    subgraph System["Supervisor Management System"]
        %% Core Use Cases
        UC1["Manage Groups - View Assignments, Set Permissions, Track Roles"]
        UC2["Conduct Meetings - Schedule, Track Attendance, Export PDF"]
        UC3["Manage Reports - Create/Edit, Review Status, Final Approval"]
        UC4["Review Submissions - View/Download, Annotate PDFs, Send Feedback"]
        UC5["Thesis Approval - Finalize, Approve, Publish"]
    end
    
    %% Connections
    Supervisor --> UC1
    Supervisor --> UC2
    Supervisor --> UC3
    Supervisor --> UC4
    Supervisor --> UC5
    
    %% Styling
    classDef usecase fill:#e0f2f1,stroke:#00695c,stroke-width:2px
    
    class UC1,UC2,UC3,UC4,UC5 usecase
    
    style Supervisor fill:#ffecb3,stroke:#ff6f00,stroke-width:3px
```

## Detailed Workflow Diagram

```mermaid
graph TD
    %% Actor
    actor Supervisor
    
    %% Main Workflows
    subgraph Workflows["Supervisor Workflows"]
        %% Group Management Flow
        subgraph GroupFlow["Group Management"]
            ViewGroups[View Groups]
            SetPerms[Set Co-Supervisor Permissions]
            ViewGroups --> SetPerms
        end
        
        %% Meeting Flow
        subgraph MeetingFlow["Meeting Workflow"]
            CreateMeet[Create Meeting]
            RecordAttend[Record Attendance]
            ExportPDF[Export to PDF]
            CreateMeet --> RecordAttend
            RecordAttend --> ExportPDF
        end
        
        %% Report Flow
        subgraph ReportFlow["Report Workflow"]
            CreateReport[Create Report]
            Review[Mark Under Review]
            Annotate[Annotate Submission]
            Approve[Approve Thesis]
            Publish[Publish to Repository]
            
            CreateReport --> Review
            Review --> Annotate
            Annotate --> Approve
            Approve --> Publish
        end
    end
    
    Supervisor --> ViewGroups
    Supervisor --> CreateMeet
    Supervisor --> CreateReport
    
    %% Styling
    classDef process fill:#f5f5f5,stroke:#424242,stroke-width:1px
    classDef workflow fill:#fafafa,stroke:#9e9e9e,stroke-width:2px
    
    class ViewGroups,SetPerms,CreateMeet,RecordAttend,ExportPDF,CreateReport,Review,Annotate,Approve,Publish process
    
    style Supervisor fill:#ffe0b2,stroke:#e65100,stroke-width:3px
```

## Key Use Cases

### Group Management
- **View Assignments**: See all groups where assigned as main, co-supervisor, or panel member
- **Permission Control**: Toggle meeting management permissions for co-supervisors
- **Role Visibility**: Clear distinction between different supervisor roles

### Meeting Management
- **Full CRUD Operations**: Create, view, edit meetings for managed groups
- **Filtering**: Filter meetings by group or date range
- **Student Data**: Fetch group student information via JSON
- **Export Capability**: Generate PDF reports with attendance records

### Report Management
- **Complete Lifecycle**: Create, edit, review, and finalize reports
- **Status Tracking**: Mark reports as under review
- **Final Approval**: Approve and publish final thesis to public repository
- **Version Control**: Maintain report history and changes

### Submission Review & Annotation
- **Multiple Formats**: View submissions inline or download for offline review
- **PDF Annotation**: Comprehensive annotation tools for PDF documents
- **Draft Management**: Save annotation drafts before sending
- **Student Notification**: Automatic notification when feedback is sent
- **History Tracking**: View complete annotation history per submission

## Extended Use Cases

```mermaid
graph TB
    %% Extended functionality
    subgraph Extended["Extended Features"]
        UC30[Batch Operations]
        UC31[Generate Reports]
        UC32[Track Progress]
        UC33[Communication]
    end
    
    UC30 --> BulkApprove[Bulk Approve]
    UC30 --> BulkExport[Bulk Export]
    
    UC31 --> ProgressReport[Progress Reports]
    UC31 --> Statistics[Group Statistics]
    
    UC32 --> Timeline[View Timeline]
    UC32 --> Milestones[Track Milestones]
    
    UC33 --> Comments[Add Comments]
    UC33 --> Notifications[Send Notifications]
    
    actor Supervisor2 as "Supervisor"
    Supervisor2 --> Extended
    
    %% Styling
    classDef extended fill:#e8eaf6,stroke:#3f51b5,stroke-width:1px
```

## Access Paths
- `/supervisor/dashboard` - Main supervisor dashboard
- `/supervisor/groups` - Group management interface
- `/supervisor/meetings` - Meeting scheduling and management
- `/supervisor/reports` - Report creation and management

## Permissions Matrix

| Feature | Main Supervisor | Co-Supervisor | Panel Member |
|---------|----------------|---------------|--------------|
| Create Meetings | ✓ | With Permission | ✗ |
| Edit Meetings | ✓ | With Permission | ✗ |
| Create Reports | ✓ | ✗ | ✗ |
| Delete Reports | ✓ | ✗ | ✗ |
| Final Approval | ✓ | ✗ | ✗ |
| Annotate | ✓ | ✓ | ✓ |
| View Submissions | ✓ | ✓ | ✓ |

## Notes
- Main supervisors have full control over their assigned groups
- Co-supervisor meeting permissions are granted per group
- Final thesis approval and repository publication are exclusive to main supervisors
- All annotations trigger automatic student notifications
- PDF export includes comprehensive meeting and attendance data