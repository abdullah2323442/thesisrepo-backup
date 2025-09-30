# Simplified High-Level Use Case Diagram
## Thesis Management System - Core Features

This simplified diagram shows only the essential high-level features for easy understanding and evaluation.

```mermaid
%%{init: {'theme':'base', 'themeVariables': { 'fontSize':'14px'}}}%%
graph LR
    %% Actors (Outside System)
    Student([👤 Student])
    Advisor([👤 Advisor])
    Supervisor([👤 Supervisor])
    Admin([👤 Admin])
    ExtAPI([🌐 External API])
    
    %% System Boundary
    subgraph System["<b>Thesis Management System</b>"]
        direction TB
        
        %% Core Use Cases - Organized by Function
        %% Authentication & Access
        UC1((Authenticate<br/>Users))
        
        %% Group Management
        UC2((Manage<br/>Groups))
        UC3((Assign<br/>Supervisors))
        
        %% Academic Activities
        UC4((Submit<br/>Reports))
        UC5((Review<br/>Reports))
        UC6((Schedule<br/>Meetings))
        
        %% System Administration
        UC7((Manage<br/>System))
        UC8((Sync External<br/>Data))
        
        %% Advanced Features
        UC9((Run Lottery<br/>Assignment))
        UC10((Approve<br/>Thesis))
        
        %% Include/Extend Relationships
        UC9 -.->|<<include>>| UC3
        UC5 -.->|<<extend>>| UC10
    end
    
    %% Actor Connections to Use Cases
    
    %% Student
    Student --> UC1
    Student --> UC4
    Student --> UC6
    
    %% Advisor
    Advisor --> UC1
    Advisor --> UC2
    Advisor --> UC3
    Advisor --> UC9
    
    %% Supervisor
    Supervisor --> UC1
    Supervisor --> UC5
    Supervisor --> UC6
    Supervisor --> UC10
    
    %% Admin
    Admin --> UC1
    Admin --> UC2
    Admin --> UC7
    Admin --> UC8
    
    %% External API
    ExtAPI -.->|provides data| UC8
    ExtAPI -.->|authenticates| UC1
    
    %% Styling for clarity
    classDef actorStyle fill:#4A90E2,stroke:#2E5C8A,stroke-width:3px,color:#fff,font-weight:bold
    classDef useCaseStyle fill:#5DADE2,stroke:#2874A6,stroke-width:2px,color:#fff
    classDef systemStyle fill:#ffffff,stroke:#34495E,stroke-width:3px
    
    class Student,Advisor,Supervisor,Admin,ExtAPI actorStyle
    class UC1,UC2,UC3,UC4,UC5,UC6,UC7,UC8,UC9,UC10 useCaseStyle
```

## Core System Goals

### 1. **Authentication & Access Control**
- **Authenticate Users**: Secure login for all user types
- Integration with external university API for students/teachers

### 2. **Group Management**
- **Manage Groups**: Create, modify, and organize thesis groups
- **Assign Supervisors**: Match supervisors to student groups
- **Run Lottery Assignment**: Automated fair supervisor distribution

### 3. **Academic Workflow**
- **Submit Reports**: Students submit thesis work
- **Review Reports**: Supervisors evaluate submissions
- **Approve Thesis**: Final approval of completed work
- **Schedule Meetings**: Coordinate supervisor-student meetings

### 4. **System Administration**
- **Manage System**: Configure settings, users, and resources
- **Sync External Data**: Import student/teacher data from university

## Key Actors

| Actor | Primary Responsibilities |
|-------|-------------------------|
| **Student** | Submit reports, attend meetings, view progress |
| **Advisor** | Manage groups, assign supervisors, oversee students |
| **Supervisor** | Review work, approve thesis, guide students |
| **Admin** | System configuration, data management, monitoring |
| **External API** | Provide authentication and user data |

## Main System Benefits

1. **Streamlined Thesis Management**: Automates group formation and supervisor assignment
2. **Fair Distribution**: Lottery algorithm ensures equitable supervisor allocation
3. **Progress Tracking**: Clear workflow from submission to approval
4. **Integration**: Seamless connection with university systems
5. **Role-Based Access**: Each user sees only relevant features

## Use Case Relationships

- **Include**: Lottery assignment automatically includes supervisor assignment
- **Extend**: Report review may extend to final thesis approval
- **External Dependency**: Authentication and data sync rely on external API

---

*This simplified diagram focuses on the 10 core features that define the system's primary purpose: managing thesis groups, assignments, and the academic review process.*