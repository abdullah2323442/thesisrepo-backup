# Sequence Diagrams - Thesis Repository Management System

## Table of Contents
1. [System Authentication](#1-system-authentication)
2. [Student Operations](#2-student-operations)
3. [Supervisor Operations](#3-supervisor-operations)
4. [Advisor Operations](#4-advisor-operations)
5. [Administrative Functions](#5-administrative-functions)
6. [System Architecture](#6-system-architecture)

---

## 1. System Authentication

### 1.1 User Authentication Process
```mermaid
sequenceDiagram
    participant User
    participant System
    participant External API
    participant Database

    User->>System: Submit credentials
    System->>System: Identify user type
    
    alt Faculty authentication
        System->>External API: Validate faculty credentials
        External API-->>System: Return user profile
    else Student authentication
        System->>External API: Validate student credentials
        External API-->>System: Return student data
    end
    
    System->>Database: Store/Update user record
    System->>System: Create session
    System-->>User: Redirect to dashboard
```

### 1.2 Role-Based Access Control
```mermaid
sequenceDiagram
    participant User
    participant System
    participant Database

    User->>System: Request resource
    System->>Database: Verify user permissions
    
    alt Authorized
        Database-->>System: Permission granted
        System-->>User: Display resource
    else Unauthorized
        Database-->>System: Permission denied
        System-->>User: Access denied message
    end
```

---

## 2. Student Operations

### 2.1 Report Submission Process
```mermaid
sequenceDiagram
    participant Student
    participant System
    participant Database

    Student->>System: Upload report document
    System->>System: Validate document format
    System->>Database: Store submission
    System->>Database: Create supervisor notification
    System-->>Student: Confirmation message
```

### 2.2 View Feedback and Annotations
```mermaid
sequenceDiagram
    participant Student
    participant System
    participant Database

    Student->>System: Request feedback
    System->>Database: Retrieve annotations
    Database-->>System: Return feedback data
    System-->>Student: Display annotated document
```

### 2.3 Dashboard Notification System
```mermaid
sequenceDiagram
    participant Student
    participant Dashboard
    participant System
    participant Database

    Note over Student,Database: Notifications appear on student dashboard
    
    Student->>Dashboard: Access dashboard
    Dashboard->>System: Check for notifications
    System->>Database: Query unread notifications
    
    alt New notifications exist
        Database-->>System: Return notification list
        System-->>Dashboard: Display notification badge
        Dashboard-->>Student: Show notification count
        
        Student->>Dashboard: Click notification bell
        Dashboard->>System: Fetch notification details
        System->>Database: Retrieve full notifications
        Database-->>System: Return notification data
        System-->>Dashboard: Display notifications
        
        Note over Dashboard: Types of notifications:<br/>• New report assigned<br/>• Report annotated<br/>• New comment added<br/>• Meeting scheduled
        
        Student->>Dashboard: Mark as read
        Dashboard->>System: Update notification status
        System->>Database: Mark notifications read
    else No new notifications
        Database-->>System: Empty result
        System-->>Dashboard: No notifications
        Dashboard-->>Student: Clear notification area
    end
```

---

## 3. Supervisor Operations

### 3.1 Report Management
```mermaid
sequenceDiagram
    participant Supervisor
    participant System
    participant Database

    Supervisor->>System: Create report assignment
    System->>Database: Store report details
    System->>Database: Create student notification
    System-->>Supervisor: Confirmation
    
    Note over Database: Students see notification on dashboard
```

### 3.2 Document Annotation Process

#### 3.2.1 Complete Annotation Workflow
```mermaid
sequenceDiagram
    participant Supervisor
    participant Frontend
    participant Controller
    participant Database
    participant Storage
    participant NotificationService
    participant Student

    Note over Supervisor,Student: Comprehensive PDF Annotation Process
    
    %% Phase 1: Access and Verification
    rect rgb(240, 248, 255)
        Note over Supervisor,Database: Phase 1: Access & Authorization
        Supervisor->>Frontend: Navigate to report submission
        Frontend->>Controller: GET /supervisor/reports/{report}/submissions/{submission}/annotate
        Controller->>Database: Verify supervisor authorization
        
        alt Supervisor authorized
            Controller->>Database: Check supervisor_id matches group.supervisor_id
            Database-->>Controller: Authorization confirmed
            Controller->>Storage: Verify PDF file exists
            Storage-->>Controller: File validation result
            
            alt PDF valid
                Controller->>Database: Retrieve existing annotation sessions
                Database-->>Controller: Return sessions (ordered by version desc)
                Controller-->>Frontend: Render annotation interface
                Frontend-->>Supervisor: Display PDF annotation studio
            else PDF invalid
                Controller-->>Frontend: Return error
                Frontend-->>Supervisor: Show "PDF not found" message
            end
        else Unauthorized
            Controller-->>Frontend: Return 403 error
            Frontend-->>Supervisor: Access denied
        end
    end

    %% Phase 2: Annotation Creation
    rect rgb(255, 248, 240)
        Note over Supervisor,Storage: Phase 2: Interactive Annotation
        
        Supervisor->>Frontend: Select annotation tool
        Note over Frontend: Tools: Comment, Highlight,<br/>Underline, Strikethrough
        
        loop For each annotation
            Supervisor->>Frontend: Click/drag on PDF canvas
            
            alt Comment annotation
                Frontend->>Frontend: Show comment modal
                Supervisor->>Frontend: Enter comment text
                Frontend->>Frontend: Create comment object
                Note over Frontend: {type: 'comment', x, y, page,<br/>comment: text, color}
            else Highlight/Underline/Strikethrough
                Frontend->>Frontend: Track mouse coordinates
                Frontend->>Frontend: Create shape object
                Note over Frontend: {type, startX, startY,<br/>endX, endY, page, color}
            end
            
            Frontend->>Frontend: Add to annotations array
            Frontend->>Frontend: Render annotation on canvas
            Frontend->>Frontend: Update annotation counter
        end
        
        Supervisor->>Frontend: Enter general feedback message (optional)
        Note over Frontend: Message for all group members
    end

    %% Phase 3: Save as Draft
    rect rgb(240, 255, 240)
        Note over Supervisor,Database: Phase 3: Save Annotations (Draft)
        
        Supervisor->>Frontend: Click "Save Annotations"
        Frontend->>Frontend: Prepare payload
        Note over Frontend: {annotations: [...],<br/>feedback_message: "..."}
        
        Frontend->>Controller: POST /supervisor/reports/{report}/submissions/{submission}/annotations
        Controller->>Database: Begin transaction
        
        Controller->>Database: Get next version number
        Database-->>Controller: version = MAX(version) + 1
        
        Controller->>Database: Create ReportAnnotationSession
        Note over Database: Fields stored:<br/>• report_id, submission_id<br/>• supervisor_id, version<br/>• message (feedback)<br/>• annotations_json (array)<br/>• is_sent: false<br/>• created_by_type
        
        Database-->>Controller: Session created with ID
        Controller->>Database: Commit transaction
        
        Controller-->>Frontend: Return success response
        Note over Frontend: {success: true,<br/>session_id, version,<br/>is_sent: false}
        
        Frontend-->>Supervisor: Show "Saved as draft" message
        Frontend->>Frontend: Enable "Send Feedback" button
    end

    %% Phase 4: Send Feedback
    rect rgb(255, 240, 245)
        Note over Supervisor,Student: Phase 4: Send Feedback to Students
        
        Supervisor->>Frontend: Click "Send Feedback"
        Frontend->>Frontend: Show confirmation modal
        Note over Frontend: Display annotation summary:<br/>• Total annotations count<br/>• Comment count<br/>• Feedback preview
        
        Supervisor->>Frontend: Confirm send
        Frontend->>Controller: POST /supervisor/reports/{report}/submissions/{submission}/annotations/{session}/send-feedback
        
        Controller->>Database: Begin transaction
        Controller->>Database: Verify session not already sent
        
        alt Not yet sent
            Controller->>Database: Get all group students
            Database-->>Controller: Return GroupStudent records
            
            loop For each student
                Controller->>Database: Find User by roll/student_id
                Database-->>Controller: Return User or null
                
                alt User found
                    Controller->>NotificationService: Create NewReportAnnotation
                    Note over NotificationService: Notification data:<br/>• type: 'report_annotation'<br/>• report details<br/>• supervisor info<br/>• feedback preview<br/>• version number
                    
                    NotificationService->>Database: Store in notifications table
                    NotificationService->>Student: Trigger real-time update
                    Note over Student: Dashboard notification badge updates
                else User not found
                    Controller->>Controller: Log warning
                end
            end
            
            Controller->>Database: Update annotation session
            Note over Database: Set is_sent = true,<br/>sent_at = now()
            
            Controller->>Database: Commit transaction
            Controller-->>Frontend: Return success
            Frontend-->>Supervisor: "Feedback sent successfully!"
        else Already sent
            Controller-->>Frontend: Return error
            Frontend-->>Supervisor: "Already sent" message
        end
    end

    %% Phase 5: Student Access
    rect rgb(245, 245, 255)
        Note over Student,Storage: Phase 5: Student Views Feedback
        
        Student->>Frontend: Check dashboard notifications
        Frontend->>Database: Query unread notifications
        Database-->>Frontend: Return annotation notifications
        Frontend-->>Student: Display notification badge
        
        Student->>Frontend: Click notification
        Frontend->>Controller: GET /student/reports/{report}/submissions/{submission}/annotations/{session}
        
        Controller->>Database: Verify student group membership
        Database-->>Controller: Confirm access
        
        Controller->>Database: Load annotation session
        Database-->>Controller: Return session with annotations_json
        
        Controller-->>Frontend: Render annotation viewer
        Frontend->>Frontend: Parse annotations_json
        Frontend->>Storage: Load original PDF
        Storage-->>Frontend: Return PDF file
        
        Frontend->>Frontend: Render PDF with annotations
        Note over Frontend: Display:<br/>• PDF pages<br/>• Annotation overlays<br/>• Comment boxes<br/>• Supervisor feedback
        
        Frontend-->>Student: Interactive annotated PDF view
        
        opt Download annotated PDF
            Student->>Frontend: Click download
            Frontend->>Controller: GET /student/.../annotations/{session}/download
            Controller->>Storage: Retrieve annotated file
            Storage-->>Controller: Return file stream
            Controller-->>Student: Download "annotated_feedback_v{version}.pdf"
        end
    end
```

#### 3.2.2 Annotation Data Structure
```mermaid
graph TD
    subgraph "Annotation Session"
        AS[ReportAnnotationSession]
        AS --> R[report_id]
        AS --> S[submission_id]
        AS --> SU[supervisor_id]
        AS --> V[version: incremental]
        AS --> M[message: general feedback]
        AS --> AJ[annotations_json: array]
        AS --> IS[is_sent: boolean]
        AS --> SA[sent_at: timestamp]
        AS --> CT[created_by_type: role]
    end
    
    subgraph "Annotation Object"
        AJ --> AO[Annotation Item]
        AO --> T[type: comment/highlight/underline/strikethrough]
        AO --> P[page: number]
        AO --> CO[Coordinates]
        CO --> XY[x, y: for comments]
        CO --> SE[startX/Y, endX/Y: for shapes]
        CO --> N[xNorm, yNorm: normalized coords]
        AO --> TXT[comment/text: string]
        AO --> COL[color: hex value]
        AO --> ID[id: timestamp]
    end
    
    subgraph "Notification Data"
        AS --> ND[Notification]
        ND --> NT[type: 'report_annotation']
        ND --> GI[group_id, group_name]
        ND --> RI[report_id, report_type]
        ND --> SI[submission_id]
        ND --> SN[supervisor_name, role]
        ND --> FP[feedback_preview]
        ND --> VN[version number]
    end
    
    style AS fill:#e1f5fe,stroke:#01579b,stroke-width:2px
    style AO fill:#fff3e0,stroke:#e65100,stroke-width:2px
    style ND fill:#f3e5f5,stroke:#4a148c,stroke-width:2px
```

#### 3.2.3 Multi-Role Annotation Support
```mermaid
sequenceDiagram
    participant User
    participant System
    participant Database
    
    Note over User,Database: Different roles can annotate
    
    alt Supervisor
        User->>System: Access via /supervisor/reports/.../annotate
        System->>Database: Set created_by_type = 'supervisor'
    else Co-Supervisor
        User->>System: Access via /co-supervisor/reports/.../annotate
        System->>Database: Set created_by_type = 'co_supervisor'
    else Panel Member
        User->>System: Access via /panel-member/reports/.../annotate
        System->>Database: Set created_by_type = 'panel_member'
    end
    
    System->>Database: Store annotation with role identifier
    Database-->>System: Session created with role context
    
    Note over Database: Students see role-specific<br/>notification titles:<br/>"New Supervisor Feedback"<br/>"New Co-Supervisor Feedback"<br/>"New Panel Member Feedback"
```

#### 3.2.4 Annotation History Management
```mermaid
sequenceDiagram
    participant Supervisor
    participant System
    participant Database
    participant Student
    
    Note over Supervisor,Student: Version-controlled annotation history
    
    Supervisor->>System: View annotation history
    System->>Database: Query all sessions for submission
    Database-->>System: Return sessions ordered by version DESC
    
    System-->>Supervisor: Display session list
    Note over Supervisor: Shows for each session:<br/>• Version number<br/>• Creation date<br/>• Annotation count<br/>• Feedback message<br/>• Send status
    
    Supervisor->>System: Create new annotation
    System->>Database: Calculate next version
    Note over Database: version = MAX(version) + 1
    
    System->>Database: Store with new version
    Database-->>System: Confirm creation
    
    parallel Student Access
        Student->>System: View feedback history
        System->>Database: Get all sent sessions
        Database-->>System: Return is_sent=true sessions
        System-->>Student: Display version timeline
        
        Student->>System: Select specific version
        System->>Database: Load session by version
        Database-->>System: Return annotation data
        System-->>Student: Render versioned annotations
    end
```

#### 3.2.5 Real-time Annotation Rendering
```mermaid
sequenceDiagram
    participant Browser
    participant Canvas
    participant AnnotationLayer
    participant PDFRenderer
    
    Note over Browser,PDFRenderer: Client-side annotation rendering
    
    Browser->>PDFRenderer: Load PDF document
    PDFRenderer->>Canvas: Render PDF page
    
    Browser->>Browser: Parse annotations_json
    
    loop For each annotation
        alt Comment type
            Browser->>AnnotationLayer: Create comment marker
            Note over AnnotationLayer: Position at (x,y) or (xNorm,yNorm)
            AnnotationLayer->>AnnotationLayer: Add click handler
            AnnotationLayer->>Canvas: Overlay comment icon 💬
        else Shape type (highlight/underline/strikethrough)
            Browser->>Canvas: Calculate coordinates
            Note over Canvas: Use normalized or absolute coords
            
            alt Highlight
                Canvas->>Canvas: Draw semi-transparent rectangle
                Note over Canvas: fillStyle with alpha 0.3
            else Underline
                Canvas->>Canvas: Draw line below text
                Note over Canvas: Green line at bottom
            else Strikethrough
                Canvas->>Canvas: Draw line through text
                Note over Canvas: Red line at middle
            end
        end
    end
    
    Browser->>Browser: Track annotation state
    Note over Browser: • Show/hide annotations<br/>• Page navigation<br/>• Zoom handling
    
    opt User interaction
        Browser->>AnnotationLayer: Click comment marker
        AnnotationLayer->>Browser: Show comment modal
        Browser-->>Browser: Display comment text
    end
```

### 3.3 Meeting Documentation
```mermaid
sequenceDiagram
    participant Supervisor
    participant System
    participant Database

    Supervisor->>System: Record meeting details
    System->>System: Process attendance data
    System->>Database: Store meeting record
    
    alt Generate report
        Supervisor->>System: Request meeting report
        System->>Database: Compile meeting history
        System-->>Supervisor: Generate PDF report
    end
```

---

## 4. Advisor Operations

### 4.1 Group Formation
```mermaid
sequenceDiagram
    participant Advisor
    participant System
    participant External API
    participant Database

    Advisor->>System: Request student list
    System->>External API: Fetch batch students
    External API-->>System: Return student data
    System-->>Advisor: Display available students
    Advisor->>System: Create groups
    System->>Database: Store group assignments
    System-->>Advisor: Confirmation
```

### 4.2 Supervisor Assignment System

#### 4.2.1 Automated Assignment Process
```mermaid
sequenceDiagram
    participant Advisor
    participant System
    participant Database

    Advisor->>System: Initiate assignment process
    System->>System: Select assignment strategy
    System->>Database: Retrieve eligible groups
    System->>Database: Retrieve available supervisors
    System->>System: Execute assignment algorithm
    System->>Database: Store assignments
    System-->>Advisor: Display results
```

#### 4.2.2 Area of Interest Based Assignment
```mermaid
sequenceDiagram
    participant Advisor
    participant System
    participant Database

    Note over Advisor,Database: Strategy: Match groups with supervisor expertise
    
    Advisor->>System: Select AOI-based assignment
    System->>Database: Retrieve unassigned groups
    System->>Database: Retrieve supervisors with expertise areas
    
    loop For each group
        System->>System: Identify group's area of interest
        System->>System: Find matching supervisors
        System->>System: Random selection from matches
        System->>Database: Assign supervisor to group
    end
    
    System-->>Advisor: Display assignment results
    
    Note over System: Ensures expertise alignment<br/>May result in uneven distribution
```

#### 4.2.3 Ranking Based Assignment
```mermaid
sequenceDiagram
    participant Advisor
    participant System
    participant Database

    Note over Advisor,Database: Strategy: Fair distribution by designation rank
    
    Advisor->>System: Select ranking-based assignment
    System->>Database: Retrieve unassigned groups
    System->>Database: Retrieve supervisors by designation
    System->>System: Sort by rank (Professor to Lecturer)
    
    loop Round-robin assignment
        System->>System: Select next supervisor in rotation
        System->>System: Verify supervisor capacity
        System->>Database: Assign supervisor to group
        System->>System: Move to next supervisor
        
        Note over System: Each supervisor gets one group<br/>before anyone gets second
    end
    
    System-->>Advisor: Display assignment results
    
    Note over System: Ensures equal distribution<br/>Ignores expertise matching
```

#### 4.2.4 Hybrid Assignment Strategy
```mermaid
sequenceDiagram
    participant Advisor
    participant System
    participant Database

    Note over Advisor,Database: Strategy: Balance expertise and fair distribution
    
    Advisor->>System: Select hybrid assignment
    System->>Database: Retrieve unassigned groups
    System->>Database: Retrieve supervisors with expertise
    
    loop For each group
        System->>System: Find area-matching supervisors
        System->>System: Check assignment counts
        System->>System: Select supervisor with minimum load
        
        alt Multiple candidates with same load
            System->>System: Apply rank-based selection
        end
        
        System->>Database: Assign supervisor to group
    end
    
    System-->>Advisor: Display assignment results
    
    Note over System: Optimizes both expertise match<br/>and workload distribution
```

#### 4.2.5 Assignment Strategy Comparison
```mermaid
graph TD
    Start[Assignment Strategy Selection]
    
    Start --> AOI[Area-Based Strategy]
    Start --> Rank[Ranking-Based Strategy]
    Start --> Hybrid[Hybrid Strategy]
    
    AOI --> AOIResult[Expertise-focused<br/>Random within matches<br/>Possible uneven load]
    Rank --> RankResult[Equal distribution<br/>Round-robin assignment<br/>Ignores expertise]
    Hybrid --> HybridResult[Balanced approach<br/>Expertise with fairness<br/>Optimal distribution]
    
    style Start fill:#f9f,stroke:#333,stroke-width:2px
    style AOIResult fill:#e8f5e9,stroke:#4caf50,stroke-width:1px
    style RankResult fill:#e3f2fd,stroke:#2196f3,stroke-width:1px
    style HybridResult fill:#fff3e0,stroke:#ff9800,stroke-width:1px
```

### 4.3 Data Import/Export
```mermaid
sequenceDiagram
    participant Advisor
    participant System
    participant Database

    alt Import Process
        Advisor->>System: Upload spreadsheet
        System->>System: Validate data format
        System->>Database: Store group data
        System-->>Advisor: Import confirmation
    else Export Process
        Advisor->>System: Request template
        System->>Database: Retrieve group data
        System->>System: Generate spreadsheet
        System-->>Advisor: Download file
    end
```

---

## 5. Administrative Functions

### 5.1 External Data Synchronization
```mermaid
sequenceDiagram
    participant Administrator
    participant System
    participant External API
    participant Database

    Administrator->>System: Initiate synchronization
    System->>External API: Request updated data
    External API-->>System: Return data
    System->>System: Process updates
    System->>Database: Update records
    System-->>Administrator: Synchronization report
```

### 5.2 System Monitoring
```mermaid
sequenceDiagram
    participant Administrator
    participant System
    participant Database

    Administrator->>System: Request system metrics
    System->>Database: Query performance data
    Database-->>System: Return statistics
    System->>System: Calculate metrics
    System-->>Administrator: Display dashboard
```

---

## 6. System Architecture

### 6.1 Request Processing Flow
```mermaid
sequenceDiagram
    participant Client
    participant Web Server
    participant Application
    participant Database

    Client->>Web Server: HTTP Request
    Web Server->>Application: Route request
    Application->>Application: Process business logic
    Application->>Database: Data operation
    Database-->>Application: Return data
    Application-->>Web Server: Generate response
    Web Server-->>Client: HTTP Response
```

### 6.2 File Management
```mermaid
sequenceDiagram
    participant User
    participant System
    participant Storage
    participant Database

    User->>System: Upload file
    System->>System: Validate file
    
    alt Valid file
        System->>Storage: Store file
        System->>Database: Save metadata
        System-->>User: Upload successful
    else Invalid file
        System-->>User: Validation error
    end
```

---

## System Components Description

### Core Components
- **Client**: End-user interface (web browser)
- **System**: Application server handling business logic
- **Database**: Persistent data storage
- **External API**: University information systems
- **Storage**: File storage service

### User Roles
- **Student**: Thesis/project students
- **Supervisor**: Faculty members supervising groups
- **Advisor**: Faculty coordinating student groups
- **Administrator**: System administrators

### Key Features
1. **Authentication**: Multi-role authentication with external API integration
2. **Group Management**: Formation and assignment of student groups
3. **Report Workflow**: Creation, submission, and review of reports
4. **Meeting Documentation**: Recording and tracking of supervision meetings
5. **Automated Assignment**: Algorithmic supervisor-group matching
6. **Data Synchronization**: Integration with university systems
7. **In-App Notifications**: Dashboard-based notifications for students on report updates

---

## Notes
These sequence diagrams illustrate the primary interactions within the Thesis Repository Management System, demonstrating the flow of information between system components and user roles. The diagrams follow UML 2.0 notation standards and focus on essential system behaviors rather than implementation details.