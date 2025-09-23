# Complete Thesis Management Flow

```mermaid
flowchart TB
    Start([System Start]) --> AdminSetup[Admin: Configure System]
    AdminSetup --> CreateAreas[Create Areas of Interest]
    CreateAreas --> AddSupervisors[Add Supervisors]
    
    AddSupervisors --> AdvisorWork[Advisor: Create Groups]
    AdvisorWork --> AssignStudents[Assign Students to Groups]
    AssignStudents --> SetResearchAreas[Set Research Areas]
    
    SetResearchAreas --> RunLottery[Run Supervisor Lottery]
    RunLottery --> LotteryMode{Lottery Mode}
    
    LotteryMode -->|AOI| AOIMatch[Match by Area]
    LotteryMode -->|Rank| RankMatch[Match by Rank]
    LotteryMode -->|Combined| CombinedMatch[AOI + Rank]
    
    AOIMatch --> AssignSupervisors[Assign Supervisors]
    RankMatch --> AssignSupervisors
    CombinedMatch --> AssignSupervisors
    
    AssignSupervisors --> SupervisorPhase[Supervisor: Begin Supervision]
    SupervisorPhase --> ScheduleMeetings[Schedule Meetings]
    ScheduleMeetings --> CreateReportTasks[Create Report Tasks]
    
    CreateReportTasks --> StudentPhase[Student: Work Phase]
    StudentPhase --> SubmitReport[Submit Report]
    
    SubmitReport --> ReviewCycle{Review Status}
    
    ReviewCycle -->|Needs Revision| ReceiveFeedback[Receive Feedback]
    ReceiveFeedback --> ViewAnnotations[View Annotations]
    ViewAnnotations --> ReviseWork[Revise Work]
    ReviseWork --> SubmitReport
    
    ReviewCycle -->|Approved| PanelReview[Panel: Final Review]
    PanelReview --> FinalEvaluation[Final Evaluation]
    FinalEvaluation --> AssignGrade[Assign Final Grade]
    
    AssignGrade --> Complete[Thesis Complete]
    Complete --> End([End])
    
    style Start fill:#4CAF50,color:#fff
    style End fill:#f44336,color:#fff
    style LotteryMode fill:#FFE082
    style ReviewCycle fill:#FFE082
    style AdminSetup fill:#FFCDD2
    style AdvisorWork fill:#C5CAE9
    style SupervisorPhase fill:#C8E6C9
    style StudentPhase fill:#FFE0B2
    style PanelReview fill:#E1BEE7
```

## Description
End-to-end thesis management process from system setup to final evaluation.

## Major Phases
1. **Setup Phase**: Admin configures system
2. **Formation Phase**: Advisor creates groups
3. **Assignment Phase**: Lottery-based supervisor assignment
4. **Supervision Phase**: Active thesis supervision
5. **Submission Phase**: Report submission and review
6. **Evaluation Phase**: Final panel evaluation

## Key Features
- Multiple lottery modes for fair assignment
- Iterative review and revision cycle
- Multi-stage evaluation process