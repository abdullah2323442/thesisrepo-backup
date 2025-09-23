# Data Flow Diagram - Level 1

```mermaid
flowchart TB
    %% External Entities
    Student[Student]
    Supervisor[Supervisor]
    Advisor[Advisor]
    Admin[Admin]
    Panel[Panel Member]
    API[External API]
    
    %% Processes
    P1((1.0<br/>User<br/>Authentication))
    P2((2.0<br/>Group<br/>Management))
    P3((3.0<br/>Supervisor<br/>Assignment))
    P4((4.0<br/>Report<br/>Submission))
    P5((5.0<br/>Report<br/>Review))
    P6((6.0<br/>Meeting<br/>Management))
    P7((7.0<br/>Notification<br/>System))
    P8((8.0<br/>Evaluation<br/>Process))
    P9((9.0<br/>System<br/>Administration))
    
    %% Data Stores
    D1[(D1: Users)]
    D2[(D2: Groups)]
    D3[(D3: Supervisors)]
    D4[(D4: Reports)]
    D5[(D5: Meetings)]
    D6[(D6: Notifications)]
    D7[(D7: Areas of Interest)]
    D8[(D8: Evaluations)]
    D9[(D9: System Config)]
    
    %% Student flows
    Student -->|Login credentials| P1
    P1 -->|Auth token| Student
    Student -->|Report files| P4
    P4 -->|Submission confirmation| Student
    Student -->|Meeting attendance| P6
    
    %% Supervisor flows
    Supervisor -->|Login credentials| P1
    P1 -->|Auth token| Supervisor
    Supervisor -->|Review feedback| P5
    P5 -->|Student submissions| Supervisor
    Supervisor -->|Meeting schedule| P6
    
    %% Advisor flows
    Advisor -->|Login credentials| P1
    P1 -->|Auth token| Advisor
    Advisor -->|Group data| P2
    P2 -->|Group status| Advisor
    Advisor -->|Assignment request| P3
    P3 -->|Assignment results| Advisor
    
    %% Admin flows
    Admin -->|Login credentials| P1
    P1 -->|Auth token| Admin
    Admin -->|System settings| P9
    P9 -->|System reports| Admin
    
    %% Panel flows
    Panel -->|Login credentials| P1
    P1 -->|Auth token| Panel
    Panel -->|Evaluation data| P8
    P8 -->|Reports for review| Panel
    
    %% External API flows
    API -->|External data| P9
    P9 -->|Data requests| API
    
    %% Process to Data Store flows
    P1 <-->|User data| D1
    P2 <-->|Group data| D2
    P3 <-->|Supervisor data| D3
    P3 <-->|AOI data| D7
    P4 <-->|Report data| D4
    P5 <-->|Review data| D4
    P6 <-->|Meeting data| D5
    P7 <-->|Notification data| D6
    P8 <-->|Evaluation data| D8
    P9 <-->|Config data| D9
    
    %% Inter-process flows
    P2 -->|Groups for assignment| P3
    P3 -->|Assignment notifications| P7
    P4 -->|Reports for review| P5
    P4 -->|Submission notifications| P7
    P5 -->|Review notifications| P7
    P6 -->|Meeting notifications| P7
    P8 -->|Grade notifications| P7
    
    %% Styling
    style Student fill:#FFE0B2
    style Supervisor fill:#C8E6C9
    style Advisor fill:#C5CAE9
    style Admin fill:#FFCDD2
    style Panel fill:#E1BEE7
    style API fill:#FFF8E1
    style D1 fill:#FFF3E0
    style D2 fill:#FFF3E0
    style D3 fill:#FFF3E0
    style D4 fill:#FFF3E0
    style D5 fill:#FFF3E0
    style D6 fill:#FFF3E0
    style D7 fill:#FFF3E0
    style D8 fill:#FFF3E0
    style D9 fill:#FFF3E0
```

## Description
Level 1 DFD breaks down the thesis management system into major processes, showing data flows between processes, external entities, and data stores.

## Major Processes
1. **User Authentication (1.0)**: Handles login and access control
2. **Group Management (2.0)**: Creates and manages thesis groups
3. **Supervisor Assignment (3.0)**: Assigns supervisors to groups
4. **Report Submission (4.0)**: Handles student report uploads
5. **Report Review (5.0)**: Manages review and annotation process
6. **Meeting Management (6.0)**: Schedules and tracks meetings
7. **Notification System (7.0)**: Sends alerts and updates
8. **Evaluation Process (8.0)**: Final thesis evaluation
9. **System Administration (9.0)**: System configuration and monitoring

## Data Stores
- **D1 Users**: User accounts and authentication
- **D2 Groups**: Thesis group information
- **D3 Supervisors**: Supervisor details and availability
- **D4 Reports**: Submitted reports and reviews
- **D5 Meetings**: Meeting schedules and attendance
- **D6 Notifications**: System notifications
- **D7 Areas of Interest**: Research areas
- **D8 Evaluations**: Final evaluations and grades
- **D9 System Config**: System settings

## Key Data Flows
- Authentication flows through Process 1.0
- Group creation and assignment through Processes 2.0 and 3.0
- Report submission and review cycle through Processes 4.0 and 5.0
- All notifications centralized through Process 7.0