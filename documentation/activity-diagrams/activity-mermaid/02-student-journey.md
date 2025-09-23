# Student Journey Activity Diagram

```mermaid
flowchart TD
    Start([Student Login - User Model]) --> CheckGroup{GroupStudent Assignment?}
    
    CheckGroup -->|Yes| ViewGroup[View Group Details<br/>Supervisor & Co-Supervisor Info]
    CheckGroup -->|No| Wait[Wait for Advisor Assignment]
    Wait --> CheckNotifications[Check Notifications Table]
    CheckNotifications --> CheckGroup
    
    ViewGroup --> ViewBatch[View Batch Information]
    ViewBatch --> CheckMeetings[Check Meeting Schedule]
    CheckMeetings --> ViewReq[View Report Requirements]
    
    ViewReq --> PrepareDoc[Prepare Document]
    PrepareDoc --> CreateSubmission[Create StudentReportSubmission]
    CreateSubmission --> ChooseFormat{Upload Format?}
    
    ChooseFormat -->|PowerPoint| UploadPPT[Upload .pptx file]
    ChooseFormat -->|PDF| UploadPDF[Upload .pdf file]
    ChooseFormat -->|Both| UploadBoth[Upload Multiple Files]
    
    UploadPPT --> SubmitReport[Submit via Report Model]
    UploadPDF --> SubmitReport
    UploadBoth --> SubmitReport
    
    SubmitReport --> TriggerNotif[Trigger NewReportAssigned Notification]
    TriggerNotif --> WaitReview[Wait for Review]
    
    WaitReview --> CheckAnnotations{Check ReportAnnotationSession}
    
    CheckAnnotations -->|Has Annotations| ViewAnnot[View ReportAnnotationSession<br/>& ReportComments]
    ViewAnnot --> CheckCreator{Annotation created_by_type?}
    
    CheckCreator -->|supervisor| SupervisorFeedback[Read Supervisor Feedback]
    CheckCreator -->|advisor| AdvisorFeedback[Read Advisor Feedback]
    CheckCreator -->|panel_member| PanelFeedback[Read Panel Feedback]
    
    SupervisorFeedback --> RecordAttendance[Record MeetingAttendance]
    AdvisorFeedback --> RecordAttendance
    PanelFeedback --> FinalEval[Final Evaluation Stage]
    
    RecordAttendance --> MakeChanges[Revise Based on Comments]
    MakeChanges --> UpdateSubmission[Update StudentReportSubmission]
    UpdateSubmission --> TriggerUpdate[Trigger ReportUpdated Notification]
    TriggerUpdate --> PrepareDoc
    
    FinalEval --> CheckPanel{Panel Approval?}
    CheckPanel -->|Approved| Complete[Mark Report Complete]
    CheckPanel -->|Needs Revision| MakeChanges
    
    Complete --> End([Thesis Complete])
    
    CheckAnnotations -->|No Annotations Yet| WaitReview
    
    style Start fill:#4CAF50,color:#fff
    style End fill:#f44336,color:#fff
    style CheckGroup fill:#FFE082
    style CheckAnnotations fill:#FFE082
    style CheckCreator fill:#FFE082
    style CheckPanel fill:#FFE082
    style ChooseFormat fill:#FFE082
    style TriggerNotif fill:#E1F5FE
    style TriggerUpdate fill:#E1F5FE
```

## Description
This diagram illustrates the complete student journey accurately reflecting the database models and notification system.

## Key Models Used
- **User**: Student authentication and profile
- **GroupStudent**: Student-group membership
- **Group**: Group information with supervisor assignments
- **Batch**: Academic batch/year information
- **Report**: Main report entity
- **StudentReportSubmission**: Individual submission tracking
- **ReportAnnotationSession**: Annotation sessions with created_by_type
- **ReportComment**: Specific feedback comments
- **Meeting & MeetingAttendance**: Meeting tracking
- **Notifications**: NewReportAssigned, ReportUpdated, NewReportComment, NewReportAnnotation

## Key Decision Points
- **GroupStudent Assignment**: Students must be in GroupStudent table to proceed
- **Upload Format**: Flexible file format support
- **Annotation Source**: Different feedback paths based on created_by_type (supervisor/advisor/panel_member)
- **Panel Approval**: Final evaluation by GroupPanelMember

## Revision Cycle
Students iterate through StudentReportSubmission updates based on ReportAnnotationSession feedback until panel approval.