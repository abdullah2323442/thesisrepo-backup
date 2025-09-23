# System Overview Activity Diagram

```mermaid
flowchart TD
    Start([Start]) --> Admin[Admin: Setup System]
    Admin --> AdminTasks[Configure Areas & Users]
    
    AdminTasks --> Advisor[Advisor: Create Groups]
    Advisor --> AssignStudents[Assign Students to Groups]
    AssignStudents --> SetAOI[Set Areas of Interest]
    SetAOI --> RunLottery[Run Supervisor Lottery]
    
    RunLottery --> Supervisor[Supervisor: Receive Groups]
    Supervisor --> ReviewReports[Review Reports]
    ReviewReports --> Meetings[Schedule Meetings]
    Meetings --> Feedback[Provide Feedback]
    
    Feedback --> Student[Student: Submit Reports]
    Student --> Attend[Attend Meetings]
    Attend --> ViewFeedback[View Feedback]
    ViewFeedback --> Revise[Revise & Resubmit]
    
    Revise --> Panel[Panel: Final Evaluation]
    Panel --> End([End])
    
    style Start fill:#4CAF50,color:#fff
    style End fill:#f44336,color:#fff
    style Admin fill:#E3F2FD
    style Advisor fill:#F3E5F5
    style Supervisor fill:#E8F5E9
    style Student fill:#FFF3E0
    style Panel fill:#EFEBE9
```

## Description
This diagram shows the high-level workflow of the thesis management system, displaying how different actors interact throughout the thesis process.

## Key Actors
- **Admin**: System configuration and user management
- **Advisor**: Group creation and supervisor assignment
- **Supervisor**: Report review and feedback
- **Student**: Report submission and revision
- **Panel**: Final evaluation