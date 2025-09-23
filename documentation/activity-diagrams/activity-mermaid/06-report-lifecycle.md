# Report Submission Lifecycle

```mermaid
flowchart TD
    Start([Supervisor Creates Report]) --> SetDetails[Set Report Details]
    SetDetails --> AssignToGroup[Assign to Group]
    AssignToGroup --> Publish[Publish Report]
    
    Publish --> Notify[System Sends Notifications]
    Notify --> StudentReceive[Student Receives Notification]
    
    StudentReceive --> PrepareReport[Student Prepares Report]
    PrepareReport --> UploadFile[Upload File]
    UploadFile --> Validation{File Valid?}
    
    Validation -->|No| ShowError[Show Error Message]
    ShowError --> FixIssues[Fix Issues]
    FixIssues --> UploadFile
    
    Validation -->|Yes| StoreSubmission[Store Submission]
    StoreSubmission --> NotifySupervisor[Notify Supervisor]
    
    NotifySupervisor --> SupervisorReview[Supervisor Reviews]
    SupervisorReview --> AddAnnotations[Add PDF Annotations]
    AddAnnotations --> WriteFeedback[Write Feedback]
    
    WriteFeedback --> StatusDecision{Status Decision}
    
    StatusDecision -->|Approve| MarkApproved[Mark as Approved]
    MarkApproved --> StudentViewApproval[Student Views Approval]
    StudentViewApproval --> DownloadCert[Download Certificate]
    DownloadCert --> End1([Complete])
    
    StatusDecision -->|Revise| RequestRevision[Request Revision]
    RequestRevision --> StudentViewFeedback[Student Views Feedback]
    StudentViewFeedback --> MakeChanges[Make Changes]
    MakeChanges --> Resubmit[Resubmit Report]
    Resubmit --> SupervisorReview
    
    StatusDecision -->|Reject| RejectReport[Reject Report]
    RejectReport --> StudentViewRejection[Student Views Rejection]
    StudentViewRejection --> MajorChanges[Major Changes Required]
    MajorChanges --> PrepareReport
    
    style Start fill:#4CAF50,color:#fff
    style End1 fill:#f44336,color:#fff
    style Validation fill:#FFE082
    style StatusDecision fill:#FFE082
```

## Description
Complete lifecycle of report submission from creation to final approval.

## Key Stages
1. **Creation**: Supervisor creates and assigns report
2. **Submission**: Student uploads report
3. **Review**: Supervisor reviews and annotates
4. **Decision**: Approve, Revise, or Reject
5. **Revision**: Iterative improvement cycle

## Status Options
- **Approved**: Report accepted, certificate issued
- **Needs Revision**: Minor changes required
- **Rejected**: Major rework needed