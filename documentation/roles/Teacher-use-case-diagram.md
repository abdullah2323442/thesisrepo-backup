# Teacher Use Case Diagram

## Overview
Teacher is an umbrella role for faculty members who can access supervisor, co-supervisor, and panel member functionalities based on their assignments.

## Use Case Diagram

```mermaid
graph TB
    %% Actor
    Teacher[("👤 Teacher")]
    
    %% System Boundary
    subgraph System["Thesis Management System - Teacher Module"]
        %% Faculty Dashboard
        subgraph FacultyDashboard["Faculty Dashboard"]
            UC1["View Teacher Dashboard"]
            UC2["Navigate to Role Dashboards"]
            UC3["Switch Between Roles"]
        end
        
        %% Role-Based Access
        subgraph RoleAccess["Role-Based Access"]
            UC4["Access Supervisor Dashboard<br/>(if assigned as supervisor)"]
            UC5["Access Co-Supervisor Dashboard<br/>(if assigned as co-supervisor)"]
            UC6["Access Panel Member Dashboard<br/>(if assigned as panel member)"]
        end
        
        %% Supervisor Functions (When Applicable)
        subgraph SupervisorFunctions["As Supervisor"]
            UC7["Manage Meetings"]
            UC8["Create/Edit Reports"]
            UC9["Approve Thesis"]
            UC10["Annotate Submissions"]
        end
        
        %% Co-Supervisor Functions (When Applicable)
        subgraph CoSupervisorFunctions["As Co-Supervisor"]
            UC11["View Groups"]
            UC12["Conditional Meeting Management"]
            UC13["Review Reports"]
            UC14["Annotate Submissions"]
        end
        
        %% Panel Member Functions (When Applicable)
        subgraph PanelFunctions["As Panel Member"]
            UC15["Review Reports"]
            UC16["Annotate Submissions"]
            UC17["Mark Under Review"]
        end
        
        %% Teacher-Specific
        subgraph TeacherSpecific["Teacher-Specific Features"]
            UC18["Add Comments on Reports<br/>(for supervised groups)"]
            UC19["Trigger Student Notifications"]
        end
    end
    
    %% Connections
    Teacher --> UC1
    Teacher --> UC2
    Teacher --> UC3
    Teacher --> UC4
    Teacher --> UC5
    Teacher --> UC6
    
    UC4 --> UC7
    UC4 --> UC8
    UC4 --> UC9
    UC4 --> UC10
    
    UC5 --> UC11
    UC5 --> UC12
    UC5 --> UC13
    UC5 --> UC14
    
    UC6 --> UC15
    UC6 --> UC16
    UC6 --> UC17
    
    Teacher --> UC18
    UC18 -.includes.-> UC19
    
    %% Styling
    classDef actor fill:#ffecb3,stroke:#ff6f00,stroke-width:3px
    classDef usecase fill:#e8f5e9,stroke:#388e3c,stroke-width:1px
    classDef roleSpecific fill:#e3f2fd,stroke:#1976d2,stroke-width:1px,stroke-dasharray: 5 5
    classDef subsystem fill:#f5f5f5,stroke:#616161,stroke-width:2px
    
    class Teacher actor
    class UC1,UC2,UC3,UC18,UC19 usecase
    class UC4,UC5,UC6,UC7,UC8,UC9,UC10,UC11,UC12,UC13,UC14,UC15,UC16,UC17 roleSpecific
```

## Simplified Multi-Role View

```mermaid
graph LR
    %% Actor
    Teacher((Teacher))
    
    %% System Boundary
    subgraph System["Teacher Multi-Role System"]
        %% Central Hub
        Hub[Teacher Dashboard<br/>Role Switcher]
        
        %% Role Branches
        subgraph Roles["Available Roles"]
            Supervisor[Supervisor Role<br/>- Full Management<br/>- Thesis Approval<br/>- Report Creation]
            CoSupervisor[Co-Supervisor Role<br/>- Conditional Meetings<br/>- Report Review<br/>- Annotations]
            Panel[Panel Member Role<br/>- Review Only<br/>- Annotations<br/>- Feedback]
        end
        
        %% Teacher Features
        Comments[Add Comments<br/>on Supervised Groups]
    end
    
    %% Connections
    Teacher --> Hub
    Hub --> Supervisor
    Hub --> CoSupervisor
    Hub --> Panel
    Teacher --> Comments
    
    %% Styling
    classDef actor fill:#ffe0b2,stroke:#ef6c00,stroke-width:3px
    classDef hub fill:#fff9c4,stroke:#f9a825,stroke-width:2px
    classDef role fill:#e1f5fe,stroke:#0277bd,stroke-width:1px
    classDef feature fill:#f3e5f5,stroke:#7b1fa2,stroke-width:1px
    
    class Teacher actor
    class Hub hub
    class Supervisor,CoSupervisor,Panel role
    class Comments feature
```

## Dynamic Role Assignment Flow

```mermaid
graph TD
    %% Actor
    Teacher((Teacher))
    
    %% Role Detection
    subgraph RoleDetection["Dynamic Role Detection"]
        Login[Teacher Login]
        CheckRoles{Check Assigned Roles}
        
        %% Role Availability
        HasSuper{Supervisor<br/>Assignments?}
        HasCoSuper{Co-Supervisor<br/>Assignments?}
        HasPanel{Panel Member<br/>Assignments?}
        
        %% Enable Dashboards
        EnableSuper[Enable Supervisor Dashboard]
        EnableCoSuper[Enable Co-Supervisor Dashboard]
        EnablePanel[Enable Panel Dashboard]
        
        %% Combined Access
        CombinedDash[Show All Available Dashboards]
    end
    
    Teacher --> Login
    Login --> CheckRoles
    CheckRoles --> HasSuper
    CheckRoles --> HasCoSuper
    CheckRoles --> HasPanel
    
    HasSuper -->|Yes| EnableSuper
    HasCoSuper -->|Yes| EnableCoSuper
    HasPanel -->|Yes| EnablePanel
    
    EnableSuper --> CombinedDash
    EnableCoSuper --> CombinedDash
    EnablePanel --> CombinedDash
    
    %% Styling
    classDef actor fill:#ffccbc,stroke:#d84315,stroke-width:3px
    classDef process fill:#ffffff,stroke:#424242,stroke-width:1px
    classDef decision fill:#fff3e0,stroke:#e65100,stroke-width:2px
    classDef enabled fill:#c8e6c9,stroke:#2e7d32,stroke-width:1px
    
    class Teacher actor
    class Login,CheckRoles,CombinedDash process
    class HasSuper,HasCoSuper,HasPanel decision
    class EnableSuper,EnableCoSuper,EnablePanel enabled
```

## Role-Based Capabilities Matrix

| Capability | As Supervisor | As Co-Supervisor | As Panel Member | Teacher-Specific |
|------------|--------------|------------------|-----------------|------------------|
| View Groups | ✓ | ✓ | ✓ | ✓ |
| Create Meetings | ✓ | Conditional | ✗ | - |
| Create Reports | ✓ | ✗ | ✗ | - |
| Delete Reports | ✓ | ✗ | ✗ | - |
| Approve Thesis | ✓ | ✗ | ✗ | - |
| Annotate | ✓ | ✓ | ✓ | - |
| Add Comments | ✓ | ✗ | ✗ | ✓ (on supervised) |
| Switch Roles | - | - | - | ✓ |

## Use Case Scenarios

```mermaid
graph LR
    subgraph Scenario1["Scenario 1: Multiple Roles"]
        T1((Teacher A))
        T1 --> S1[Supervisor for Group 1]
        T1 --> C1[Co-Supervisor for Group 2]
        T1 --> P1[Panel for Group 3]
    end
    
    subgraph Scenario2["Scenario 2: Single Role"]
        T2((Teacher B))
        T2 --> S2[Supervisor for Groups 4,5,6]
    end
    
    subgraph Scenario3["Scenario 3: Review Only"]
        T3((Teacher C))
        T3 --> P3[Panel for Groups 7,8]
    end
    
    %% Styling
    classDef teacher fill:#fff8e1,stroke:#ff8f00,stroke-width:2px
    classDef role fill:#e8eaf6,stroke:#5e35b1,stroke-width:1px
```

## Comment System (Teacher-Specific)

```mermaid
graph TD
    subgraph CommentSystem["Teacher Comment System"]
        CheckSuper{Is Main Supervisor<br/>for Group?}
        
        AllowComment[Allow Comment]
        AddComment[Add Comment to Report]
        NotifyStudent[Send Notification]
        SaveHistory[Save to History]
        
        DenyComment[Comment Not Allowed]
        
        CheckSuper -->|Yes| AllowComment
        CheckSuper -->|No| DenyComment
        
        AllowComment --> AddComment
        AddComment --> NotifyStudent
        AddComment --> SaveHistory
    end
    
    Teacher2((Teacher)) --> CheckSuper
    
    %% Styling
    classDef allowed fill:#c8e6c9,stroke:#2e7d32,stroke-width:1px
    classDef denied fill:#ffcdd2,stroke:#c62828,stroke-width:1px
    
    class AllowComment,AddComment,NotifyStudent,SaveHistory allowed
    class DenyComment denied
```

## Access Paths
- `/teacher/dashboard` - Main teacher dashboard with role switcher
- `/supervisor/dashboard` - When assigned as supervisor
- `/co-supervisor/dashboard` - When assigned as co-supervisor
- `/panel-member/dashboard` - When assigned as panel member

## Navigation Flow

```mermaid
sequenceDiagram
    participant T as Teacher
    participant Sys as System
    participant SD as Supervisor Dashboard
    participant CD as Co-Supervisor Dashboard
    participant PD as Panel Dashboard
    
    T->>Sys: Login as Teacher
    Sys->>Sys: Check role assignments
    Sys-->>T: Show Teacher Dashboard
    
    T->>Sys: Request available roles
    Sys-->>T: Display role options
    
    alt Select Supervisor Role
        T->>SD: Navigate to Supervisor
        SD-->>T: Supervisor features
    else Select Co-Supervisor Role
        T->>CD: Navigate to Co-Supervisor
        CD-->>T: Co-Supervisor features
    else Select Panel Role
        T->>PD: Navigate to Panel
        PD-->>T: Panel features
    end
    
    T->>Sys: Add comment (if supervisor)
    Sys->>Sys: Verify supervisor status
    Sys-->>T: Comment added & notification sent
```

## Key Features

### Faculty Dashboard
- **Unified Access Point**: Single entry for all faculty roles
- **Role Switcher**: Easy navigation between assigned roles
- **Overview**: See all assignments across different roles

### Dynamic Role Access
- **Automatic Detection**: System identifies all assigned roles
- **Conditional Display**: Only show relevant dashboards
- **Seamless Switching**: Move between roles without re-login

### Comment System
- **Supervisor-Only**: Comments restricted to main supervisors
- **Student Notifications**: Automatic notification on new comments
- **Audit Trail**: Complete history of all comments

## Notes
- Teacher role serves as an umbrella for all faculty positions
- Access to specific features depends on actual role assignments
- Comment feature is unique to teacher role when acting as supervisor
- Role switching provides flexibility for multi-role faculty
- All role-specific permissions are enforced at the system level