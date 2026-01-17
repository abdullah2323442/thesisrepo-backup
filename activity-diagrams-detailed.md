# Detailed Activity Diagrams - Justifying Sequence Diagrams

This document contains PlantUML activity diagrams that correspond to and justify each sequence diagram in `sequence.md`.

## Table of Contents
1. [Authentication Process](#1-authentication-process)
2. [Student Operations](#2-student-operations)
3. [Supervisor Operations](#3-supervisor-operations)
4. [Advisor Operations](#4-advisor-operations)
5. [Administrative Functions](#5-administrative-functions)

---

## 1. Authentication Process

### 1.1 User Authentication Activity Diagram

```plantuml
@startuml User Authentication Process
!theme plain
skinparam backgroundColor #FFFFFF

title User Authentication Process\n(Justifies Sequence Diagram 1.1)

start

:User Accesses System;

partition "Credential Submission" {
    :Enter Username;
    :Enter Password;
    :Submit Credentials;
}

partition "User Type Identification" {
    if (User Type?) then (Faculty)
        :Route to Faculty Authentication;
        
        partition "Faculty Authentication" {
            :Call External API;
            :Validate Faculty Credentials;
            
            if (Valid Credentials?) then (yes)
                :Retrieve Faculty Profile;
                :Extract Role Information;
            else (no)
                :Return Authentication Error;
                stop
            endif
        }
        
    else (Student)
        :Route to Student Authentication;
        
        partition "Student Authentication" {
            :Call External API;
            :Validate Student Credentials;
            
            if (Valid Credentials?) then (yes)
                :Retrieve Student Data;
                :Extract Batch Information;
            else (no)
                :Return Authentication Error;
                stop
            endif
        }
    endif
}

partition "Session Management" {
    :Store User Record in Database;
    :Update Last Login Timestamp;
    :Create Session Token;
    :Set Session Expiry;
    :Store Session in Cache;
}

partition "Dashboard Routing" {
    if (User Role?) then (Student)
        :Redirect to Student Dashboard;
    elseif (Supervisor) then
        :Redirect to Supervisor Dashboard;
    elseif (Advisor) then
        :Redirect to Advisor Dashboard;
    else (Administrator)
        :Redirect to Admin Dashboard;
    endif
}

:Display Welcome Message;

stop

@enduml
```

### 1.2 Role-Based Access Control Activity Diagram

```plantuml
@startuml Role-Based Access Control
!theme plain
skinparam backgroundColor #FFFFFF

title Role-Based Access Control\n(Justifies Sequence Diagram 1.2)

start

:User Requests Resource;

partition "Request Validation" {
    :Extract Session Token;
    :Validate Session;
    
    if (Valid Session?) then (no)
        :Return Unauthorized Error;
        stop
    else (yes)
        :Retrieve User Information;
    endif
}

partition "Permission Verification" {
    :Identify Required Permission;
    :Query User Permissions from Database;
    :Check Role-Based Rules;
    
    if (Has Permission?) then (yes)
        partition "Authorized Access" {
            :Log Access Event;
            :Retrieve Resource Data;
            :Apply Data Filters (if needed);
            :Render Resource View;
            :Display Resource to User;
        }
    else (no)
        partition "Unauthorized Access" {
            :Log Access Attempt;
            :Generate Access Denied Message;
            :Display Error Page;
        }
    endif
}

stop

@enduml
```

---

## 2. Student Operations

### 2.1 Report Submission Activity Diagram

```plantuml
@startuml Report Submission Process
!theme plain
skinparam backgroundColor #FFFFFF

title Report Submission Process\n(Justifies Sequence Diagram 2.1)

start

:Student Navigates to Report Submission;

partition "Document Selection" {
    :Click Upload Button;
    :Select Document from File System;
    :Confirm Selection;
}

partition "Document Validation" {
    :Check File Extension;
    
    if (Valid Extension?) then (no)
        :Display Format Error;
        stop
    else (yes)
        :Check File Size;
        
        if (Within Size Limit?) then (no)
            :Display Size Error;
            stop
        else (yes)
            :Validate PDF Structure;
            
            if (Valid PDF?) then (no)
                :Display Corruption Error;
                stop
            endif
        endif
    endif
}

partition "Submission Processing" {
    :Generate Unique Filename;
    :Upload to Storage;
    :Create Database Record;
    :Link to Student;
    :Link to Report Assignment;
    :Set Submission Timestamp;
}

partition "Notification Creation" {
    :Identify Supervisor;
    :Create Notification Record;
    :Set Notification Type: "New Submission";
    :Store in Database;
}

:Display Success Message;
:Show Submission Details;

stop

@enduml
```

### 2.2 View Feedback and Annotations Activity Diagram

```plantuml
@startuml View Feedback and Annotations
!theme plain
skinparam backgroundColor #FFFFFF

title View Feedback and Annotations\n(Justifies Sequence Diagram 2.2)

start

:Student Clicks Notification;

partition "Access Verification" {
    :Extract Report ID;
    :Verify Student Ownership;
    
    if (Authorized?) then (no)
        :Display Access Denied;
        stop
    endif
}

partition "Annotation Retrieval" {
    :Query Annotation Sessions;
    :Retrieve Latest Version;
    :Fetch Annotation Data;
    :Load Original PDF;
}

partition "Display Preparation" {
    :Merge Annotations with PDF;
    :Prepare Viewer Interface;
    :Load Comments;
    :Highlight Annotated Sections;
}

:Display Annotated Document;

if (Student Action?) then (Download)
    :Generate Download Link;
    :Provide PDF File;
elseif (View Comments) then
    :Display Comment Thread;
elseif (Reply to Comment) then
    :Open Reply Interface;
    :Submit Reply;
    :Create Notification for Supervisor;
endif

stop

@enduml
```

### 2.3 Dashboard Notification System Activity Diagram

```plantuml
@startuml Dashboard Notification System
!theme plain
skinparam backgroundColor #FFFFFF

title Dashboard Notification System\n(Justifies Sequence Diagram 2.3)

start

:Student Accesses Dashboard;

partition "Notification Check" {
    :Query Unread Notifications;
    :Count Notification Types;
    
    if (Has Unread Notifications?) then (yes)
        :Calculate Total Count;
        :Display Notification Badge;
        :Show Count on Bell Icon;
    else (no)
        :Clear Notification Area;
        :Hide Badge;
    endif
}

if (Student Clicks Bell Icon?) then (yes)
    partition "Notification Display" {
        :Fetch Full Notification Details;
        :Sort by Timestamp (Newest First);
        :Group by Type;
        
        :Display Notification List;
        note right
            Notification Types:
            • New report assigned
            • Report annotated
            • New comment added
            • Meeting scheduled
        end note
    }
    
    if (Student Clicks Notification?) then (yes)
        :Mark as Read;
        :Update Database;
        :Navigate to Related Resource;
    endif
    
    if (Mark All as Read?) then (yes)
        :Update All Notifications;
        :Clear Badge;
        :Refresh Display;
    endif
endif

stop

@enduml
```

---

## 3. Supervisor Operations

### 3.1 Report Management Activity Diagram

```plantuml
@startuml Report Management
!theme plain
skinparam backgroundColor #FFFFFF

title Report Management\n(Justifies Sequence Diagram 3.1)

start

:Supervisor Accesses Report Management;

partition "Report Assignment Creation" {
    :Click Create New Report;
    :Enter Report Details;
    note right
        • Report title
        • Description
        • Due date
        • Report type
    end note
    
    :Select Target Group;
    :Set Submission Deadline;
    :Add Instructions;
}

partition "Database Storage" {
    :Validate Input Data;
    
    if (Valid Data?) then (yes)
        :Create Report Record;
        :Store Report Details;
        :Link to Supervisor;
        :Link to Group;
        :Set Status: "Pending";
    else (no)
        :Display Validation Errors;
        stop
    endif
}

partition "Student Notification" {
    :Retrieve Group Members;
    
    repeat
        :Create Notification for Student;
        :Set Type: "New Report Assigned";
        :Store in Database;
    repeat while (More Students?) is (yes)
}

:Display Confirmation Message;
:Show Report Summary;

note right
    Students immediately see
    notification on their dashboard
end note

stop

@enduml
```

### 3.2 Document Annotation Process Activity Diagram

```plantuml
@startuml Document Annotation Process
!theme plain
skinparam backgroundColor #FFFFFF

title Document Annotation Process\n(Justifies Sequence Diagrams 3.2.1, 3.2.2, 3.2.3)

start

:Supervisor Accesses Report Submission;

partition "Authorization Check" {
    :Verify Supervisor Assignment;
    
    if (Authorized?) then (no)
        :Display Access Denied;
        stop
    endif
}

partition "PDF Interface Loading" {
    :Load PDF Document;
    :Initialize Annotation Tools;
    :Display PDF Viewer;
}

partition "Annotation Creation" {
    repeat
        if (Annotation Type?) then (Highlight)
            :Select Text;
            :Apply Highlight;
            :Choose Color;
        elseif (Comment) then
            :Click Location;
            :Enter Comment Text;
            :Set Comment Type;
        elseif (Drawing) then
            :Use Drawing Tool;
            :Create Shape/Line;
        else (Stamp)
            :Select Stamp Type;
            :Place on Document;
        endif
        
        :Store Annotation Locally;
    repeat while (More Annotations?) is (yes)
}

partition "Save Annotations" {
    :Click Save Button;
    :Create Annotation Session;
    :Generate Version Number;
    :Store Annotations in Database;
    :Link to Report Submission;
    :Set Timestamp;
}

partition "Feedback Distribution" {
    if (Send Feedback Now?) then (yes)
        :Retrieve Student List from Group;
        
        repeat
            :Create Notification;
            :Set Type: "Report Annotated";
            :Include Annotation Summary;
            :Store Notification;
        repeat while (More Students?) is (yes)
        
        :Update Report Status;
        :Set Status: "Reviewed";
    endif
}

:Display Success Confirmation;

note right
    Students can now access
    annotated document from
    their dashboard
end note

stop

@enduml
```

### 3.3 Meeting Documentation Activity Diagram

```plantuml
@startuml Meeting Documentation
!theme plain
skinparam backgroundColor #FFFFFF

title Meeting Documentation\n(Justifies Sequence Diagram 3.3)

start

:Supervisor Accesses Meeting Management;

partition "Meeting Record Creation" {
    :Click Record New Meeting;
    :Enter Meeting Details;
    note right
        • Meeting date
        • Meeting time
        • Duration
        • Location/Mode
        • Agenda
    end note
    
    :Select Group;
}

partition "Attendance Processing" {
    :Display Group Members;
    
    repeat
        :Mark Student Attendance;
        if (Present?) then (yes)
            :Set Status: Present;
        else (no)
            :Set Status: Absent;
            :Add Reason (optional);
        endif
    repeat while (More Students?) is (yes)
}

partition "Meeting Notes" {
    :Enter Discussion Points;
    :Add Action Items;
    :Set Follow-up Tasks;
    :Attach Documents (optional);
}

partition "Database Storage" {
    :Create Meeting Record;
    :Store Meeting Details;
    :Store Attendance Records;
    :Link to Group;
    :Link to Supervisor;
    :Set Timestamp;
}

if (Generate Report?) then (yes)
    partition "Report Generation" {
        :Query Meeting History;
        :Compile Attendance Statistics;
        :Format Meeting Details;
        :Generate PDF Report;
        :Provide Download Link;
    }
endif

:Display Confirmation;

stop

@enduml
```

---

## 4. Advisor Operations

### 4.1 Manual Group Creation Activity Diagram

```plantuml
@startuml Manual Group Creation
!theme plain
skinparam backgroundColor #FFFFFF

title Manual Group Creation\n(Justifies Sequence Diagram 4.1.1)

start

:Advisor Accesses Group Management;

partition "Student List Retrieval" {
    :Select Batch;
    :Call External API;
    :Fetch Batch Students;
    :Receive Student Data;
    :Display Available Students;
}

partition "Group Structure Creation" {
    :Enter Number of Groups;
    :Calculate Groups Needed;
    
    repeat
        :Create Group Record;
        :Generate Group Name;
        :Store in Database;
    repeat while (More Groups to Create?) is (yes)
}

partition "Manual Student Assignment" {
    :Display Groups and Students Side-by-Side;
    
    repeat
        :Select Student;
        :Select Target Group;
        :Validate Assignment;
        
        if (Valid Assignment?) then (yes)
            :Store Assignment in Database;
            :Update Group Member Count;
            :Remove from Available List;
        else (no)
            :Display Validation Error;
        endif
    repeat while (More Students to Assign?) is (yes)
}

:Display Group Formation Summary;
:Show Group Compositions;

stop

@enduml
```

### 4.2 Excel-Based Group Import Activity Diagram

```plantuml
@startuml Excel-Based Group Import
!theme plain
skinparam backgroundColor #FFFFFF

title Excel-Based Group Import\n(Justifies Sequence Diagram 4.1.2)

start

:Advisor Clicks Import Groups;

partition "File Upload" {
    :Select Excel File;
    :Upload File to Server;
}

partition "File Validation" {
    :Check File Extension;
    
    if (Valid Extension?) then (no)
        :Display Format Error;
        stop
    endif
    
    :Parse Excel Structure;
    
    if (Valid Structure?) then (no)
        :Display Structure Error;
        :Show Expected Format;
        stop
    endif
}

partition "Data Parsing" {
    :Read Student-Group Mappings;
    :Validate Student IDs;
    :Validate Group Names;
    
    if (Valid Data?) then (no)
        :Generate Error Report;
        :Highlight Invalid Rows;
        :Display Errors;
        stop
    endif
}

partition "Database Operations" {
    :Begin Transaction;
    :Clear Existing Assignments;
    
    repeat
        :Check if Group Exists;
        
        if (Group Exists?) then (no)
            :Create New Group;
        endif
    repeat while (More Groups?) is (yes)
    
    :Randomize Group Allocation;
    :Bulk Insert Assignments;
    :Commit Transaction;
}

:Generate Import Summary;
:Display Success Statistics;
note right
    • Total students imported
    • Groups created
    • Assignments made
end note

stop

@enduml
```

### 4.3 Template Export Activity Diagram

```plantuml
@startuml Template Export for Group Assignment
!theme plain
skinparam backgroundColor #FFFFFF

title Template Export for Group Assignment\n(Justifies Sequence Diagram 4.1.3)

start

:Advisor Clicks Export Template;

partition "Data Collection" {
    :Select Batch;
    :Call External API;
    :Fetch Batch Students;
    :Receive Student List;
    
    :Query Existing Groups;
    :Retrieve Group Data from Database;
}

partition "Template Generation" {
    :Create Excel Workbook;
    
    :Add Header Row;
    note right
        Columns:
        • Student ID
        • Student Name
        • Group (empty)
    end note
    
    repeat
        :Add Student Row;
        :Fill Student ID;
        :Fill Student Name;
        :Leave Group Column Empty;
    repeat while (More Students?) is (yes)
    
    :Add Instructions Sheet;
    :Add Available Groups List;
    :Format Cells;
    :Apply Data Validation;
}

:Generate Download Link;
:Provide Template File;

:Display Download Success;

stop

@enduml
```

### 4.4 Supervisor Assignment - AOI-Based Activity Diagram

```plantuml
@startuml AOI-Based Supervisor Assignment
!theme plain
skinparam backgroundColor #FFFFFF

title Area of Interest Based Assignment\n(Justifies Sequence Diagram 4.2.2)

start

:Advisor Selects AOI-Based Assignment;

partition "Data Retrieval" {
    :Query Unassigned Groups;
    :Retrieve Groups with Area of Interest;
    
    :Query Available Supervisors;
    :Retrieve Supervisors with Expertise Areas;
}

partition "Assignment Algorithm" {
    repeat
        :Select Next Unassigned Group;
        :Identify Group's Area of Interest;
        
        :Find Supervisors with Matching Expertise;
        
        if (Matching Supervisors Found?) then (yes)
            :Random Selection from Matches;
            :Assign Supervisor to Group;
            :Store Assignment in Database;
            :Create Assignment History Record;
        else (no)
            :Mark Group as Unassigned;
            :Add to Exception List;
        endif
    repeat while (More Groups?) is (yes)
}

partition "Results Processing" {
    :Calculate Assignment Statistics;
    :Generate Distribution Report;
    
    note right
        Statistics:
        • Total assignments made
        • Expertise match rate
        • Supervisor load distribution
        • Unassigned groups
    end note
}

:Display Assignment Results;

note right
    Ensures expertise alignment
    May result in uneven distribution
end note

stop

@enduml
```

### 4.5 Supervisor Assignment - Ranking-Based Activity Diagram

```plantuml
@startuml Ranking-Based Supervisor Assignment
!theme plain
skinparam backgroundColor #FFFFFF

title Ranking-Based Supervisor Assignment\n(Justifies Sequence Diagram 4.2.3)

start

:Advisor Selects Ranking-Based Assignment;

partition "Data Retrieval" {
    :Query Unassigned Groups;
    :Retrieve All Groups;
    
    :Query Available Supervisors;
    :Retrieve Supervisors by Designation;
}

partition "Supervisor Sorting" {
    :Sort Supervisors by Rank;
    note right
        Rank Order:
        1. Professor
        2. Associate Professor
        3. Assistant Professor
        4. Senior Lecturer
        5. Lecturer
    end note
    
    :Initialize Round-Robin Counter;
}

partition "Round-Robin Assignment" {
    repeat
        :Select Next Supervisor in Rotation;
        :Verify Supervisor Capacity;
        
        if (Has Capacity?) then (yes)
            :Select Next Unassigned Group;
            :Assign Supervisor to Group;
            :Store Assignment in Database;
            :Increment Supervisor Load;
        endif
        
        :Move to Next Supervisor;
        
        if (End of Supervisor List?) then (yes)
            :Reset to First Supervisor;
            :Increment Round Counter;
        endif
    repeat while (Unassigned Groups Exist?) is (yes)
}

partition "Results Processing" {
    :Calculate Distribution Statistics;
    :Generate Fairness Report;
    
    note right
        Statistics:
        • Groups per supervisor
        • Distribution variance
        • Assignment rounds completed
    end note
}

:Display Assignment Results;

note right
    Ensures equal distribution
    Ignores expertise matching
end note

stop

@enduml
```

### 4.6 Supervisor Assignment - Hybrid Strategy Activity Diagram

```plantuml
@startuml Hybrid Supervisor Assignment Strategy
!theme plain
skinparam backgroundColor #FFFFFF

title Hybrid Assignment Strategy\n(Justifies Sequence Diagram 4.2.4)

start

:Advisor Selects Hybrid Assignment;

partition "Data Retrieval" {
    :Query Unassigned Groups;
    :Retrieve Groups with Area of Interest;
    
    :Query Available Supervisors;
    :Retrieve Supervisors with Expertise and Rank;
}

partition "Hybrid Assignment Algorithm" {
    repeat
        :Select Next Unassigned Group;
        :Identify Group's Area of Interest;
        
        partition "Expertise Matching" {
            :Find Supervisors with Matching Expertise;
            
            if (Matches Found?) then (yes)
                :Filter to Matching Supervisors;
            else (no)
                :Use All Available Supervisors;
            endif
        }
        
        partition "Load Balancing" {
            :Check Current Assignment Counts;
            :Identify Supervisors with Minimum Load;
            
            if (Multiple Candidates with Same Load?) then (yes)
                partition "Rank-Based Tiebreaker" {
                    :Sort Candidates by Rank;
                    :Select Highest Ranked;
                }
            else (no)
                :Select Supervisor with Minimum Load;
            endif
        }
        
        :Assign Supervisor to Group;
        :Store Assignment in Database;
        :Update Supervisor Load Counter;
        :Create Assignment History Record;
        
    repeat while (More Groups?) is (yes)
}

partition "Results Processing" {
    :Calculate Comprehensive Statistics;
    :Generate Balanced Report;
    
    note right
        Statistics:
        • Expertise match rate
        • Load distribution variance
        • Average groups per supervisor
        • Optimization score
    end note
}

:Display Assignment Results;

note right
    Optimizes both expertise match
    and workload distribution
end note

stop

@enduml
```

---

## 5. Administrative Functions

### 5.1 External Data Synchronization Activity Diagram

```plantuml
@startuml External Data Synchronization
!theme plain
skinparam backgroundColor #FFFFFF

title External Data Synchronization\n(Justifies Sequence Diagram 5.1)

start

:Administrator Initiates Synchronization;

partition "Synchronization Configuration" {
    :Select Data Type to Sync;
    note right
        Options:
        • Student data
        • Faculty data
        • Batch information
        • All data
    end note
    
    :Set Sync Parameters;
}

partition "External API Communication" {
    :Establish API Connection;
    :Authenticate with External System;
    
    if (Authentication Successful?) then (no)
        :Log Error;
        :Display Connection Error;
        stop
    endif
    
    :Request Updated Data;
    :Receive Data Response;
}

partition "Data Processing" {
    :Parse Received Data;
    :Validate Data Structure;
    
    if (Valid Data?) then (no)
        :Log Validation Error;
        :Display Data Error;
        stop
    endif
    
    :Compare with Existing Records;
    :Identify New Records;
    :Identify Updated Records;
    :Identify Deleted Records;
}

partition "Database Updates" {
    :Begin Transaction;
    
    repeat
        if (Record Type?) then (New)
            :Insert New Record;
        elseif (Updated) then
            :Update Existing Record;
        else (Deleted)
            :Mark as Inactive;
        endif
    repeat while (More Records?) is (yes)
    
    :Commit Transaction;
}

partition "Report Generation" {
    :Calculate Sync Statistics;
    :Generate Synchronization Report;
    note right
        Report includes:
        • Records added
        • Records updated
        • Records deleted
        • Sync duration
        • Errors encountered
    end note
    
    :Log Sync Event;
}

:Display Synchronization Report;

stop

@enduml
```

### 5.2 System Monitoring Activity Diagram

```plantuml
@startuml System Monitoring
!theme plain
skinparam backgroundColor #FFFFFF

title System Monitoring\n(Justifies Sequence Diagram 5.2)

start

:Administrator Accesses Monitoring Dashboard;

partition "Metrics Collection" {
    :Request System Metrics;
    
    partition "Performance Data Query" {
        :Query Database Performance;
        :Retrieve Query Statistics;
        :Get Connection Pool Status;
        
        :Query Application Performance;
        :Retrieve Response Times;
        :Get Memory Usage;
        :Get CPU Usage;
        
        :Query User Activity;
        :Retrieve Active Sessions;
        :Get Request Counts;
    }
}

partition "Metrics Calculation" {
    :Calculate Average Response Time;
    :Calculate Request Rate;
    :Calculate Error Rate;
    :Calculate Resource Utilization;
    :Calculate User Engagement Metrics;
    
    :Compare with Historical Data;
    :Identify Trends;
    :Detect Anomalies;
}

partition "Dashboard Rendering" {
    :Prepare Visualization Data;
    
    :Generate Performance Charts;
    note right
        Charts include:
        • Response time trends
        • Request volume
        • Error rates
        • Resource usage
        • User activity
    end note
    
    :Display Real-time Metrics;
    :Show System Health Status;
    :Highlight Alerts (if any);
}

if (Performance Issues Detected?) then (yes)
    :Generate Alert Notification;
    :Display Warning Message;
    :Suggest Remediation Actions;
endif

:Enable Auto-refresh;

stop

@enduml
```

---

## Summary

These activity diagrams provide detailed workflow representations that justify and expand upon the sequence diagrams in `sequence.md`. Each activity diagram:

1. **Corresponds to a specific sequence diagram** - Maintaining traceability
2. **Shows decision points and alternative flows** - Providing more detail than sequence diagrams
3. **Includes validation and error handling** - Demonstrating robust system design
4. **Highlights key business logic** - Making the system behavior clear
5. **Documents data flows and transformations** - Showing how information moves through the system

The activity diagrams complement the sequence diagrams by:
- Showing the **control flow** within each process
- Illustrating **conditional logic** and branching
- Depicting **iterative processes** and loops
- Highlighting **parallel activities** where applicable
- Providing **implementation-level details** for developers

These diagrams serve as comprehensive documentation for the University Thesis Management System, supporting both system understanding and implementation efforts.
