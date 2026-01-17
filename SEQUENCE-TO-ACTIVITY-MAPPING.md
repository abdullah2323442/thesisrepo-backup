# Sequence Diagram to Activity Diagram Mapping

This document provides a comprehensive mapping between the sequence diagrams in `sequence.md` and the corresponding activity diagrams, justifying how the activity diagrams represent and expand upon the sequence-based interactions.

## Overview

**Sequence Diagrams** focus on:
- Interactions between system components
- Message passing and timing
- Object collaboration
- Communication protocols

**Activity Diagrams** focus on:
- Workflow and process flow
- Decision points and branching
- Parallel and sequential activities
- Business logic and algorithms

## Mapping Table

| Sequence Diagram | Activity Diagram | Justification |
|-----------------|------------------|---------------|
| 1.1 User Authentication Process | 1.1 User Authentication Activity Diagram | Expands authentication flow with detailed validation steps, error handling, and session management |
| 1.2 Role-Based Access Control | 1.2 Role-Based Access Control Activity Diagram | Shows decision logic for permission verification and resource access control |
| 2.1 Report Submission Process | 2.1 Report Submission Activity Diagram | Details file validation workflow, storage process, and notification creation |
| 2.2 View Feedback and Annotations | 2.2 View Feedback and Annotations Activity Diagram | Illustrates annotation retrieval, merging process, and display preparation |
| 2.3 Dashboard Notification System | 2.3 Dashboard Notification System Activity Diagram | Shows notification checking logic, display workflow, and read status management |
| 3.1 Report Management | 3.1 Report Management Activity Diagram | Expands report creation workflow with validation and bulk notification process |
| 3.2.1 Annotation Creation and Storage | 3.2 Document Annotation Process Activity Diagram | Details annotation tool usage, local storage, and version management |
| 3.2.2 Feedback Distribution | 3.2 Document Annotation Process Activity Diagram | Integrated into annotation process showing feedback distribution workflow |
| 3.2.3 Student Access to Annotations | 2.2 View Feedback and Annotations Activity Diagram | Covered in student feedback viewing workflow |
| 3.3 Meeting Documentation | 3.3 Meeting Documentation Activity Diagram | Shows attendance processing, note-taking workflow, and report generation |
| 4.1.1 Manual Group Creation | 4.1 Manual Group Creation Activity Diagram | Details student selection, group structure creation, and assignment validation |
| 4.1.2 Excel-Based Group Import | 4.2 Excel-Based Group Import Activity Diagram | Shows file validation, parsing logic, and bulk import transaction |
| 4.1.3 Template Export | 4.3 Template Export Activity Diagram | Illustrates data collection, template generation, and file creation workflow |
| 4.2.1 Automated Assignment Process | 4.4, 4.5, 4.6 Assignment Activity Diagrams | Generic process expanded into three specific strategy implementations |
| 4.2.2 Area of Interest Based Assignment | 4.4 AOI-Based Supervisor Assignment | Details expertise matching algorithm and random selection logic |
| 4.2.3 Ranking Based Assignment | 4.5 Ranking-Based Supervisor Assignment | Shows round-robin algorithm, capacity checking, and fair distribution logic |
| 4.2.4 Hybrid Assignment Strategy | 4.6 Hybrid Strategy Activity Diagram | Illustrates combined approach with load balancing and expertise matching |
| 5.1 External Data Synchronization | 5.1 External Data Synchronization Activity Diagram | Details API communication, data comparison, and update transaction workflow |
| 5.2 System Monitoring | 5.2 System Monitoring Activity Diagram | Shows metrics collection, calculation process, and dashboard rendering |

## Detailed Justifications

### 1. Authentication and Access Control

#### Sequence Diagram 1.1 → Activity Diagram 1.1
**Justification:**
- The sequence diagram shows the message flow between User, System, External API, and Database
- The activity diagram expands this by showing:
  - Detailed credential submission process
  - Branching logic for faculty vs. student authentication
  - Error handling for invalid credentials
  - Session management steps
  - Role-based dashboard routing

**Key Additions in Activity Diagram:**
- Explicit error handling paths
- Session token creation and storage
- Role determination logic
- Dashboard routing based on user type

#### Sequence Diagram 1.2 → Activity Diagram 1.2
**Justification:**
- The sequence diagram shows permission verification interaction
- The activity diagram adds:
  - Session validation before permission check
  - Detailed permission verification logic
  - Resource filtering based on permissions
  - Access logging for both authorized and unauthorized attempts

**Key Additions in Activity Diagram:**
- Session token extraction and validation
- Logging mechanisms
- Data filtering logic
- Error page generation

### 2. Student Operations

#### Sequence Diagram 2.1 → Activity Diagram 2.1
**Justification:**
- The sequence diagram shows the basic submission flow
- The activity diagram expands with:
  - Multi-level file validation (extension, size, structure)
  - Detailed error handling for each validation step
  - Unique filename generation
  - Comprehensive database linking

**Key Additions in Activity Diagram:**
- Three-tier validation process
- Storage management details
- Metadata creation and linking
- Supervisor identification for notifications

#### Sequence Diagram 2.2 → Activity Diagram 2.2
**Justification:**
- The sequence diagram shows annotation retrieval
- The activity diagram details:
  - Access verification process
  - Annotation merging with original PDF
  - Comment loading and display
  - Optional download functionality

**Key Additions in Activity Diagram:**
- Authorization check details
- PDF merging process
- Comment thread display
- Reply functionality

#### Sequence Diagram 2.3 → Activity Diagram 2.3
**Justification:**
- The sequence diagram shows notification checking and display
- The activity diagram expands with:
  - Notification counting and grouping
  - Badge display logic
  - Mark as read functionality
  - Navigation to related resources

**Key Additions in Activity Diagram:**
- Notification type categorization
- Sorting and grouping logic
- Bulk mark as read functionality
- Resource navigation workflow

### 3. Supervisor Operations

#### Sequence Diagram 3.1 → Activity Diagram 3.1
**Justification:**
- The sequence diagram shows report assignment creation
- The activity diagram adds:
  - Detailed input form fields
  - Data validation before storage
  - Group member retrieval
  - Bulk notification creation loop

**Key Additions in Activity Diagram:**
- Input field specifications
- Validation error handling
- Group member iteration
- Notification type specification

#### Sequence Diagrams 3.2.1, 3.2.2, 3.2.3 → Activity Diagram 3.2
**Justification:**
- The three sequence diagrams show different aspects of annotation
- The unified activity diagram combines:
  - Annotation creation workflow
  - Multiple annotation type handling
  - Version management
  - Feedback distribution
  - Student access (cross-referenced with 2.2)

**Key Additions in Activity Diagram:**
- Annotation tool type selection
- Local storage before saving
- Version number generation
- Conditional feedback distribution

#### Sequence Diagram 3.3 → Activity Diagram 3.3
**Justification:**
- The sequence diagram shows meeting recording
- The activity diagram expands with:
  - Detailed meeting information fields
  - Attendance marking for each student
  - Meeting notes and action items
  - Optional report generation

**Key Additions in Activity Diagram:**
- Meeting detail specifications
- Individual attendance processing
- Action item tracking
- PDF report generation workflow

### 4. Advisor Operations

#### Sequence Diagram 4.1.1 → Activity Diagram 4.1
**Justification:**
- The sequence diagram shows manual group creation flow
- The activity diagram details:
  - External API integration for student data
  - Group structure calculation
  - Interactive assignment interface
  - Assignment validation

**Key Additions in Activity Diagram:**
- Batch selection process
- Group number calculation
- Side-by-side display interface
- Validation error handling

#### Sequence Diagram 4.1.2 → Activity Diagram 4.2
**Justification:**
- The sequence diagram shows Excel import process
- The activity diagram expands with:
  - Multi-level file validation
  - Detailed parsing logic
  - Transaction management
  - Error reporting with row highlighting

**Key Additions in Activity Diagram:**
- File extension and structure validation
- Student ID and group name validation
- Transaction rollback capability
- Detailed import statistics

#### Sequence Diagram 4.1.3 → Activity Diagram 4.3
**Justification:**
- The sequence diagram shows template export
- The activity diagram details:
  - Data collection from multiple sources
  - Excel workbook structure creation
  - Data validation rules
  - Instructions sheet generation

**Key Additions in Activity Diagram:**
- Column specifications
- Data validation setup
- Formatting details
- Instructions sheet content

#### Sequence Diagram 4.2.2 → Activity Diagram 4.4
**Justification:**
- The sequence diagram shows AOI-based assignment
- The activity diagram expands with:
  - Detailed matching algorithm
  - Exception handling for unmatched groups
  - Statistics calculation
  - Distribution analysis

**Key Additions in Activity Diagram:**
- Expertise matching logic
- Random selection from matches
- Exception list management
- Comprehensive statistics

#### Sequence Diagram 4.2.3 → Activity Diagram 4.5
**Justification:**
- The sequence diagram shows ranking-based assignment
- The activity diagram details:
  - Rank ordering specification
  - Round-robin implementation
  - Capacity verification
  - Fair distribution calculation

**Key Additions in Activity Diagram:**
- Explicit rank order
- Round counter management
- Capacity checking logic
- Distribution variance calculation

#### Sequence Diagram 4.2.4 → Activity Diagram 4.6
**Justification:**
- The sequence diagram shows hybrid strategy
- The activity diagram expands with:
  - Two-phase matching (expertise + load)
  - Tiebreaker logic
  - Optimization scoring
  - Balanced distribution analysis

**Key Additions in Activity Diagram:**
- Expertise filtering logic
- Load balancing algorithm
- Rank-based tiebreaker
- Optimization metrics

### 5. Administrative Functions

#### Sequence Diagram 5.1 → Activity Diagram 5.1
**Justification:**
- The sequence diagram shows synchronization flow
- The activity diagram adds:
  - Configuration options
  - Authentication handling
  - Data comparison logic
  - Transaction management
  - Comprehensive reporting

**Key Additions in Activity Diagram:**
- Data type selection
- Authentication error handling
- Record type identification (new/updated/deleted)
- Detailed sync statistics

#### Sequence Diagram 5.2 → Activity Diagram 5.2
**Justification:**
- The sequence diagram shows monitoring request
- The activity diagram expands with:
  - Multiple metrics collection
  - Calculation algorithms
  - Trend analysis
  - Alert generation
  - Auto-refresh capability

**Key Additions in Activity Diagram:**
- Specific metrics enumeration
- Historical comparison
- Anomaly detection
- Alert notification logic

## Benefits of Activity Diagrams

The activity diagrams provide several advantages over sequence diagrams:

### 1. **Control Flow Visibility**
- Shows decision points clearly
- Illustrates loops and iterations
- Demonstrates parallel activities
- Highlights alternative paths

### 2. **Business Logic Documentation**
- Details validation rules
- Shows calculation processes
- Illustrates algorithm implementations
- Documents business rules

### 3. **Error Handling**
- Explicit error paths
- Validation failure handling
- Exception management
- Recovery procedures

### 4. **Implementation Guidance**
- Step-by-step process flow
- Data transformation details
- Integration points
- Transaction boundaries

### 5. **Completeness**
- End-to-end workflow coverage
- All possible paths documented
- Edge cases considered
- Success and failure scenarios

## Usage Guidelines

### For Developers
- Use sequence diagrams to understand component interactions
- Use activity diagrams to implement workflow logic
- Cross-reference both for complete understanding
- Follow activity diagram paths for test case creation

### For Business Analysts
- Use sequence diagrams for system communication overview
- Use activity diagrams for business process documentation
- Reference activity diagrams for requirement validation
- Use for user story elaboration

### For Testers
- Use sequence diagrams to identify integration points
- Use activity diagrams to create test scenarios
- Follow all paths in activity diagrams for test coverage
- Validate decision points and error handling

### For Project Managers
- Use sequence diagrams for high-level system overview
- Use activity diagrams for task breakdown
- Reference for effort estimation
- Use for progress tracking

## Conclusion

The activity diagrams successfully justify and expand upon the sequence diagrams by:

1. **Maintaining Consistency**: All interactions shown in sequence diagrams are represented in activity diagrams
2. **Adding Detail**: Activity diagrams provide implementation-level details not visible in sequence diagrams
3. **Showing Logic**: Decision points, loops, and conditional flows are explicitly documented
4. **Handling Errors**: Error paths and exception handling are clearly illustrated
5. **Supporting Implementation**: Developers can follow activity diagrams to implement features

Together, the sequence and activity diagrams provide comprehensive documentation of the University Thesis Management System, supporting all stakeholders from business analysts to developers to testers.
