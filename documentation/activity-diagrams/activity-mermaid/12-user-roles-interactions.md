# User Roles and Interactions

```mermaid
graph TB
    subgraph "System Roles"
        Admin[Admin<br/>• System Config<br/>• User Management<br/>• Performance Monitor]
        Advisor[Advisor<br/>• Create Groups<br/>• Assign Students<br/>• Run Lottery]
        Supervisor[Supervisor<br/>• Review Reports<br/>• Annotate PDFs<br/>• Schedule Meetings]
        Student[Student<br/>• Submit Reports<br/>• View Feedback<br/>• Attend Meetings]
        Panel[Panel Member<br/>• Final Evaluation<br/>• Grade Assignment]
        CoSup[Co-Supervisor<br/>• Assist Review<br/>• Additional Feedback]
    end
    
    Admin -->|Creates & Manages| Advisor
    Admin -->|Syncs from API| Supervisor
    
    Advisor -->|Assigns Groups| Supervisor
    Advisor -->|Creates Groups for| Student
    
    Supervisor -->|Reviews Work| Student
    Supervisor -->|Collaborates| CoSup
    Supervisor -->|Submits to| Panel
    
    Student -->|Submits Reports| Supervisor
    Student -->|Final Defense| Panel
    
    CoSup -->|Supports| Student
    Panel -->|Final Grade| Student
    
    style Admin fill:#FFCDD2
    style Advisor fill:#C5CAE9
    style Supervisor fill:#C8E6C9
    style Student fill:#FFE0B2
    style Panel fill:#E1BEE7
    style CoSup fill:#B2DFDB
```

## Description
Visual representation of all system roles and their interactions.

## Role Hierarchy
1. **Admin**: System-level management
2. **Advisor**: Group and assignment management
3. **Supervisor**: Primary thesis supervision
4. **Co-Supervisor**: Secondary supervision support
5. **Student**: Thesis work and submission
6. **Panel Member**: Final evaluation

## Key Interactions
- Admin manages all system users
- Advisor bridges admin and academic roles
- Supervisor-Student is the core interaction
- Panel provides final assessment