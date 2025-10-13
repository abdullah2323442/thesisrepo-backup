# Panel Member Use Case Diagram

## Overview
Panel Members serve as reviewers for thesis groups, providing feedback through annotations without management responsibilities.

## Use Case Diagram (Reference Design Style)

```mermaid
graph LR
    %% Actor
    PanelMember((Panel Member))
    
    %% System Boundary
    subgraph System["Thesis Management System"]
        %% Use Cases
        UC1[Report Annotation]
    end
    
    %% Connections
    PanelMember ---|teal| UC1
    
    %% Styling
    classDef actor fill:#009688,stroke:#000,stroke-width:2px
    classDef system fill:#4dabf7,stroke:#000,stroke-width:2px
    classDef usecase fill:#ffffff,stroke:#000,stroke-width:1px
    
    class PanelMember actor
    class System system
    class UC1 usecase
    
    style PanelMember fill:#009688,color:#fff
```

## Detailed Use Case Diagram

```mermaid
graph TB
    %% Actor
    PanelMember[("👤 Panel Member")]
    
    %% System Boundary
    subgraph System["Thesis Management System - Panel Member Module"]
        %% Group Access
        subgraph Groups["Group Access"]
            UC1["View Assigned Groups"]
            UC2["View Group Details"]
            UC3["View Student Information"]
        end
        
        %% Report Review
        subgraph Reports["Report Review"]
            UC4["View Report Details"]
            UC5["View Student Submissions"]
            UC6["Mark as Under Review"]
            UC7["View Inline Documents"]
            UC8["Download Submissions"]
            UC9["Track Review Status"]
        end
        
        %% Report Annotations
        subgraph Annotations["Report Annotations"]
            UC10["Annotate PDF Submissions"]
            UC11["Save Draft Annotations"]
            UC12["Send Feedback to Students"]
            UC13["Trigger Student Notifications"]
            UC14["View Annotation History"]
            UC15["Review Previous Feedback"]
        end
    end
    
    %% Connections
    PanelMember --> UC1
    PanelMember --> UC2
    PanelMember --> UC3
    PanelMember --> UC4
    PanelMember --> UC5
    PanelMember --> UC6
    PanelMember --> UC7
    PanelMember --> UC8
    PanelMember --> UC9
    PanelMember --> UC10
    PanelMember --> UC11
    PanelMember --> UC12
    PanelMember --> UC13
    PanelMember --> UC14
    PanelMember --> UC15
    
    %% Include relationships
    UC12 -.includes.-> UC13
    UC10 -.includes.-> UC11
    
    %% Styling
    classDef actor fill:#e1bee7,stroke:#7b1fa2,stroke-width:3px
    classDef usecase fill:#e8eaf6,stroke:#5e35b1,stroke-width:1px
    classDef subsystem fill:#f5f5f5,stroke:#616161,stroke-width:2px
    
    class PanelMember actor
    class UC1,UC2,UC3,UC4,UC5,UC6,UC7,UC8,UC9,UC10,UC11,UC12,UC13,UC14,UC15 usecase
```

## Simplified Version for Better Readability

```mermaid
graph LR
    %% Actor
    PanelMember((Panel Member))
    
    %% System Boundary
    subgraph System["Panel Member Portal"]
        %% Core Use Cases
        UC1["View Groups - Assigned Groups, Student Details"]
        UC2["Review Reports - View Submissions, Mark Status, Download Files"]
        UC3["Provide Feedback - Annotate PDFs, Send Comments, View History"]
    end
    
    %% Connections
    PanelMember --> UC1
    PanelMember --> UC2
    PanelMember --> UC3
    
    %% Styling
    classDef actor fill:#f3e5f5,stroke:#6a1b9a,stroke-width:3px
    classDef usecase fill:#e0f2f1,stroke:#00695c,stroke-width:2px
    
    class PanelMember actor
    class UC1,UC2,UC3 usecase
```

## Review Workflow Diagram

```mermaid
graph TD
    %% Actor
    PanelMember((Panel Member))
    
    %% Review Workflow
    subgraph ReviewProcess["Panel Review Process"]
        %% Initial Access
        ViewGroups[View Assigned Groups]
        SelectGroup[Select Group to Review]
        
        %% Report Review
        ViewReports[View Group Reports]
        SelectReport[Select Report]
        MarkReview[Mark as Under Review]
        
        %% Submission Review
        ViewSubmission[View Student Submission]
        DownloadPDF[Download PDF]
        
        %% Annotation Process
        AnnotatePDF[Annotate PDF]
        SaveDraft[Save as Draft]
        SendFeedback[Send Feedback]
        NotifyStudent[Student Notified]
        
        %% Flow
        ViewGroups --> SelectGroup
        SelectGroup --> ViewReports
        ViewReports --> SelectReport
        SelectReport --> MarkReview
        SelectReport --> ViewSubmission
        ViewSubmission --> DownloadPDF
        DownloadPDF --> AnnotatePDF
        AnnotatePDF --> SaveDraft
        SaveDraft --> SendFeedback
        SendFeedback --> NotifyStudent
    end
    
    PanelMember --> ViewGroups
    
    %% Styling
    classDef actor fill:#ce93d8,stroke:#6a1b9a,stroke-width:3px
    classDef process fill:#ffffff,stroke:#424242,stroke-width:1px
    classDef notification fill:#fff9c4,stroke:#f57f17,stroke-width:1px
    
    class PanelMember actor
    class ViewGroups,SelectGroup,ViewReports,SelectReport,MarkReview,ViewSubmission,DownloadPDF,AnnotatePDF,SaveDraft,SendFeedback process
    class NotifyStudent notification
```

## Key Use Cases

### Group Access
- **View Only**: Read-only access to assigned groups
- **Group Details**: View group composition and areas of interest
- **Student Information**: Access student details for context

### Report Review
- **Comprehensive View**: Access all report details for assigned groups
- **Submission Access**: View submissions inline or download for offline review
- **Status Tracking**: Mark reports as "under review" to indicate progress
- **Multiple Formats**: Support for PDF, PPT, and PPTX submissions

### Annotation & Feedback
- **PDF Annotation**: Full annotation toolkit for PDF documents
- **Draft System**: Save work in progress before finalizing
- **Feedback Delivery**: Send completed annotations to students
- **Automatic Notifications**: Students receive instant notifications
- **History Tracking**: Access complete annotation history

## Limitations & Restrictions

```mermaid
graph TB
    subgraph Allowed["✓ Panel Member CAN"]
        A1[View Assigned Groups]
        A2[View Reports]
        A3[Download Submissions]
        A4[Annotate PDFs]
        A5[Send Feedback]
        A6[Mark Under Review]
        A7[View History]
    end
    
    subgraph NotAllowed["✗ Panel Member CANNOT"]
        N1[Create Reports]
        N2[Edit Reports]
        N3[Delete Reports]
        N4[Manage Meetings]
        N5[Approve Thesis]
        N6[Assign Students]
        N7[Modify Groups]
    end
    
    %% Styling
    classDef allowed fill:#c8e6c9,stroke:#2e7d32,stroke-width:2px
    classDef notallowed fill:#ffcdd2,stroke:#c62828,stroke-width:2px
    
    class A1,A2,A3,A4,A5,A6,A7 allowed
    class N1,N2,N3,N4,N5,N6,N7 notallowed
```

## Comparison with Other Reviewer Roles

| Feature | Panel Member | Co-Supervisor | Supervisor |
|---------|--------------|---------------|------------|
| View Groups | ✓ | ✓ | ✓ |
| View Reports | ✓ | ✓ | ✓ |
| Annotate Submissions | ✓ | ✓ | ✓ |
| Mark Under Review | ✓ | ✓ | ✓ |
| Manage Meetings | ✗ | Conditional | ✓ |
| Create Reports | ✗ | ✗ | ✓ |
| Delete Reports | ✗ | ✗ | ✓ |
| Final Approval | ✗ | ✗ | ✓ |

## Annotation Features

```mermaid
graph LR
    subgraph AnnotationTools["Annotation Toolkit"]
        Text[Text Comments]
        Highlight[Highlighting]
        Drawing[Drawing Tools]
        Stamps[Review Stamps]
        Notes[Sticky Notes]
    end
    
    subgraph Actions["Actions"]
        Save[Save Draft]
        Send[Send to Student]
        History[View History]
    end
    
    AnnotationTools --> Actions
    
    %% Styling
    classDef tool fill:#e3f2fd,stroke:#1565c0,stroke-width:1px
    classDef action fill:#fff3e0,stroke:#e65100,stroke-width:1px
    
    class Text,Highlight,Drawing,Stamps,Notes tool
    class Save,Send,History action
```

## Access Paths
- `/panel-member/dashboard` - Main panel member dashboard
- `/panel-member/groups` - View assigned groups
- `/panel-member/reports` - Access reports for review

## System Interaction Flow

```mermaid
sequenceDiagram
    participant PM as Panel Member
    participant Sys as System
    participant S as Student
    
    %% Access Flow
    PM->>Sys: Login to system
    Sys-->>PM: Show dashboard
    
    %% Review Flow
    PM->>Sys: View assigned groups
    Sys-->>PM: Display groups
    PM->>Sys: Select report to review
    Sys-->>PM: Show report & submission
    
    %% Annotation Flow
    PM->>Sys: Mark as under review
    Sys-->>PM: Status updated
    PM->>Sys: Annotate PDF
    PM->>Sys: Save as draft
    Sys-->>PM: Draft saved
    PM->>Sys: Send feedback
    Sys->>S: Notification sent
    Sys-->>PM: Feedback delivered
    
    %% History
    PM->>Sys: View annotation history
    Sys-->>PM: Display previous annotations
```

## Best Practices for Panel Members

1. **Timely Review**: Complete reviews within designated timeframes
2. **Constructive Feedback**: Provide detailed, actionable feedback
3. **Draft Management**: Save work regularly to prevent loss
4. **Clear Communication**: Use clear annotations and comments
5. **Status Updates**: Mark reports as "under review" when starting

## Notes
- Panel Members have the most limited role, focused solely on review
- No management capabilities for meetings, groups, or reports
- All feedback automatically triggers student notifications
- Review history is maintained for accountability
- Access is restricted to assigned groups only