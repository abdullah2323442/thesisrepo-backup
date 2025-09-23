# Data Flow Diagram - Level 2 (Report Management Subsystem)

```mermaid
flowchart TB
    %% External Entities
    Student[Student]
    Supervisor[Supervisor]
    CoSup[Co-Supervisor]
    Panel[Panel Member]
    NotifSys[Notification System]
    
    %% Report Submission Processes (4.x)
    P41((4.1<br/>Validate<br/>Submission))
    P42((4.2<br/>Store<br/>Report))
    P43((4.3<br/>Generate<br/>Submission ID))
    P44((4.4<br/>Check<br/>Deadline))
    
    %% Report Review Processes (5.x)
    P51((5.1<br/>Retrieve<br/>Report))
    P52((5.2<br/>Create<br/>Annotation<br/>Session))
    P53((5.3<br/>Add<br/>Annotations))
    P54((5.4<br/>Generate<br/>Annotated PDF))
    P55((5.5<br/>Set Report<br/>Status))
    P56((5.6<br/>Write<br/>Feedback))
    
    %% Data Stores
    D41[(D4.1<br/>Report<br/>Metadata)]
    D42[(D4.2<br/>Report<br/>Files)]
    D43[(D4.3<br/>Annotation<br/>Sessions)]
    D44[(D4.4<br/>Feedback<br/>History)]
    D45[(D4.5<br/>Report<br/>Status)]
    
    %% Student submission flow
    Student -->|Submission request| P44
    P44 -->|Valid deadline| P41
    P41 -->|Validation result| Student
    P41 -->|Valid submission| P43
    P43 -->|Submission ID| P42
    P42 -->|Store file| D42
    P42 -->|Store metadata| D41
    P42 -->|Submission notification| NotifSys
    
    %% Supervisor review flow
    Supervisor -->|Request report| P51
    P51 <-->|Report file| D42
    P51 -->|Report content| Supervisor
    Supervisor -->|Start annotation| P52
    P52 -->|Create session| D43
    P52 -->|Session ID| P53
    Supervisor -->|Annotations| P53
    P53 -->|Store annotations| D43
    P53 -->|Complete annotations| P54
    P54 -->|Store annotated PDF| D42
    P54 -->|Annotated PDF| Supervisor
    
    %% Feedback flow
    Supervisor -->|Feedback text| P56
    P56 -->|Store feedback| D44
    P56 -->|Update status| P55
    P55 -->|New status| D45
    P55 -->|Status notification| NotifSys
    
    %% Co-Supervisor flow
    CoSup -->|Request report| P51
    P51 -->|Report content| CoSup
    CoSup -->|Start annotation| P52
    CoSup -->|Annotations| P53
    
    %% Panel review flow
    Panel -->|Request final report| P51
    P51 -->|Report content| Panel
    Panel -->|Evaluation feedback| P56
    
    %% Student retrieval flow
    Student -->|View submission| P51
    P51 -->|Report & feedback| Student
    D43 -->|Annotated PDF| Student
    D44 -->|Feedback history| Student
    D45 -->|Current status| Student
    
    %% Inter-store relationships
    D41 <-->|File reference| D42
    D41 <-->|Status link| D45
    D43 -->|Annotation summary| D44
    
    %% Styling
    style Student fill:#FFE0B2
    style Supervisor fill:#C8E6C9
    style CoSup fill:#B2DFDB
    style Panel fill:#E1BEE7
    style NotifSys fill:#E0F2F1
    style D41 fill:#FFF3E0
    style D42 fill:#FFF3E0
    style D43 fill:#FFF3E0
    style D44 fill:#FFF3E0
    style D45 fill:#FFF3E0
```

## Description
Level 2 DFD details the Report Management subsystem, breaking down processes 4.0 (Report Submission) and 5.0 (Report Review) into sub-processes.

## Report Submission Sub-processes (4.x)
- **4.1 Validate Submission**: Checks file format, size, and requirements
- **4.2 Store Report**: Saves report file to storage
- **4.3 Generate Submission ID**: Creates unique identifier
- **4.4 Check Deadline**: Verifies submission is within deadline

## Report Review Sub-processes (5.x)
- **5.1 Retrieve Report**: Fetches report for viewing
- **5.2 Create Annotation Session**: Initiates annotation process
- **5.3 Add Annotations**: Records comments and markups
- **5.4 Generate Annotated PDF**: Creates annotated version
- **5.5 Set Report Status**: Updates report status
- **5.6 Write Feedback**: Records reviewer feedback

## Data Stores
- **D4.1 Report Metadata**: Submission details and properties
- **D4.2 Report Files**: Actual document storage
- **D4.3 Annotation Sessions**: Annotation data and history
- **D4.4 Feedback History**: All feedback records
- **D4.5 Report Status**: Current status tracking

## Key Data Flows

### Submission Flow
1. Student submits report
2. System validates deadline and format
3. Generates unique ID
4. Stores file and metadata
5. Sends notification

### Review Flow
1. Reviewer retrieves report
2. Creates annotation session
3. Adds annotations and feedback
4. Generates annotated PDF
5. Updates status and notifies student

### Multi-reviewer Support
- Supervisors, Co-supervisors, and Panel members can all review
- Each creates separate annotation sessions
- All feedback stored in history