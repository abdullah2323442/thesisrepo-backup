# Student Journey Activity Diagram

```mermaid
flowchart TD
    Start([Student Login]) --> CheckGroup{Assigned to Group?}
    
    CheckGroup -->|Yes| ViewInfo[View Supervisor Info]
    CheckGroup -->|No| Wait[Wait for Assignment]
    Wait --> End1([End])
    
    ViewInfo --> CheckNotif[Check Notifications]
    CheckNotif --> ViewReq[View Report Requirements]
    
    ViewReq --> PrepareDoc[Prepare Document]
    PrepareDoc --> ChooseFormat{Upload Format?}
    
    ChooseFormat -->|PowerPoint| UploadPPT[Upload .pptx file]
    ChooseFormat -->|PDF| UploadPDF[Upload .pdf file]
    
    UploadPPT --> Submit[Submit Report]
    UploadPDF --> Submit
    
    Submit --> WaitReview[Wait for Review]
    WaitReview --> CheckStatus{Status?}
    
    CheckStatus -->|Approved| Download[Download Certificate]
    Download --> Complete[Complete Thesis]
    Complete --> End2([End])
    
    CheckStatus -->|Needs Revision| ViewAnnot[View Annotations]
    ViewAnnot --> ReadFeedback[Read Feedback]
    ReadFeedback --> MakeChanges[Make Changes]
    MakeChanges --> PrepareDoc
    
    CheckStatus -->|Rejected| MajorRev[Major Revision Required]
    MajorRev --> Consult[Consult Supervisor]
    Consult --> PrepareDoc
    
    style Start fill:#4CAF50,color:#fff
    style End1 fill:#f44336,color:#fff
    style End2 fill:#f44336,color:#fff
    style CheckGroup fill:#FFE082
    style CheckStatus fill:#FFE082
    style ChooseFormat fill:#FFE082
```

## Description
This diagram illustrates the complete student journey from login to thesis completion, including the submission and revision cycle.

## Key Decision Points
- **Group Assignment**: Students must be assigned to a group to proceed
- **Upload Format**: Choice between PowerPoint and PDF
- **Review Status**: Approved, Needs Revision, or Rejected

## Revision Cycle
Students may go through multiple revision cycles based on supervisor feedback until the report is approved.