# Comprehensive Use Case Diagram

## Overview
This diagram shows all system actors and their interactions with the Thesis Management System, following the reference design style.

## Complete System Use Case Diagram

```mermaid
graph LR
    %% Actors
    Admin((Admin))
    CoSupervisor((Co-Supervisor))
    Supervisor((Supervisor))
    Advisor((Advisor))
    PanelMember((Panel Member))
    Student((Student))
    
    %% System Boundary
    subgraph System["Thesis Management System"]
        %% Use Cases
        UC1[Manage AOI & Batches]
        UC2[Group Management]
        UC3[Supervisor Assignment]
        UC4[Meeting Management]
        UC5[Report Annotation]
        UC6[Report Management]
        UC7[Thesis Approval]
        UC8[Document Submission]
        UC9[View Dashboard]
    end
    
    %% Admin Connections (Red)
    Admin ---|red| UC1
    Admin ---|red| UC2
    
    %% Advisor Connections (Blue)
    Advisor ---|blue| UC2
    Advisor ---|blue| UC3
    
    %% Co-Supervisor Connections (Green)
    CoSupervisor ---|green| UC4
    
    %% Supervisor Connections (Pink)
    Supervisor ---|pink| UC4
    Supervisor ---|pink| UC5
    Supervisor ---|pink| UC6
    Supervisor ---|pink| UC7
    
    %% Panel Member Connections (Teal)
    PanelMember ---|teal| UC5
    
    %% Student Connections (Purple)
    Student ---|purple| UC8
    Student ---|purple| UC9
    Student ---|purple| UC7
    
    %% Styling
    classDef adminActor fill:#ff6b6b,stroke:#000,stroke-width:2px,color:#fff
    classDef advisorActor fill:#2196f3,stroke:#000,stroke-width:2px,color:#fff
    classDef coSupervisorActor fill:#4caf50,stroke:#000,stroke-width:2px,color:#fff
    classDef supervisorActor fill:#e91e63,stroke:#000,stroke-width:2px,color:#fff
    classDef panelMemberActor fill:#009688,stroke:#000,stroke-width:2px,color:#fff
    classDef studentActor fill:#9c27b0,stroke:#000,stroke-width:2px,color:#fff
    classDef system fill:#4dabf7,stroke:#000,stroke-width:2px
    classDef usecase fill:#ffffff,stroke:#000,stroke-width:1px
    
    class Admin adminActor
    class Advisor advisorActor
    class CoSupervisor coSupervisorActor
    class Supervisor supervisorActor
    class PanelMember panelMemberActor
    class Student studentActor
    class System system
    class UC1,UC2,UC3,UC4,UC5,UC6,UC7,UC8,UC9 usecase
```

## Detailed Actor-Use Case Relationships

### Admin Use Cases
- **Manage AOI & Batches**: Create, edit, delete areas of interest and manage batch synchronization
- **Group Management**: Global group creation and management across all batches

### Advisor Use Cases
- **Group Management**: Create student groups with capacity of 3 students
- **Supervisor Assignment**: Assign supervisors using manual or lottery methods

### Supervisor Use Cases
- **Meeting Management**: Schedule and manage thesis meetings
- **Report Annotation**: Provide feedback on student submissions
- **Report Management**: Create and manage thesis reports
- **Thesis Approval**: Final approval and repository publication

### Co-Supervisor Use Cases
- **Meeting Management**: Conditional meeting management based on permissions

### Panel Member Use Cases
- **Report Annotation**: Review and annotate student submissions

### Student Use Cases
- **Document Submission**: Upload thesis documents (PDF, PPT, PPTX)
- **View Dashboard**: Access group information and progress
- **Thesis Approval**: View approval status

## Permission Matrix

| Use Case | Admin | Advisor | Supervisor | Co-Supervisor | Panel Member | Student |
|----------|-------|---------|------------|---------------|--------------|---------|
| Manage AOI & Batches | ✓ | - | - | - | - | - |
| Group Management | ✓ | ✓ | - | - | - | - |
| Supervisor Assignment | - | ✓ | - | - | - | - |
| Meeting Management | - | - | ✓ | Conditional | - | - |
| Report Annotation | - | - | ✓ | ✓ | ✓ | - |
| Report Management | - | - | ✓ | - | - | - |
| Thesis Approval | - | - | ✓ | - | - | View Only |
| Document Submission | - | - | - | - | - | ✓ |
| View Dashboard | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |

## System Architecture Context

```mermaid
graph TB
    subgraph External["External Systems"]
        API[University API]
        Repo[Public Repository]
    end
    
    subgraph TMS["Thesis Management System"]
        Core[Core System]
    end
    
    subgraph Actors["System Actors"]
        A[Admin]
        Ad[Advisor]
        S[Supervisor]
        CS[Co-Supervisor]
        PM[Panel Member]
        St[Student]
    end
    
    API -.-> Core
    Core -.-> Repo
    
    A --> Core
    Ad --> Core
    S --> Core
    CS --> Core
    PM --> Core
    St --> Core
    
    %% Styling
    classDef external fill:#ffecb3,stroke:#ff6f00,stroke-width:2px
    classDef system fill:#e3f2fd,stroke:#1565c0,stroke-width:2px
    classDef actor fill:#f5f5f5,stroke:#616161,stroke-width:1px
    
    class API,Repo external
    class Core,TMS system
    class A,Ad,S,CS,PM,St actor
```

## Key System Features by Role

### Administrative Features
- **Admin**: System configuration, monitoring, global management
- **Advisor**: Group formation, supervisor assignment algorithms

### Supervisory Features
- **Supervisor**: Full thesis guidance and approval authority
- **Co-Supervisor**: Assisted guidance with conditional permissions
- **Panel Member**: Review and feedback only

### End-User Features
- **Student**: Document submission, progress tracking, notifications

## Access Control Levels

1. **Level 1 - Admin**: Full system access
2. **Level 2 - Advisor**: Group and assignment management
3. **Level 3 - Supervisor**: Thesis management and approval
4. **Level 4 - Co-Supervisor**: Conditional management access
5. **Level 5 - Panel Member**: Review access only
6. **Level 6 - Student**: Submission and view access

## Notes
- The system follows a hierarchical permission structure
- Each role has specific use cases aligned with their responsibilities
- Color coding helps identify actor-use case relationships
- The reference design style emphasizes clarity and simplicity