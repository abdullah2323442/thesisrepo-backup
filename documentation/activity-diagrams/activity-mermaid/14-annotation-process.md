# PDF Annotation Process

```mermaid
flowchart TD
    Start([Supervisor Opens Report]) --> SelectSubmission[Select Student Submission]
    SelectSubmission --> ViewOptions{View Options}
    
    ViewOptions -->|View Only| ViewPDF[View PDF in Browser]
    ViewOptions -->|Annotate| StartAnnotation[Start Annotation Session]
    
    ViewPDF --> End1([View Complete])
    
    StartAnnotation --> LoadPDF[Load PDF in Annotator]
    LoadPDF --> AnnotTools[Select Annotation Tools]
    
    AnnotTools --> ToolType{Tool Type}
    
    ToolType -->|Text| AddComment[Add Text Comment]
    ToolType -->|Highlight| HighlightText[Highlight Text]
    ToolType -->|Draw| DrawShapes[Draw Shapes/Arrows]
    ToolType -->|Note| AddStickyNote[Add Sticky Note]
    
    AddComment --> SaveProgress[Save Progress]
    HighlightText --> SaveProgress
    DrawShapes --> SaveProgress
    AddStickyNote --> SaveProgress
    
    SaveProgress --> MoreAnnotations{More Annotations?}
    
    MoreAnnotations -->|Yes| AnnotTools
    MoreAnnotations -->|No| FinalizeSession[Finalize Session]
    
    FinalizeSession --> GenerateAnnotatedPDF[Generate Annotated PDF]
    GenerateAnnotatedPDF --> WriteSummary[Write Overall Feedback]
    
    WriteSummary --> SendFeedback{Send to Student?}
    
    SendFeedback -->|Yes| NotifyStudent[Notify Student]
    NotifyStudent --> StudentAccess[Student Accesses Feedback]
    StudentAccess --> ViewAnnotatedPDF[View Annotated PDF]
    ViewAnnotatedPDF --> DownloadPDF[Download Annotated PDF]
    DownloadPDF --> End2([Process Complete])
    
    SendFeedback -->|No| SaveDraft[Save as Draft]
    SaveDraft --> End3([Saved for Later])
    
    style Start fill:#4CAF50,color:#fff
    style End1 fill:#f44336,color:#fff
    style End2 fill:#f44336,color:#fff
    style End3 fill:#FFA726,color:#fff
    style ViewOptions fill:#FFE082
    style ToolType fill:#FFE082
    style MoreAnnotations fill:#FFE082
    style SendFeedback fill:#FFE082
```

## Description
Detailed PDF annotation workflow for report review and feedback.

## Annotation Tools
- **Text Comments**: Add specific feedback on content
- **Highlights**: Mark important sections
- **Drawings**: Visual indicators and corrections
- **Sticky Notes**: Additional context and suggestions

## Process Flow
1. Supervisor selects submission to review
2. Opens PDF in annotation mode
3. Adds various types of annotations
4. Saves progress periodically
5. Finalizes and generates annotated PDF
6. Sends feedback to student
7. Student views and downloads annotated version