# Use Case Diagram - Best A4 Version

```mermaid
flowchart TB
    %% Title
    subgraph title[" "]
        Title[<b>Thesis Management System - Use Cases</b>]
    end
    
    %% Actors
    Student((Student))
    Supervisor((Supervisor))
    Advisor((Advisor))
    Admin((Admin))
    Panel((Panel))
    API[(External<br/>API)]
    
    %% Main System Container
    subgraph System[" "]
        %% Core Features
        subgraph Core["Core Features"]
            UC1[Submit Report]
            UC2[Review & Annotate]
            UC3[Provide Feedback]
            UC4[Schedule Meeting]
            UC5[Track Progress]
        end
        
        %% Management Features
        subgraph Mgmt["Management"]
            UC6[Create Groups]
            UC7[Assign Students]
            UC8[Run Lottery]
            UC9[Manage Users]
            UC10[Configure System]
        end
        
        %% Evaluation Features
        subgraph Eval["Evaluation"]
            UC11[Evaluate Thesis]
            UC12[Assign Grades]
            UC13[Conduct Defense]
        end
    end
    
    %% Actor Connections
    Student --> UC1
    Student --> UC5
    Student --> UC4
    
    Supervisor --> UC2
    Supervisor --> UC3
    Supervisor --> UC4
    
    Advisor --> UC6
    Advisor --> UC7
    Advisor --> UC8
    
    Admin --> UC9
    Admin --> UC10
    
    Panel --> UC11
    Panel --> UC12
    Panel --> UC13
    
    API -.-> UC10
    API -.-> UC7
    
    %% Use Case Dependencies
    UC1 ==> UC2
    UC2 ==> UC3
    UC6 ==> UC7
    UC7 ==> UC8
    UC11 ==> UC12
    
    %% Styling
    style System fill:#FAFAFA,stroke:#666,stroke-width:2px
    style Core fill:#E8F5E9,stroke:#4CAF50
    style Mgmt fill:#E3F2FD,stroke:#2196F3
    style Eval fill:#FFF3E0,stroke:#FF9800
    
    classDef actor fill:#F5F5F5,stroke:#333,stroke-width:2px
    classDef feature fill:#FFF,stroke:#666,stroke-width:1px
    
    class Student,Supervisor,Advisor,Admin,Panel actor
    class UC1,UC2,UC3,UC4,UC5,UC6,UC7,UC8,UC9,UC10,UC11,UC12,UC13 feature
```

---

## 📋 Use Case Quick Reference

### Primary Actors & Responsibilities

| **Actor** | **Main Functions** | **Access Level** |
|-----------|-------------------|-----------------|
| 👤 **Student** | Submit reports, View feedback, Track progress | Basic User |
| 👨‍🏫 **Supervisor** | Review & annotate, Provide guidance, Schedule meetings | Reviewer |
| 👥 **Advisor** | Create groups, Manage assignments, Run lottery | Manager |
| ⚙️ **Admin** | System configuration, User management, Monitoring | Administrator |
| 📊 **Panel** | Final evaluation, Grade assignment, Defense | Evaluator |

### Core System Workflows

```
1. Report Submission:  Student → Submit → Supervisor → Review → Feedback → Student
2. Group Formation:    Advisor → Create → Assign → Lottery → Supervisor Assignment
3. Final Evaluation:   Supervisor → Finalize → Panel → Evaluate → Grade
```

### Feature Categories

**🟢 Core Features** - Daily operations
- Submit/Review reports
- Annotation & feedback
- Meeting management
- Progress tracking

**🔵 Management** - Administrative tasks
- Group creation
- Student assignment
- Supervisor lottery
- User management

**🟠 Evaluation** - Assessment activities
- Thesis evaluation
- Grade assignment
- Defense conduction

### System Interactions

| From | To | Action |
|------|-----|--------|
| Student | System | Submits reports |
| System | Supervisor | Notifies for review |
| Supervisor | Student | Provides feedback |
| Advisor | System | Configures groups |
| System | API | Syncs data |
| Panel | System | Records grades |

---
*Optimized for A4 printing - Use landscape orientation for best results*