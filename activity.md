# Activity Diagrams - Thesis Repository Management System

## Table of Contents
1. [User Authentication Flow](#1-user-authentication-flow)
2. [Student Activities](#2-student-activities)
3. [Supervisor Activities](#3-supervisor-activities)
4. [Advisor Activities](#4-advisor-activities)
5. [Administrative Activities](#5-administrative-activities)
6. [System Processes](#6-system-processes)

---

## 1. User Authentication Flow

### 1.1 Login Process
```mermaid
graph TD
    Start([Start]) --> Input[Enter Credentials]
    Input --> CheckType{Identify User Type}
    
    CheckType -->|Numeric ID| StudentAuth[Student Authentication]
    CheckType -->|Alphanumeric ID| FacultyAuth[Faculty Authentication]
    
    StudentAuth --> CallStudentAPI[Call Student API]
    FacultyAuth --> CallFacultyAPI[Call Faculty API]
    
    CallStudentAPI --> ValidateS{Valid?}
    CallFacultyAPI --> ValidateF{Valid?}
    
    ValidateS -->|No| ShowError[Display Error]
    ValidateF -->|No| ShowError
    
    ValidateS -->|Yes| CreateSession[Create Session]
    ValidateF -->|Yes| ParseRoles[Parse TypeId Roles]
    
    ParseRoles --> CreateSession
    CreateSession --> RedirectDashboard[Redirect to Dashboard]
    
    ShowError --> Input
    RedirectDashboard --> End([End])
    
    style Start fill:#e1f5fe
    style End fill:#e1f5fe
    style ShowError fill:#ffebee
    style CreateSession fill:#e8f5e9
```

---

## 2. Student Activities

### 2.1 Report Submission Workflow
```mermaid
graph TD
    Start([Student Starts]) --> ViewReports[View Assigned Reports]
    ViewReports --> CheckStatus{Report Status?}
    
    CheckStatus -->|Draft| SelectReport[Select Report]
    CheckStatus -->|No Reports| Wait[Wait for Assignment]
    
    SelectReport --> UploadOption{Upload Document}
    UploadOption --> SelectFile[Select PDF File]
    
    SelectFile --> ValidateFile{Valid PDF?}
    ValidateFile -->|No| ShowFileError[Show Format Error]
    ValidateFile -->|Yes| UploadFile[Upload to Server]
    
    ShowFileError --> SelectFile
    
    UploadFile --> StoreFile[Store in Database]
    StoreFile --> NotifySupervisor[Create Supervisor Notification]
    NotifySupervisor --> ShowSuccess[Display Success Message]
    
    ShowSuccess --> End([End])
    Wait --> End
    
    style Start fill:#e1f5fe
    style End fill:#e1f5fe
    style ShowFileError fill:#ffebee
    style ShowSuccess fill:#e8f5e9
```

### 2.2 Notification Management
```mermaid
graph TD
    Start([Student Dashboard]) --> CheckNotif[Check Notifications]
    CheckNotif --> QueryDB{New Notifications?}
    
    QueryDB -->|Yes| DisplayBadge[Show Notification Badge]
    QueryDB -->|No| ClearBadge[Clear Badge]
    
    DisplayBadge --> UserClick{User Clicks Bell?}
    UserClick -->|Yes| FetchDetails[Fetch Notification Details]
    UserClick -->|No| KeepBadge[Keep Badge Visible]
    
    FetchDetails --> DisplayList[Display Notification List]
    DisplayList --> SelectNotif{Select Notification?}
    
    SelectNotif -->|Yes| ViewDetails[View Full Details]
    SelectNotif -->|No| ClosePanel[Close Panel]
    
    ViewDetails --> MarkRead[Mark as Read]
    MarkRead --> UpdateDB[Update Database]
    
    UpdateDB --> End([End])
    ClearBadge --> End
    KeepBadge --> End
    ClosePanel --> End
    
    style Start fill:#e1f5fe
    style End fill:#e1f5fe
    style DisplayBadge fill:#fff3cd
```

---

## 3. Supervisor Activities

### 3.1 Report Creation and Management
```mermaid
graph TD
    Start([Supervisor Starts]) --> SelectGroup[Select Student Group]
    SelectGroup --> CreateReport[Create New Report]
    
    CreateReport --> EnterDetails[Enter Report Details]
    EnterDetails --> SetDeadline[Set Submission Deadline]
    SetDeadline --> SaveReport[Save to Database]
    
    SaveReport --> NotifyStudents[Notify All Group Students]
    NotifyStudents --> ReportCreated[Report Created Successfully]
    
    ReportCreated --> ManageOption{Management Option?}
    
    ManageOption -->|View Submissions| ViewSubmissions[View Student Submissions]
    ManageOption -->|Add Annotation| AnnotateDoc[Annotate Document]
    ManageOption -->|Add Comment| AddComment[Add Comment]
    ManageOption -->|Approve| ApproveReport[Approve Report]
    
    ViewSubmissions --> CheckSubmission{Submission Available?}
    CheckSubmission -->|Yes| ReviewDoc[Review Document]
    CheckSubmission -->|No| WaitSubmission[Wait for Submission]
    
    AnnotateDoc --> SaveAnnotation[Save Annotations]
    AddComment --> SaveComment[Save Comment]
    ApproveReport --> UpdateStatus[Update Report Status]
    
    SaveAnnotation --> NotifyStudents2[Notify Students]
    SaveComment --> NotifyStudents2
    UpdateStatus --> NotifyStudents2
    
    NotifyStudents2 --> End([End])
    WaitSubmission --> End
    
    style Start fill:#e1f5fe
    style End fill:#e1f5fe
    style ReportCreated fill:#e8f5e9
```

### 3.2 Meeting Documentation Process
```mermaid
graph TD
    Start([Start Meeting Record]) --> SelectGroup[Select Group]
    SelectGroup --> EnterDate[Enter Meeting Date]
    
    EnterDate --> EnterTopics[Enter Discussed Topics]
    EnterTopics --> EnterOutcomes[Enter Meeting Outcomes]
    EnterOutcomes --> MarkAttendance[Mark Student Attendance]
    
    MarkAttendance --> LoopStudents{For Each Student}
    LoopStudents -->|Mark| SetPresent{Present?}
    SetPresent -->|Yes| MarkPresent[Mark Present]
    SetPresent -->|No| MarkAbsent[Mark Absent]
    
    MarkPresent --> NextStudent{More Students?}
    MarkAbsent --> NextStudent
    
    NextStudent -->|Yes| LoopStudents
    NextStudent -->|No| SaveMeeting[Save Meeting Record]
    
    SaveMeeting --> GeneratePDF{Generate Report?}
    GeneratePDF -->|Yes| CreatePDF[Create PDF Report]
    GeneratePDF -->|No| Complete[Meeting Recorded]
    
    CreatePDF --> DownloadPDF[Download PDF]
    DownloadPDF --> Complete
    
    Complete --> End([End])
    
    style Start fill:#e1f5fe
    style End fill:#e1f5fe
    style Complete fill:#e8f5e9
```

---

## 4. Advisor Activities

### 4.1 Group Formation Process
```mermaid
graph TD
    Start([Advisor Starts]) --> SelectBatch[Select Student Batch]
    SelectBatch --> FetchStudents[Fetch Students from API]
    
    FetchStudents --> DisplayList[Display Student List]
    DisplayList --> CreateGroups{Create Groups Method?}
    
    CreateGroups -->|Manual| ManualGroup[Manual Selection]
    CreateGroups -->|Excel Import| ExcelImport[Upload Excel File]
    
    ManualGroup --> SelectStudents[Select Students]
    SelectStudents --> AssignToGroup[Assign to Group]
    AssignToGroup --> SetGroupName[Set Group Name]
    SetGroupName --> SetAOI[Set Area of Interest]
    
    ExcelImport --> ValidateExcel{Valid Format?}
    ValidateExcel -->|No| ShowExcelError[Show Format Error]
    ValidateExcel -->|Yes| ParseData[Parse Excel Data]
    
    ShowExcelError --> ExcelImport
    ParseData --> SetAOI
    
    SetAOI --> SaveGroup[Save Group to Database]
    SaveGroup --> MoreGroups{Create More Groups?}
    
    MoreGroups -->|Yes| DisplayList
    MoreGroups -->|No| GroupsCreated[Groups Created Successfully]
    
    GroupsCreated --> End([End])
    
    style Start fill:#e1f5fe
    style End fill:#e1f5fe
    style ShowExcelError fill:#ffebee
    style GroupsCreated fill:#e8f5e9
```

### 4.2 Supervisor Assignment Lottery
```mermaid
graph TD
    Start([Start Assignment]) --> SelectMode{Select Assignment Mode}
    
    SelectMode -->|AOI-Based| AOIMode[Area of Interest Mode]
    SelectMode -->|Ranking-Based| RankMode[Ranking Mode]
    SelectMode -->|Hybrid| HybridMode[Combined Mode]
    
    AOIMode --> GetGroups1[Get Unassigned Groups]
    RankMode --> GetGroups2[Get Unassigned Groups]
    HybridMode --> GetGroups3[Get Unassigned Groups]
    
    GetGroups1 --> GetSupervisors1[Get Supervisors with AOI]
    GetGroups2 --> GetSupervisors2[Get All Supervisors]
    GetGroups3 --> GetSupervisors3[Get Supervisors with AOI]
    
    GetSupervisors1 --> AOIAlgorithm[Execute AOI Matching]
    GetSupervisors2 --> RankAlgorithm[Execute Round-Robin]
    GetSupervisors3 --> HybridAlgorithm[Execute Balanced Assignment]
    
    AOIAlgorithm --> ProcessGroups1{For Each Group}
    RankAlgorithm --> ProcessGroups2{For Each Group}
    HybridAlgorithm --> ProcessGroups3{For Each Group}
    
    ProcessGroups1 -->|Match AOI| RandomSelect[Random Selection from Matches]
    ProcessGroups2 -->|Next in Rotation| AssignByRank[Assign by Rank Order]
    ProcessGroups3 -->|Balance Load| OptimalSelect[Select Optimal Supervisor]
    
    RandomSelect --> UpdateDB1[Update Database]
    AssignByRank --> UpdateDB2[Update Database]
    OptimalSelect --> UpdateDB3[Update Database]
    
    UpdateDB1 --> CheckMore1{More Groups?}
    UpdateDB2 --> CheckMore2{More Groups?}
    UpdateDB3 --> CheckMore3{More Groups?}
    
    CheckMore1 -->|Yes| ProcessGroups1
    CheckMore2 -->|Yes| ProcessGroups2
    CheckMore3 -->|Yes| ProcessGroups3
    
    CheckMore1 -->|No| ShowResults[Display Assignment Results]
    CheckMore2 -->|No| ShowResults
    CheckMore3 -->|No| ShowResults
    
    ShowResults --> End([End])
    
    style Start fill:#e1f5fe
    style End fill:#e1f5fe
    style ShowResults fill:#e8f5e9
```

---

## 5. Administrative Activities

### 5.1 Data Synchronization Process
```mermaid
graph TD
    Start([Admin Initiates Sync]) --> SelectType{Sync Type?}
    
    SelectType -->|Supervisors| SyncSupervisors[Sync Supervisors]
    SelectType -->|Students| SyncStudents[Sync Students]
    SelectType -->|Batches| SyncBatches[Sync Batches]
    
    SyncSupervisors --> CallSupAPI[Call Supervisor API]
    SyncStudents --> CallStuAPI[Call Student API]
    SyncBatches --> CallBatchAPI[Call Batch API]
    
    CallSupAPI --> ProcessSupData[Process Supervisor Data]
    CallStuAPI --> ProcessStuData[Process Student Data]
    CallBatchAPI --> ProcessBatchData[Process Batch Data]
    
    ProcessSupData --> CompareSupDB{Compare with Database}
    ProcessStuData --> CompareStuDB{Compare with Database}
    ProcessBatchData --> CompareBatchDB{Compare with Database}
    
    CompareSupDB -->|New| AddSupervisor[Add New Supervisor]
    CompareSupDB -->|Existing| UpdateSupervisor[Update Supervisor]
    CompareSupDB -->|Deleted| DeactivateSupervisor[Deactivate Supervisor]
    
    CompareStuDB -->|New| AddStudent[Add New Student]
    CompareStuDB -->|Existing| UpdateStudent[Update Student]
    
    CompareBatchDB -->|New| AddBatch[Add New Batch]
    CompareBatchDB -->|Existing| UpdateBatch[Update Batch]
    
    AddSupervisor --> SyncComplete[Synchronization Complete]
    UpdateSupervisor --> SyncComplete
    DeactivateSupervisor --> SyncComplete
    AddStudent --> SyncComplete
    UpdateStudent --> SyncComplete
    AddBatch --> SyncComplete
    UpdateBatch --> SyncComplete
    
    SyncComplete --> GenerateReport[Generate Sync Report]
    GenerateReport --> End([End])
    
    style Start fill:#e1f5fe
    style End fill:#e1f5fe
    style SyncComplete fill:#e8f5e9
```

### 5.2 System Monitoring
```mermaid
graph TD
    Start([Monitor System]) --> CollectMetrics[Collect System Metrics]
    
    CollectMetrics --> QueryPerformance[Query Performance Data]
    QueryPerformance --> AnalyzeData{Analyze Metrics}
    
    AnalyzeData --> CheckUsers[Check Active Users]
    AnalyzeData --> CheckReports[Check Report Statistics]
    AnalyzeData --> CheckAssignments[Check Assignment Status]
    AnalyzeData --> CheckStorage[Check Storage Usage]
    
    CheckUsers --> CalcUserMetrics[Calculate User Metrics]
    CheckReports --> CalcReportMetrics[Calculate Report Metrics]
    CheckAssignments --> CalcAssignMetrics[Calculate Assignment Metrics]
    CheckStorage --> CalcStorageMetrics[Calculate Storage Metrics]
    
    CalcUserMetrics --> CompileResults[Compile All Results]
    CalcReportMetrics --> CompileResults
    CalcAssignMetrics --> CompileResults
    CalcStorageMetrics --> CompileResults
    
    CompileResults --> GenerateDashboard[Generate Dashboard View]
    GenerateDashboard --> DisplayMetrics[Display to Administrator]
    
    DisplayMetrics --> RefreshOption{Auto Refresh?}
    RefreshOption -->|Yes| WaitInterval[Wait 30 seconds]
    RefreshOption -->|No| End([End])
    
    WaitInterval --> CollectMetrics
    
    style Start fill:#e1f5fe
    style End fill:#e1f5fe
    style GenerateDashboard fill:#e8f5e9
```

---

## 6. System Processes

### 6.1 Report Lifecycle
```mermaid
graph TD
    Start([Report Created]) --> StatusDraft[Status: Draft]
    
    StatusDraft --> StudentSubmits{Student Submits?}
    StudentSubmits -->|No| WaitSubmission[Wait for Submission]
    StudentSubmits -->|Yes| StatusSubmitted[Status: Submitted]
    
    WaitSubmission --> CheckDeadline{Deadline Passed?}
    CheckDeadline -->|Yes| StatusOverdue[Status: Overdue]
    CheckDeadline -->|No| StudentSubmits
    
    StatusSubmitted --> SupervisorReview[Supervisor Reviews]
    SupervisorReview --> AddFeedback{Add Feedback?}
    
    AddFeedback -->|Annotation| AddAnnotation[Add Annotations]
    AddFeedback -->|Comment| AddComment[Add Comments]
    AddFeedback -->|Approve| ApproveReport[Approve Report]
    AddFeedback -->|Reject| RejectReport[Request Revision]
    
    AddAnnotation --> NotifyStudent1[Notify Student]
    AddComment --> NotifyStudent2[Notify Student]
    RejectReport --> StatusRevision[Status: Needs Revision]
    
    StatusRevision --> StudentSubmits
    
    ApproveReport --> PanelReview{Panel Review Required?}
    PanelReview -->|Yes| StatusPanelReview[Status: Under Panel Review]
    PanelReview -->|No| StatusApproved[Status: Approved]
    
    StatusPanelReview --> PanelFeedback[Panel Provides Feedback]
    PanelFeedback --> FinalDecision{Final Decision}
    
    FinalDecision -->|Approved| StatusApproved
    FinalDecision -->|Revision| StatusRevision
    
    StatusApproved --> StatusComplete[Status: Complete]
    StatusOverdue --> StatusComplete
    
    StatusComplete --> ArchiveReport[Archive Report]
    ArchiveReport --> End([End])
    
    NotifyStudent1 --> SupervisorReview
    NotifyStudent2 --> SupervisorReview
    
    style Start fill:#e1f5fe
    style End fill:#e1f5fe
    style StatusComplete fill:#e8f5e9
    style StatusOverdue fill:#ffebee
```

### 6.2 Notification Flow
```mermaid
graph TD
    Start([Event Triggered]) --> IdentifyType{Event Type?}
    
    IdentifyType -->|Report Created| ReportNotif[Report Notification]
    IdentifyType -->|Annotation Added| AnnotationNotif[Annotation Notification]
    IdentifyType -->|Comment Added| CommentNotif[Comment Notification]
    IdentifyType -->|Meeting Scheduled| MeetingNotif[Meeting Notification]
    
    ReportNotif --> CreateNotification[Create Notification Object]
    AnnotationNotif --> CreateNotification
    CommentNotif --> CreateNotification
    MeetingNotif --> CreateNotification
    
    CreateNotification --> IdentifyRecipients[Identify Recipients]
    IdentifyRecipients --> StoreInDB[Store in Database]
    
    StoreInDB --> SetUnread[Set Status: Unread]
    SetUnread --> UpdateCounter[Update User's Notification Counter]
    
    UpdateCounter --> UserOnline{User Online?}
    UserOnline -->|Yes| ShowBadge[Display Badge Immediately]
    UserOnline -->|No| WaitForLogin[Wait for User Login]
    
    ShowBadge --> UserViews{User Views?}
    WaitForLogin --> UserLogin[User Logs In]
    UserLogin --> ShowBadge
    
    UserViews -->|Yes| MarkAsRead[Mark as Read]
    UserViews -->|No| KeepUnread[Keep as Unread]
    
    MarkAsRead --> UpdateDB[Update Database]
    UpdateDB --> End([End])
    KeepUnread --> End
    
    style Start fill:#e1f5fe
    style End fill:#e1f5fe
    style ShowBadge fill:#fff3cd
```

### 6.3 File Upload Process
```mermaid
graph TD
    Start([User Initiates Upload]) --> SelectFile[Select File]
    
    SelectFile --> CheckSize{File Size OK?}
    CheckSize -->|No| ShowSizeError[Show Size Error]
    CheckSize -->|Yes| CheckFormat{Format Valid?}
    
    ShowSizeError --> SelectFile
    
    CheckFormat -->|No| ShowFormatError[Show Format Error]
    CheckFormat -->|Yes| GenerateHash[Generate File Hash]
    
    ShowFormatError --> SelectFile
    
    GenerateHash --> CheckDuplicate{Duplicate File?}
    CheckDuplicate -->|Yes| ShowDuplicateWarning[Show Duplicate Warning]
    CheckDuplicate -->|No| UploadToServer[Upload to Server]
    
    ShowDuplicateWarning --> UserDecision{Continue?}
    UserDecision -->|No| SelectFile
    UserDecision -->|Yes| UploadToServer
    
    UploadToServer --> StoreFile[Store in File System]
    StoreFile --> GeneratePath[Generate Storage Path]
    GeneratePath --> SaveMetadata[Save File Metadata]
    
    SaveMetadata --> CreateRecord[Create Database Record]
    CreateRecord --> LinkToEntity[Link to Related Entity]
    
    LinkToEntity --> UploadComplete[Upload Successful]
    UploadComplete --> End([End])
    
    style Start fill:#e1f5fe
    style End fill:#e1f5fe
    style UploadComplete fill:#e8f5e9
    style ShowSizeError fill:#ffebee
    style ShowFormatError fill:#ffebee
```

---

## Activity Diagram Notation Guide

### Symbols Used
- **Rounded Rectangle**: Start/End points
- **Rectangle**: Activities/Actions
- **Diamond**: Decision points
- **Arrows**: Flow direction
- **Parallel Bars**: Concurrent activities (fork/join)

### Color Coding
- 🔵 Light Blue: Start/End states
- 🟢 Light Green: Success states
- 🔴 Light Red: Error states
- 🟡 Light Yellow: Warning/Notification states

---

## System Activity Summary

### Core Workflows
1. **Authentication**: Multi-path authentication based on user type
2. **Report Management**: Complete lifecycle from creation to archival
3. **Group Management**: Formation and supervisor assignment
4. **Notification System**: Event-driven notification delivery
5. **File Management**: Secure upload and storage process
6. **Data Synchronization**: External API integration workflow

### Key Decision Points
- User type identification during login
- Assignment strategy selection for supervisor lottery
- Report approval/rejection decisions
- File validation checks
- Notification delivery timing

### Parallel Processes
- Multiple students can submit reports simultaneously
- Supervisors can manage multiple groups concurrently
- System can process multiple API synchronizations
- Notifications are processed asynchronously

---

## Notes
These activity diagrams follow IEEE standards for software documentation and UML 2.5 specifications. They represent the logical flow of activities within the Thesis Repository Management System, focusing on business logic rather than technical implementation details. The diagrams are suitable for inclusion in academic project reports and technical documentation.