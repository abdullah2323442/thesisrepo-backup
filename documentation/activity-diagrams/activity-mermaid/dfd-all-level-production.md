# Production-Grade Data Flow Diagrams - University Thesis Management System

## Level 0 - Context Diagram

```mermaid
graph TB
    %% External Entities
    Student[("👨‍🎓 Student<br/>External Entity")]
    Teacher[("👨‍🏫 Teacher<br/>External Entity")]
    Admin[("👨‍💼 Admin<br/>External Entity")]
    Advisor[("👩‍🏫 Advisor<br/>External Entity")]
    UniversityAPI[("🏛️ University API<br/>External System")]
    EmailSystem[("📧 Email System<br/>External System")]
    
    %% Main System
    ThesisSystem[["📚 Thesis Management System<br/>Main Process"]]
    
    %% Data Flows
    Student -->|"Login/Register<br/>Submit Reports<br/>View Assignments"| ThesisSystem
    Teacher -->|"Manage Groups<br/>Review Reports<br/>Schedule Meetings"| ThesisSystem
    Admin -->|"System Configuration<br/>User Management<br/>Performance Monitoring"| ThesisSystem
    Advisor -->|"Create Groups<br/>Assign Supervisors<br/>Manage Students"| ThesisSystem
    
    ThesisSystem -->|"Dashboard Data<br/>Notifications<br/>Report Status"| Student
    ThesisSystem -->|"Group Info<br/>Meeting Schedule<br/>Report Feedback"| Teacher
    ThesisSystem -->|"System Reports<br/>Performance Metrics"| Admin
    ThesisSystem -->|"Assignment Status<br/>Group Statistics"| Advisor
    
    ThesisSystem <-->|"Authentication<br/>Student Data<br/>Batch Info"| UniversityAPI
    ThesisSystem -->|"Notifications<br/>Report Updates"| EmailSystem
    
    style ThesisSystem fill:#e1f5fe,stroke:#01579b,stroke-width:3px
    style Student fill:#fff3e0,stroke:#e65100
    style Teacher fill:#fff3e0,stroke:#e65100
    style Admin fill:#fff3e0,stroke:#e65100
    style Advisor fill:#fff3e0,stroke:#e65100
    style UniversityAPI fill:#f3e5f5,stroke:#4a148c
    style EmailSystem fill:#f3e5f5,stroke:#4a148c
```

## Level 1 - Main System Processes

```mermaid
graph TB
    %% External Entities
    Student[("👨‍🎓 Student")]
    Teacher[("👨‍🏫 Teacher")]
    Admin[("👨‍💼 Admin")]
    Advisor[("👩‍🏫 Advisor")]
    UniversityAPI[("🏛️ University API")]
    EmailSystem[("📧 Email System")]
    
    %% Main Processes
    Auth["1.0<br/>🔐 Authentication<br/>& Authorization"]
    GroupMgmt["2.0<br/>👥 Group<br/>Management"]
    SupervisorAssign["3.0<br/>🎯 Supervisor<br/>Assignment"]
    ReportMgmt["4.0<br/>📄 Report<br/>Management"]
    MeetingMgmt["5.0<br/>📅 Meeting<br/>Management"]
    NotificationSys["6.0<br/>🔔 Notification<br/>System"]
    PerformanceMon["7.0<br/>📊 Performance<br/>Monitoring"]
    
    %% Data Stores
    UserDB[("D1: Users<br/>Database")]
    GroupDB[("D2: Groups<br/>Database")]
    SupervisorDB[("D3: Supervisors<br/>Database")]
    ReportDB[("D4: Reports<br/>Database")]
    MeetingDB[("D5: Meetings<br/>Database")]
    NotificationDB[("D6: Notifications<br/>Database")]
    
    %% Data Flows - Authentication
    Student -->|"Login Credentials"| Auth
    Teacher -->|"Login Credentials"| Auth
    Admin -->|"Login Credentials"| Auth
    Advisor -->|"Login Credentials"| Auth
    Auth <-->|"Verify Credentials"| UniversityAPI
    Auth <-->|"User Data"| UserDB
    Auth -->|"Session Token"| Student
    Auth -->|"Session Token"| Teacher
    
    %% Data Flows - Group Management
    Advisor -->|"Create Groups<br/>Assign Students"| GroupMgmt
    Admin -->|"Manage Groups"| GroupMgmt
    GroupMgmt <-->|"Group Data"| GroupDB
    GroupMgmt -->|"Group Info"| Student
    GroupMgmt <-->|"Student Data"| UniversityAPI
    
    %% Data Flows - Supervisor Assignment
    Advisor -->|"Assignment Request"| SupervisorAssign
    SupervisorAssign <-->|"Supervisor Data"| SupervisorDB
    SupervisorAssign <-->|"Group Data"| GroupDB
    SupervisorAssign -->|"Assignment Result"| Advisor
    SupervisorAssign -->|"Assignment Notification"| NotificationSys
    
    %% Data Flows - Report Management
    Student -->|"Submit Report"| ReportMgmt
    Teacher -->|"Review/Annotate"| ReportMgmt
    ReportMgmt <-->|"Report Data"| ReportDB
    ReportMgmt -->|"Report Status"| Student
    ReportMgmt -->|"Report Updates"| NotificationSys
    
    %% Data Flows - Meeting Management
    Teacher -->|"Schedule Meeting"| MeetingMgmt
    MeetingMgmt <-->|"Meeting Data"| MeetingDB
    MeetingMgmt -->|"Meeting Schedule"| Student
    MeetingMgmt -->|"Meeting Reminder"| NotificationSys
    
    %% Data Flows - Notifications
    NotificationSys <-->|"Notification Data"| NotificationDB
    NotificationSys -->|"Email Notifications"| EmailSystem
    NotificationSys -->|"In-App Notifications"| Student
    NotificationSys -->|"In-App Notifications"| Teacher
    
    %% Data Flows - Performance Monitoring
    Admin -->|"View Metrics"| PerformanceMon
    PerformanceMon -->|"Performance Data"| Admin
    PerformanceMon <-->|"System Metrics"| UserDB
    PerformanceMon <-->|"API Status"| UniversityAPI
    
    style Auth fill:#e8f5e9,stroke:#2e7d32
    style GroupMgmt fill:#e8f5e9,stroke:#2e7d32
    style SupervisorAssign fill:#e8f5e9,stroke:#2e7d32
    style ReportMgmt fill:#e8f5e9,stroke:#2e7d32
    style MeetingMgmt fill:#e8f5e9,stroke:#2e7d32
    style NotificationSys fill:#e8f5e9,stroke:#2e7d32
    style PerformanceMon fill:#e8f5e9,stroke:#2e7d32
    style UserDB fill:#fff9c4,stroke:#f57f17
    style GroupDB fill:#fff9c4,stroke:#f57f17
    style SupervisorDB fill:#fff9c4,stroke:#f57f17
    style ReportDB fill:#fff9c4,stroke:#f57f17
    style MeetingDB fill:#fff9c4,stroke:#f57f17
    style NotificationDB fill:#fff9c4,stroke:#f57f17
```

## Level 2 - Authentication & Authorization Process

```mermaid
graph TB
    %% External Entities
    User[("👤 User<br/>(Student/Teacher/<br/>Admin/Advisor)")]
    UniversityAPI[("🏛️ University API")]
    
    %% Sub-processes
    ValidateInput["1.1<br/>Validate<br/>Input"]
    CheckAPI["1.2<br/>External API<br/>Authentication"]
    LocalAuth["1.3<br/>Local<br/>Authentication"]
    CreateSession["1.4<br/>Create<br/>Session"]
    RoleCheck["1.5<br/>Role<br/>Authorization"]
    RateLimit["1.6<br/>Rate<br/>Limiting"]
    
    %% Data Stores
    UserDB[("D1: Users")]
    SessionDB[("D7: Sessions")]
    RateLimitDB[("D8: Rate Limits")]
    
    %% Data Flows
    User -->|"Login Request"| ValidateInput
    ValidateInput -->|"Valid Credentials"| CheckAPI
    ValidateInput -->|"Invalid Input"| User
    
    CheckAPI <-->|"API Verification"| UniversityAPI
    CheckAPI -->|"API Success"| CreateSession
    CheckAPI -->|"API Failure"| LocalAuth
    
    LocalAuth <-->|"Check Credentials"| UserDB
    LocalAuth -->|"Auth Success"| CreateSession
    LocalAuth -->|"Auth Failure"| User
    
    CreateSession <-->|"Store Session"| SessionDB
    CreateSession -->|"Session Token"| RoleCheck
    
    RoleCheck <-->|"Get User Role"| UserDB
    RoleCheck -->|"Authorized Access"| User
    
    RateLimit <-->|"Check Limits"| RateLimitDB
    RateLimit -->|"Block/Allow"| ValidateInput
    
    style ValidateInput fill:#e3f2fd,stroke:#1565c0
    style CheckAPI fill:#e3f2fd,stroke:#1565c0
    style LocalAuth fill:#e3f2fd,stroke:#1565c0
    style CreateSession fill:#e3f2fd,stroke:#1565c0
    style RoleCheck fill:#e3f2fd,stroke:#1565c0
    style RateLimit fill:#e3f2fd,stroke:#1565c0
```

## Level 2 - Group Management Process

```mermaid
graph TB
    %% External Entities
    Advisor[("👩‍🏫 Advisor")]
    Admin[("👨‍💼 Admin")]
    UniversityAPI[("🏛️ University API")]
    
    %% Sub-processes
    CreateGroup["2.1<br/>Create<br/>Group"]
    AssignStudent["2.2<br/>Assign<br/>Students"]
    AssignAOI["2.3<br/>Assign Area<br/>of Interest"]
    ImportExcel["2.4<br/>Import from<br/>Excel"]
    ValidateGroup["2.5<br/>Validate<br/>Group"]
    ManagePanel["2.6<br/>Manage Panel<br/>Members"]
    
    %% Data Stores
    GroupDB[("D2: Groups")]
    StudentDB[("D9: Group Students")]
    AOIDB[("D10: Areas of Interest")]
    BatchDB[("D11: Batches")]
    
    %% Data Flows
    Advisor -->|"Create Request"| CreateGroup
    Admin -->|"Create Request"| CreateGroup
    CreateGroup <-->|"Store Group"| GroupDB
    CreateGroup -->|"Group Created"| ValidateGroup
    
    Advisor -->|"Student List"| AssignStudent
    AssignStudent <-->|"Verify Students"| UniversityAPI
    AssignStudent <-->|"Store Assignment"| StudentDB
    AssignStudent -->|"Assignment Status"| Advisor
    
    Advisor -->|"AOI Selection"| AssignAOI
    AssignAOI <-->|"Get AOI List"| AOIDB
    AssignAOI <-->|"Update Group"| GroupDB
    
    Advisor -->|"Excel File"| ImportExcel
    ImportExcel -->|"Parse Data"| AssignStudent
    ImportExcel <-->|"Batch Info"| BatchDB
    
    ValidateGroup <-->|"Check Rules"| GroupDB
    ValidateGroup -->|"Validation Result"| Advisor
    
    Admin -->|"Panel Assignment"| ManagePanel
    ManagePanel <-->|"Update Panel"| GroupDB
    
    style CreateGroup fill:#e3f2fd,stroke:#1565c0
    style AssignStudent fill:#e3f2fd,stroke:#1565c0
    style AssignAOI fill:#e3f2fd,stroke:#1565c0
    style ImportExcel fill:#e3f2fd,stroke:#1565c0
    style ValidateGroup fill:#e3f2fd,stroke:#1565c0
    style ManagePanel fill:#e3f2fd,stroke:#1565c0
```

## Level 2 - Supervisor Assignment Process (Lottery Algorithm)

```mermaid
graph TB
    %% External Entities
    Advisor[("👩‍🏫 Advisor")]
    
    %% Sub-processes
    SelectMode["3.1<br/>Select Assignment<br/>Mode"]
    AOIMatch["3.2<br/>AOI-based<br/>Matching"]
    RankMatch["3.3<br/>Rank-based<br/>Assignment"]
    CombinedMatch["3.4<br/>Combined<br/>Algorithm"]
    LoadBalance["3.5<br/>Load<br/>Balancing"]
    RecordHistory["3.6<br/>Record Assignment<br/>History"]
    Preview["3.7<br/>Preview<br/>Assignment"]
    
    %% Data Stores
    GroupDB[("D2: Groups")]
    SupervisorDB[("D3: Supervisors")]
    AOIDB[("D10: Areas of Interest")]
    HistoryDB[("D12: Assignment History")]
    
    %% Data Flows
    Advisor -->|"Assignment Request"| SelectMode
    Advisor -->|"Preview Request"| Preview
    
    SelectMode -->|"AOI Mode"| AOIMatch
    SelectMode -->|"Ranking Mode"| RankMatch
    SelectMode -->|"Combined Mode"| CombinedMatch
    
    AOIMatch <-->|"Get Groups"| GroupDB
    AOIMatch <-->|"Get Supervisors"| SupervisorDB
    AOIMatch <-->|"Match AOI"| AOIDB
    AOIMatch -->|"Assignments"| LoadBalance
    
    RankMatch <-->|"Get Groups"| GroupDB
    RankMatch <-->|"Get by Rank"| SupervisorDB
    RankMatch -->|"Round-Robin"| LoadBalance
    
    CombinedMatch <-->|"Get Groups"| GroupDB
    CombinedMatch <-->|"Get Supervisors"| SupervisorDB
    CombinedMatch <-->|"Match AOI"| AOIDB
    CombinedMatch -->|"Intelligent Assignment"| LoadBalance
    
    LoadBalance <-->|"Check Capacity"| SupervisorDB
    LoadBalance -->|"Final Assignment"| RecordHistory
    LoadBalance -->|"Assignment Result"| Advisor
    
    RecordHistory <-->|"Store History"| HistoryDB
    RecordHistory <-->|"Update Groups"| GroupDB
    
    Preview <-->|"Simulate Assignment"| SupervisorDB
    Preview -->|"Preview Result"| Advisor
    
    style SelectMode fill:#e3f2fd,stroke:#1565c0
    style AOIMatch fill:#e3f2fd,stroke:#1565c0
    style RankMatch fill:#e3f2fd,stroke:#1565c0
    style CombinedMatch fill:#e3f2fd,stroke:#1565c0
    style LoadBalance fill:#e3f2fd,stroke:#1565c0
    style RecordHistory fill:#e3f2fd,stroke:#1565c0
    style Preview fill:#e3f2fd,stroke:#1565c0
```

## Level 2 - Report Management Process

```mermaid
graph TB
    %% External Entities
    Student[("👨‍🎓 Student")]
    Supervisor[("👨‍🏫 Supervisor")]
    CoSupervisor[("👥 Co-Supervisor")]
    PanelMember[("👥 Panel Member")]
    
    %% Sub-processes
    CreateReport["4.1<br/>Create<br/>Report"]
    SubmitReport["4.2<br/>Submit<br/>Report"]
    ReviewReport["4.3<br/>Review<br/>Report"]
    AnnotateReport["4.4<br/>Annotate<br/>Report"]
    CommentReport["4.5<br/>Add<br/>Comments"]
    FinalizeReport["4.6<br/>Finalize<br/>Report"]
    TrackSubmission["4.7<br/>Track<br/>Submissions"]
    
    %% Data Stores
    ReportDB[("D4: Reports")]
    SubmissionDB[("D13: Report Submissions")]
    AnnotationDB[("D14: Annotations")]
    CommentDB[("D15: Comments")]
    FileStorage[("D16: File Storage")]
    
    %% Data Flows
    Supervisor -->|"Create Request"| CreateReport
    CreateReport <-->|"Store Report"| ReportDB
    CreateReport -->|"Report Created"| Student
    
    Student -->|"Upload File"| SubmitReport
    SubmitReport <-->|"Store File"| FileStorage
    SubmitReport <-->|"Record Submission"| SubmissionDB
    SubmitReport -->|"Submission Status"| Student
    
    Supervisor -->|"Review Request"| ReviewReport
    CoSupervisor -->|"Review Request"| ReviewReport
    PanelMember -->|"Review Request"| ReviewReport
    ReviewReport <-->|"Get Report"| ReportDB
    ReviewReport <-->|"Get Submission"| SubmissionDB
    ReviewReport -->|"Review Status"| AnnotateReport
    
    Supervisor -->|"Annotations"| AnnotateReport
    CoSupervisor -->|"Annotations"| AnnotateReport
    PanelMember -->|"Annotations"| AnnotateReport
    AnnotateReport <-->|"Store Annotations"| AnnotationDB
    AnnotateReport -->|"Feedback"| Student
    
    Supervisor -->|"Comments"| CommentReport
    CommentReport <-->|"Store Comments"| CommentDB
    CommentReport -->|"Comment Added"| Student
    
    Supervisor -->|"Finalize"| FinalizeReport
    FinalizeReport <-->|"Update Status"| ReportDB
    FinalizeReport -->|"Final Status"| Student
    
    TrackSubmission <-->|"Get History"| SubmissionDB
    TrackSubmission -->|"Submission History"| Student
    
    style CreateReport fill:#e3f2fd,stroke:#1565c0
    style SubmitReport fill:#e3f2fd,stroke:#1565c0
    style ReviewReport fill:#e3f2fd,stroke:#1565c0
    style AnnotateReport fill:#e3f2fd,stroke:#1565c0
    style CommentReport fill:#e3f2fd,stroke:#1565c0
    style FinalizeReport fill:#e3f2fd,stroke:#1565c0
    style TrackSubmission fill:#e3f2fd,stroke:#1565c0
```

## Level 2 - Meeting Management Process

```mermaid
graph TB
    %% External Entities
    Supervisor[("👨‍🏫 Supervisor")]
    CoSupervisor[("👥 Co-Supervisor")]
    Student[("👨‍🎓 Student")]
    
    %% Sub-processes
    ScheduleMeeting["5.1<br/>Schedule<br/>Meeting"]
    UpdateMeeting["5.2<br/>Update<br/>Meeting"]
    RecordAttendance["5.3<br/>Record<br/>Attendance"]
    ViewSchedule["5.4<br/>View<br/>Schedule"]
    GeneratePDF["5.5<br/>Generate<br/>PDF Report"]
    CheckPermission["5.6<br/>Check<br/>Permissions"]
    
    %% Data Stores
    MeetingDB[("D5: Meetings")]
    AttendanceDB[("D17: Meeting Attendance")]
    GroupDB[("D2: Groups")]
    
    %% Data Flows
    Supervisor -->|"Schedule Request"| CheckPermission
    CoSupervisor -->|"Schedule Request"| CheckPermission
    CheckPermission <-->|"Verify Permission"| GroupDB
    CheckPermission -->|"Allowed"| ScheduleMeeting
    CheckPermission -->|"Denied"| CoSupervisor
    
    ScheduleMeeting <-->|"Store Meeting"| MeetingDB
    ScheduleMeeting -->|"Meeting Created"| Student
    
    Supervisor -->|"Update Request"| UpdateMeeting
    UpdateMeeting <-->|"Update Data"| MeetingDB
    UpdateMeeting -->|"Meeting Updated"| Student
    
    Supervisor -->|"Attendance Data"| RecordAttendance
    RecordAttendance <-->|"Store Attendance"| AttendanceDB
    
    Student -->|"View Request"| ViewSchedule
    ViewSchedule <-->|"Get Meetings"| MeetingDB
    ViewSchedule <-->|"Get Attendance"| AttendanceDB
    ViewSchedule -->|"Schedule Data"| Student
    
    Supervisor -->|"PDF Request"| GeneratePDF
    GeneratePDF <-->|"Get Meeting Data"| MeetingDB
    GeneratePDF -->|"PDF Document"| Supervisor
    
    style ScheduleMeeting fill:#e3f2fd,stroke:#1565c0
    style UpdateMeeting fill:#e3f2fd,stroke:#1565c0
    style RecordAttendance fill:#e3f2fd,stroke:#1565c0
    style ViewSchedule fill:#e3f2fd,stroke:#1565c0
    style GeneratePDF fill:#e3f2fd,stroke:#1565c0
    style CheckPermission fill:#e3f2fd,stroke:#1565c0
```

## Level 2 - Notification System Process

```mermaid
graph TB
    %% External Entities
    EmailSystem[("📧 Email System")]
    Users[("👥 All Users")]
    
    %% Sub-processes
    TriggerNotif["6.1<br/>Trigger<br/>Notification"]
    CreateNotif["6.2<br/>Create<br/>Notification"]
    QueueNotif["6.3<br/>Queue<br/>Notification"]
    SendEmail["6.4<br/>Send<br/>Email"]
    SendInApp["6.5<br/>Send In-App<br/>Notification"]
    MarkRead["6.6<br/>Mark as<br/>Read"]
    
    %% Data Stores
    NotificationDB[("D6: Notifications")]
    QueueDB[("D18: Notification Queue")]
    UserDB[("D1: Users")]
    
    %% Data Flows
    TriggerNotif -->|"Event Data"| CreateNotif
    CreateNotif <-->|"Store Notification"| NotificationDB
    CreateNotif -->|"Notification Created"| QueueNotif
    
    QueueNotif <-->|"Add to Queue"| QueueDB
    QueueNotif -->|"Email Type"| SendEmail
    QueueNotif -->|"In-App Type"| SendInApp
    
    SendEmail <-->|"Get User Email"| UserDB
    SendEmail -->|"Email Data"| EmailSystem
    
    SendInApp <-->|"Get User Data"| UserDB
    SendInApp -->|"Push Notification"| Users
    
    Users -->|"Read Action"| MarkRead
    MarkRead <-->|"Update Status"| NotificationDB
    
    style TriggerNotif fill:#e3f2fd,stroke:#1565c0
    style CreateNotif fill:#e3f2fd,stroke:#1565c0
    style QueueNotif fill:#e3f2fd,stroke:#1565c0
    style SendEmail fill:#e3f2fd,stroke:#1565c0
    style SendInApp fill:#e3f2fd,stroke:#1565c0
    style MarkRead fill:#e3f2fd,stroke:#1565c0
```

## Level 2 - Performance Monitoring Process

```mermaid
graph TB
    %% External Entities
    Admin[("👨‍💼 Admin")]
    UniversityAPI[("🏛️ University API")]
    
    %% Sub-processes
    CollectMetrics["7.1<br/>Collect<br/>Metrics"]
    AnalyzePerf["7.2<br/>Analyze<br/>Performance"]
    CheckHealth["7.3<br/>Check System<br/>Health"]
    MonitorAPI["7.4<br/>Monitor API<br/>Status"]
    SecurityCheck["7.5<br/>Security<br/>Monitoring"]
    GenerateReport["7.6<br/>Generate<br/>Reports"]
    ClearCache["7.7<br/>Clear<br/>Cache"]
    
    %% Data Stores
    MetricsDB[("D19: Performance Metrics")]
    LogDB[("D20: System Logs")]
    CacheDB[("D21: Cache Storage")]
    
    %% Data Flows
    Admin -->|"View Request"| CollectMetrics
    CollectMetrics <-->|"Get Metrics"| MetricsDB
    CollectMetrics -->|"Raw Data"| AnalyzePerf
    
    AnalyzePerf <-->|"Get Logs"| LogDB
    AnalyzePerf -->|"Analysis Result"| GenerateReport
    
    CheckHealth <-->|"System Status"| MetricsDB
    CheckHealth -->|"Health Status"| Admin
    
    MonitorAPI <-->|"API Status"| UniversityAPI
    MonitorAPI <-->|"Store Status"| MetricsDB
    MonitorAPI -->|"API Report"| Admin
    
    SecurityCheck <-->|"Security Logs"| LogDB
    SecurityCheck -->|"Security Report"| Admin
    
    GenerateReport <-->|"Compile Data"| MetricsDB
    GenerateReport -->|"Performance Report"| Admin
    
    Admin -->|"Clear Request"| ClearCache
    ClearCache <-->|"Clear Data"| CacheDB
    ClearCache -->|"Cache Cleared"| Admin
    
    style CollectMetrics fill:#e3f2fd,stroke:#1565c0
    style AnalyzePerf fill:#e3f2fd,stroke:#1565c0
    style CheckHealth fill:#e3f2fd,stroke:#1565c0
    style MonitorAPI fill:#e3f2fd,stroke:#1565c0
    style SecurityCheck fill:#e3f2fd,stroke:#1565c0
    style GenerateReport fill:#e3f2fd,stroke:#1565c0
    style ClearCache fill:#e3f2fd,stroke:#1565c0
```

## Level 3 - Detailed Lottery Algorithm Process

```mermaid
graph TB
    %% Sub-processes for AOI-based Assignment
    Start([Start])
    GetGroups["3.2.1<br/>Get Unassigned<br/>Groups"]
    SortGroups["3.2.2<br/>Sort Groups<br/>by Number"]
    BuildPools["3.2.3<br/>Build Supervisor<br/>Pools by AOI"]
    
    %% Decision Points
    HasGroups{Has<br/>Groups?}
    HasAOI{Group has<br/>AOI?}
    HasSupervisors{AOI has<br/>Supervisors?}
    HasCapacity{Supervisor has<br/>Capacity?}
    MoreGroups{More<br/>Groups?}
    
    %% Assignment Process
    SelectGroup["3.2.4<br/>Select Next<br/>Group"]
    GetAOIList["3.2.5<br/>Get Group's<br/>AOI List"]
    RandomSelect["3.2.6<br/>Random Select<br/>from Pool"]
    CheckHistory["3.2.7<br/>Check Assignment<br/>History"]
    AssignSuper["3.2.8<br/>Assign<br/>Supervisor"]
    UpdateCapacity["3.2.9<br/>Update<br/>Capacity"]
    RecordAssign["3.2.10<br/>Record<br/>Assignment"]
    
    %% Data Stores
    GroupDB[("D2: Groups")]
    SupervisorDB[("D3: Supervisors")]
    HistoryDB[("D12: Assignment History")]
    
    %% Flow
    Start --> GetGroups
    GetGroups <-->|"Query"| GroupDB
    GetGroups --> SortGroups
    SortGroups --> BuildPools
    BuildPools <-->|"Query"| SupervisorDB
    BuildPools --> HasGroups
    
    HasGroups -->|"Yes"| SelectGroup
    HasGroups -->|"No"| End([End])
    
    SelectGroup --> GetAOIList
    GetAOIList --> HasAOI
    
    HasAOI -->|"Yes"| HasSupervisors
    HasAOI -->|"No"| MoreGroups
    
    HasSupervisors -->|"Yes"| CheckHistory
    HasSupervisors -->|"No"| MoreGroups
    
    CheckHistory <-->|"Query"| HistoryDB
    CheckHistory --> RandomSelect
    
    RandomSelect --> HasCapacity
    
    HasCapacity -->|"Yes"| AssignSuper
    HasCapacity -->|"No"| RandomSelect
    
    AssignSuper --> UpdateCapacity
    UpdateCapacity <-->|"Update"| SupervisorDB
    UpdateCapacity --> RecordAssign
    
    RecordAssign <-->|"Store"| HistoryDB
    RecordAssign <-->|"Update"| GroupDB
    RecordAssign --> MoreGroups
    
    MoreGroups -->|"Yes"| SelectGroup
    MoreGroups -->|"No"| End
    
    style Start fill:#c8e6c9,stroke:#2e7d32
    style End fill:#ffcdd2,stroke:#c62828
    style RandomSelect fill:#fff3e0,stroke:#ff6f00
    style AssignSuper fill:#e1f5fe,stroke:#0277bd
```

## Data Dictionary

### Data Stores

| Store ID | Name | Description | Key Attributes |
|----------|------|-------------|----------------|
| D1 | Users | User accounts and authentication | id, email, password, role, name |
| D2 | Groups | Thesis groups | id, name, batch_id, supervisor_id, area_of_interest_id |
| D3 | Supervisors | Supervisor information | id, fullname, designation, rank_priority, max_groups, is_active |
| D4 | Reports | Thesis reports | id, title, group_id, status, deadline, created_by |
| D5 | Meetings | Scheduled meetings | id, group_id, title, date, time, location |
| D6 | Notifications | System notifications | id, user_id, type, message, read_at |
| D7 | Sessions | User sessions | id, user_id, token, expires_at |
| D8 | Rate Limits | API rate limiting | id, key, attempts, reset_at |
| D9 | Group Students | Student-group assignments | id, group_id, student_id, roll_no |
| D10 | Areas of Interest | Research areas | id, name, description |
| D11 | Batches | Student batches | id, batch_name, is_active |
| D12 | Assignment History | Supervisor assignment history | id, group_id, supervisor_id, assigned_at, method |
| D13 | Report Submissions | Student report submissions | id, report_id, student_id, file_path, submitted_at |
| D14 | Annotations | Report annotations | id, submission_id, annotator_id, annotations_data |
| D15 | Comments | Report comments | id, report_id, user_id, comment, created_at |
| D16 | File Storage | Document storage | id, path, type, size, uploaded_by |
| D17 | Meeting Attendance | Meeting attendance records | id, meeting_id, student_id, status |
| D18 | Notification Queue | Queued notifications | id, notification_id, status, attempts |
| D19 | Performance Metrics | System performance data | id, metric_type, value, timestamp |
| D20 | System Logs | Application logs | id, level, message, context, created_at |
| D21 | Cache Storage | Cached data | key, value, expiration |

### External Entities

| Entity | Description | Interactions |
|--------|-------------|--------------|
| Student | Thesis students | Submit reports, view assignments, attend meetings |
| Teacher | Faculty members (multiple roles) | Supervise, review, conduct meetings |
| Admin | System administrators | Configure system, manage users, monitor performance |
| Advisor | Faculty advisors | Create groups, assign supervisors, manage students |
| University API | External authentication system | Verify credentials, fetch student/batch data |
| Email System | Email service provider | Send notifications, alerts, reminders |

### Key Processes

| Process | Description | Key Features |
|---------|-------------|--------------|
| Authentication | Multi-role authentication with external API | Rate limiting, session management, role-based access |
| Group Management | Create and manage thesis groups | Bulk import, Excel integration, validation |
| Supervisor Assignment | Lottery-based assignment algorithm | AOI matching, ranking priority, load balancing |
| Report Management | Complete report lifecycle | Submission, review, annotation, finalization |
| Meeting Management | Schedule and track meetings | Attendance, permissions, PDF generation |
| Notification System | Multi-channel notifications | Email, in-app, queueing, read tracking |
| Performance Monitoring | System health and metrics | API monitoring, security tracking, cache management |

## System Architecture Notes

1. **Multi-Role Support**: Teachers can act as Supervisors, Co-Supervisors, and Panel Members
2. **External API Integration**: Authentication and data synchronization with university systems
3. **Lottery Algorithm**: Three modes - AOI-based, Ranking-based, and Combined
4. **Rate Limiting**: Protection on critical endpoints (12+ protected routes)
5. **File Management**: Support for PDF reports and Excel imports/exports
6. **Real-time Notifications**: Both email and in-app notification channels
7. **Performance Monitoring**: Comprehensive metrics and health checks
8. **Security Features**: CSRF protection, secure sessions, input validation

## Production Deployment Considerations

- **Scalability**: Designed for multiple concurrent users with proper load balancing
- **Reliability**: Assignment history tracking for audit trails
- **Performance**: Caching mechanisms and optimized queries
- **Security**: Multi-layer security with rate limiting and validation
- **Monitoring**: Built-in performance and health monitoring dashboards
- **Maintenance**: Clear separation of concerns with service layer architecture