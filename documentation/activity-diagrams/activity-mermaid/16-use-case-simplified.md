# Use Case Diagram - Simplified A4 Layout

```mermaid
flowchart LR
    %% Actors Column
    subgraph Actors[" "]
        direction TB
        Student[Student]
        Supervisor[Supervisor]
        Advisor[Advisor]
        Admin[Admin]
        Panel[Panel Member]
    end
    
    %% Core Features Column
    subgraph Core["Core Features"]
        direction TB
        Submit[Submit Report]
        Review[Review & Annotate]
        Meeting[Meetings]
        Groups[Group Management]
        Lottery[Supervisor Assignment]
    end
    
    %% Support Features Column
    subgraph Support["Support Features"]
        direction TB
        Feedback[View Feedback]
        Track[Track Progress]
        Config[System Config]
        Users[User Management]
        Evaluate[Final Evaluation]
    end
    
    %% Connections
    Student --> Submit
    Student --> Feedback
    Student --> Meeting
    Student --> Track
    
    Supervisor --> Review
    Supervisor --> Meeting
    Supervisor --> Feedback
    
    Advisor --> Groups
    Advisor --> Lottery
    
    Admin --> Config
    Admin --> Users
    
    Panel --> Evaluate
    Panel --> Review
    
    %% Feature Dependencies
    Submit -.-> Review
    Review -.-> Feedback
    Groups -.-> Lottery
    Evaluate -.-> Feedback
    
    %% Styling
    style Actors fill:#E8EAF6,stroke:#3F51B5
    style Core fill:#E8F5E9,stroke:#4CAF50
    style Support fill:#FFF3E0,stroke:#FF9800
    
    classDef actorStyle fill:#E3F2FD,stroke:#1976D2,stroke-width:2px
    classDef featureStyle fill:#FFF9C4,stroke:#F57F17,stroke-width:1px
    
    class Student,Supervisor,Advisor,Admin,Panel actorStyle
    class Submit,Review,Meeting,Groups,Lottery,Feedback,Track,Config,Users,Evaluate featureStyle
```

## System Use Cases - Quick Reference

### Primary Workflows

**📝 Report Submission Flow**
```
Student → Submit Report → Supervisor Review → Feedback → Student
```

**👥 Group Formation Flow**
```
Advisor → Create Groups → Assign Students → Run Lottery → Assign Supervisors
```

**📊 Evaluation Flow**
```
Panel → Review Final Reports → Evaluate → Assign Grades
```

### Actor Permissions Matrix

| Feature | Student | Supervisor | Advisor | Admin | Panel |
|---------|---------|------------|---------|-------|-------|
| Submit Report | ✅ | - | - | - | - |
| Review Report | View | ✅ | View | View | ✅ |
| Annotate PDF | - | ✅ | - | - | ✅ |
| Schedule Meeting | Request | ✅ | - | - | - |
| Create Groups | - | - | ✅ | ✅ | - |
| Run Lottery | - | - | ✅ | - | - |
| System Config | - | - | - | ✅ | - |
| Final Evaluation | - | - | - | - | ✅ |

### Key System Interactions

1. **Students** interact primarily with submission and feedback features
2. **Supervisors** focus on review, annotation, and guidance
3. **Advisors** manage group formation and assignments
4. **Admins** handle system-level configuration
5. **Panel Members** perform final evaluations