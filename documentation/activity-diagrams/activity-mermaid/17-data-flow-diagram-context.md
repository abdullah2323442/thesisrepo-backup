# Data Flow Diagram - Context Level (Level 0)

```mermaid
flowchart TB
    %% External Entities
    Student[Student<br/>External Entity]
    Supervisor[Supervisor<br/>External Entity]
    Advisor[Advisor<br/>External Entity]
    Admin[Admin<br/>External Entity]
    Panel[Panel Member<br/>External Entity]
    API[External API<br/>External Entity]
    Email[Email System<br/>External Entity]
    
    %% Central System
    TMS((Thesis<br/>Management<br/>System))
    
    %% Data Flows from entities to system
    Student -->|Submit Reports<br/>View Feedback<br/>Attend Meetings| TMS
    TMS -->|Notifications<br/>Annotated PDFs<br/>Grades| Student
    
    Supervisor -->|Review Reports<br/>Provide Feedback<br/>Schedule Meetings| TMS
    TMS -->|Group Assignments<br/>Student Submissions<br/>Progress Reports| Supervisor
    
    Advisor -->|Create Groups<br/>Assign Students<br/>Run Lottery| TMS
    TMS -->|Assignment Results<br/>Group Statistics| Advisor
    
    Admin -->|System Config<br/>User Management<br/>Performance Monitoring| TMS
    TMS -->|System Reports<br/>Performance Metrics| Admin
    
    Panel -->|Evaluations<br/>Grades| TMS
    TMS -->|Final Reports<br/>Thesis Documents| Panel
    
    API -->|Student Data<br/>Batch Info<br/>Teacher Data| TMS
    TMS -->|Data Requests<br/>Validation Queries| API
    
    TMS -->|Notification Emails| Email
    
    %% Styling
    style Student fill:#FFE0B2
    style Supervisor fill:#C8E6C9
    style Advisor fill:#C5CAE9
    style Admin fill:#FFCDD2
    style Panel fill:#E1BEE7
    style API fill:#FFF8E1
    style Email fill:#E0F2F1
    style TMS fill:#E3F2FD,stroke:#333,stroke-width:3px
```

## Description
This Level 0 (Context) Data Flow Diagram shows the thesis management system as a single process with all external entities and their data interactions.

## External Entities
- **Student**: Submits reports, receives feedback
- **Supervisor**: Reviews work, provides guidance
- **Advisor**: Manages groups and assignments
- **Admin**: Configures and monitors system
- **Panel Member**: Evaluates final thesis
- **External API**: Provides institutional data
- **Email System**: Delivers notifications

## Key Data Flows

### Inbound Data
- Student submissions and attendance
- Supervisor reviews and feedback
- Advisor group configurations
- Admin system settings
- Panel evaluations
- External API data feeds

### Outbound Data
- Notifications and alerts
- Annotated documents
- Assignment results
- System reports
- Performance metrics
- Email notifications