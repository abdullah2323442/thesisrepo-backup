# Use Case Diagram - Compact A4 Version

```mermaid
graph TB
    %% Define Actors with compact styling
    Student([👤 Student])
    Supervisor([👨‍🏫 Supervisor])
    Advisor([👥 Advisor])
    Admin([⚙️ Admin])
    Panel([📋 Panel])
    
    %% Core System Box
    subgraph System[" "]
        %% Student Use Cases
        subgraph S[" "]
            UC1(Submit Report)
            UC2(View Feedback)
            UC3(Attend Meeting)
        end
        
        %% Supervisor Use Cases
        subgraph T[" "]
            UC4(Review Report)
            UC5(Annotate PDF)
            UC6(Schedule Meeting)
        end
        
        %% Advisor Use Cases
        subgraph A[" "]
            UC7(Create Groups)
            UC8(Run Lottery)
            UC9(Assign Students)
        end
        
        %% Admin Use Cases
        subgraph D[" "]
            UC10(Manage Users)
            UC11(Configure System)
            UC12(Sync Data)
        end
        
        %% Panel Use Cases
        subgraph P[" "]
            UC13(Evaluate Thesis)
            UC14(Assign Grades)
        end
    end
    
    %% Actor Connections
    Student --> UC1
    Student --> UC2
    Student --> UC3
    
    Supervisor --> UC4
    Supervisor --> UC5
    Supervisor --> UC6
    
    Advisor --> UC7
    Advisor --> UC8
    Advisor --> UC9
    
    Admin --> UC10
    Admin --> UC11
    Admin --> UC12
    
    Panel --> UC13
    Panel --> UC14
    
    %% Key Relationships
    UC1 -.-> UC4
    UC4 -.-> UC5
    UC7 -.-> UC8
    
    %% Styling
    classDef actor fill:#E3F2FD,stroke:#1976D2,stroke-width:2px
    classDef usecase fill:#FFF3E0,stroke:#F57C00,stroke-width:1px
    classDef system fill:#FAFAFA,stroke:#9E9E9E,stroke-width:2px
    
    class Student,Supervisor,Advisor,Admin,Panel actor
    class UC1,UC2,UC3,UC4,UC5,UC6,UC7,UC8,UC9,UC10,UC11,UC12,UC13,UC14 usecase
    class System system
```

## Thesis Management System - Core Use Cases

### Actors & Primary Functions

| Actor | Key Responsibilities |
|-------|---------------------|
| **Student** | Submit reports, view feedback, attend meetings |
| **Supervisor** | Review reports, annotate PDFs, schedule meetings |
| **Advisor** | Create groups, run lottery, assign students |
| **Admin** | Manage users, configure system, sync data |
| **Panel** | Evaluate thesis, assign final grades |

### Use Case Categories

**📚 Student Activities**
- Submit Report → Triggers review workflow
- View Feedback → Access annotations and comments
- Attend Meeting → Participate in scheduled sessions

**👨‍🏫 Supervision Activities**
- Review Report → Evaluate submissions
- Annotate PDF → Add detailed feedback
- Schedule Meeting → Organize student meetings

**👥 Group Management**
- Create Groups → Form thesis groups
- Run Lottery → Automated supervisor assignment
- Assign Students → Manual student allocation

**⚙️ Administration**
- Manage Users → User account control
- Configure System → System settings
- Sync Data → External API integration

**📋 Evaluation**
- Evaluate Thesis → Final assessment
- Assign Grades → Grade determination