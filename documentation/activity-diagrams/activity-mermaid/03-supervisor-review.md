# Supervisor Review Process

```mermaid
flowchart TD
    Start([Login to Dashboard]) --> ViewGroups[View Assigned Groups]
    
    ViewGroups --> Actions{Choose Action}
    Actions -->|Schedule| SchedMeeting[Schedule Meetings]
    Actions -->|Review| SelectReport[Select Report]
    
    SchedMeeting --> End1([Meeting Scheduled])
    
    SelectReport --> Download[Download Submission]
    Download --> ReviewType{Review Type?}
    
    ReviewType -->|Quick| MarkUnder[Mark 'Under Review']
    ReviewType -->|Detailed| OpenPDF[Open PDF Annotator]
    
    OpenPDF --> AddComments[Add Comments & Highlights]
    AddComments --> SaveAnnot[Save Annotations]
    
    MarkUnder --> WriteFeedback[Write Feedback]
    SaveAnnot --> WriteFeedback
    
    WriteFeedback --> Decision{Decision?}
    
    Decision -->|Approve| SetApproved[Set as Approved]
    SetApproved --> AddGrade[Add Final Grade]
    
    Decision -->|Revise| RequestChanges[Request Changes]
    RequestChanges --> SetDeadline[Set Deadline]
    
    Decision -->|Reject| ProvideReasons[Provide Reasons]
    
    AddGrade --> SendStudent[Send to Student]
    SetDeadline --> SendStudent
    ProvideReasons --> SendStudent
    
    SendStudent --> UpdateRecords[Update Records]
    UpdateRecords --> End2([Review Complete])
    
    style Start fill:#4CAF50,color:#fff
    style End1 fill:#f44336,color:#fff
    style End2 fill:#f44336,color:#fff
    style ReviewType fill:#FFE082
    style Decision fill:#FFE082
    style Actions fill:#FFE082
```

## Description
This diagram shows the supervisor's report review workflow, including annotation capabilities and feedback mechanisms.

## Review Types
- **Quick Review**: Mark as under review for later detailed assessment
- **Detailed Review**: Full annotation with PDF tools

## Decision Options
- **Approve**: Accept the report with final grade
- **Revise**: Request specific changes with deadline
- **Reject**: Major issues requiring complete rework