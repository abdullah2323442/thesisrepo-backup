# Activity Diagrams for Thesis Management System
## IEEE Software Project Report Standard - Compact Version

This document contains optimized activity diagrams suitable for A4 IEEE format reports. Each diagram focuses on core workflows while maintaining readability.

---

## 1. User Authentication and Role-Based Access Control

```plantuml
@startuml Authentication_RBAC
!theme plain
title User Authentication and Role-Based Access Control
skinparam activityFontSize 10
skinparam defaultFontSize 10
skinparam arrowFontSize 9
skinparam backgroundColor #FFFFFF

start
:User Login;
:Validate Credentials;

if (Valid?) then (yes)
  :Identify User Role;
  switch (Role)
  case (Admin)
    :Admin Dashboard;
  case (Teacher)
    :Select Sub-role|
    :Teacher/Supervisor/Co-Supervisor/Panel;
  case (Student)
    :Student Dashboard;
  case (Advisor)
    :Advisor Dashboard;
  endswitch
else (no)
  :Show Error;
  stop
endif

:Apply Role Middleware;
:Grant Permissions;
:Load User Context;
stop
@enduml
```

---

## 2. Core Report Management Workflow

```plantuml
@startuml Report_Management_Core
!theme plain
title Core Report Management Workflow
skinparam activityFontSize 10
skinparam defaultFontSize 10
skinparam backgroundColor #FFFFFF

start
partition "Report Creation" {
  :Supervisor Creates Report;
  :Set Deadline & Requirements;
  :Assign to Groups;
  :Notify Students;
}

partition "Submission Process" {
  :Student Uploads PDF (<10MB);
  :System Validates File;
  :Store in Database;
  :Update Status;
}

partition "Review & Feedback" {
  :Supervisor Reviews Submission;
  
  if (Action?) then (Annotate)
    :Add PDF Annotations;
    :Generate Feedback;
  else (Direct Decision)
    :Set Status;
  endif
  
  :Update Report Status|
  :Approved/Revision/Rejected;
  :Notify Student;
}

stop
@enduml
```

---

## 3. Group Assignment and Supervisor Allocation

```plantuml
@startuml Group_Supervisor_Assignment
!theme plain
title Group Formation and Supervisor Assignment
skinparam activityFontSize 10
skinparam defaultFontSize 10
skinparam backgroundColor #FFFFFF

start
partition "Group Formation" {
  :Advisor Fetches Students via API;
  
  fork
    :Manual Creation;
  fork again
    :Excel Import;
  end fork
  
  :Assign Area of Interest;
  :Save Groups;
}

partition "Supervisor Assignment" {
  :Select Assignment Method;
  
  if (Method?) then (Manual)
    :Select Supervisor;
    :Check Capacity;
    :Assign;
  else (Lottery)
    :Choose Algorithm|
    :AOI/Ranking/Combined;
    :Run Assignment;
    :Distribute Fairly;
  endif
  
  :Update Database;
  :Record History;
}

:Notify Stakeholders;
stop
@enduml
```

---

## 4. System Administration and Monitoring

```plantuml
@startuml Admin_System_Management
!theme plain
title System Administration and Monitoring
skinparam activityFontSize 10
skinparam defaultFontSize 10
skinparam backgroundColor #FFFFFF

start
split
  :Batch Management;
  :Sync with External API;
  :Activate/Deactivate;
split again
  :Supervisor Management;
  :Set Thesis Limits;
  :Assign Areas;
split again
  :Performance Monitoring;
  :Collect Metrics;
  :Generate Reports;
end split

:Update System Configuration;
:Log Activities;
stop
@enduml
```

---

## 5. Integrated Workflow Overview

```plantuml
@startuml System_Integrated_Workflow
!theme plain
title Thesis Management System - Integrated Workflow
skinparam activityFontSize 9
skinparam defaultFontSize 9
skinparam backgroundColor #FFFFFF

|Admin|
start
:System Setup;
:Configure Batches;
:Manage Supervisors;
:Define Areas of Interest;

|Advisor|
:Create Student Groups;
:Assign Areas;
:Run Supervisor Lottery;

|Supervisor|
:Create Reports;
:Set Requirements;
:Schedule Meetings;

|Student|
:View Assigned Reports;
:Submit Documents;
:Attend Meetings;

|Supervisor|
:Review Submissions;
:Provide Feedback;
:Approve/Reject;

|System|
:Send Notifications;
:Update Records;
:Generate Analytics;

stop
@enduml
```

---

## 6. External API Integration and Data Synchronization

```plantuml
@startuml API_Integration
!theme plain
title External API Integration Flow
skinparam activityFontSize 10
skinparam defaultFontSize 10
skinparam backgroundColor #FFFFFF

start
:Trigger Sync Request;

if (Cache Valid?) then (yes)
  :Return Cached Data;
else (no)
  :Call External API;
  
  if (Success?) then (yes)
    :Parse Response;
    :Update Database;
    :Refresh Cache;
  else (no)
    :Log Error;
    :Use Fallback;
  endif
endif

:Return Data;
stop
@enduml
```

---

## 7. Report Lifecycle Management

```plantuml
@startuml Report_Lifecycle
!theme plain
title Complete Report Lifecycle
skinparam activityFontSize 10
skinparam defaultFontSize 10
skinparam backgroundColor #FFFFFF

start
:Report Created;
:Status: Draft;
|
:Assigned to Groups;
:Status: Active;
|
:Student Submits;
:Status: Submitted;
|
:Supervisor Reviews;

if (Decision?) then (Approve)
  :Status: Approved;
  :End Process;
elseif (Revision)
  :Status: Revision Required;
  :Return to Student;
  backward:Student Resubmits;
else (Reject)
  :Status: Rejected;
  :End Process;
endif

stop
@enduml
```

---

## 8. Notification and Communication Flow

```plantuml
@startuml Notification_System
!theme plain
title Notification System
skinparam activityFontSize 10
skinparam defaultFontSize 10
skinparam backgroundColor #FFFFFF

start
:Event Triggered|
:Report/Meeting/Assignment;

fork
  :Database Notification;
  :Store & Mark Unread;
fork again
  :Email Notification;
  if (Enabled?) then (yes)
    :Queue Email;
    :Send;
  endif
fork again
  :Real-time Update;
  if (User Online?) then (yes)
    :Push Notification;
  endif
end fork

:Log Notification;
stop
@enduml
```

---

## 9. Security and Access Control

```plantuml
@startuml Security_Access
!theme plain
title Security and Access Control
skinparam activityFontSize 10
skinparam defaultFontSize 10
skinparam backgroundColor #FFFFFF

start
:Request Received;
:Check Authentication;

if (Authenticated?) then (yes)
  :Verify Authorization;
  
  if (Authorized?) then (yes)
    :Check Resource Access;
    
    if (Permitted?) then (yes)
      :Process Request;
      :Return Response;
    else (no)
      :403 Forbidden;
    endif
  else (no)
    :401 Unauthorized;
  endif
else (no)
  :Redirect to Login;
endif

stop
@enduml
```

---

## 10. Lottery Assignment Algorithm

```plantuml
@startuml Lottery_Algorithm
!theme plain
title Supervisor Lottery Assignment Algorithm
skinparam activityFontSize 10
skinparam defaultFontSize 10
skinparam backgroundColor #FFFFFF

start
:Load Unassigned Groups;
:Select Mode|
:AOI/Ranking/Combined;

partition "Assignment Process" {
  while (Groups Remaining?) is (yes)
    :Get Next Group;
    
    switch (Mode)
    case (AOI)
      :Match by Area;
      :Random Selection;
    case (Ranking)
      :Sort by Rank;
      :Round-Robin;
    case (Combined)
      :AOI + Ranking;
      :Fair Distribution;
    endswitch
    
    if (Supervisor Found?) then (yes)
      :Assign;
      :Update Capacity;
    else (no)
      :Mark Unassigned;
    endif
  endwhile (no)
}

:Generate Report;
:Show Results|
:Assigned/Unassigned/Stats;
stop
@enduml
```

---

## Appendix A: Simplified System Overview

```plantuml
@startuml System_Overview_Simple
!theme plain
title Thesis Management System Overview
skinparam activityFontSize 10
skinparam defaultFontSize 10
skinparam backgroundColor #FFFFFF
skinparam partitionBorderColor #333333

|#LightBlue|Setup Phase|
:Admin configures system;
:Advisor creates groups;
:Assign supervisors;

|#LightGreen|Execution Phase|
:Supervisors create reports;
:Students submit work;
:Reviews and feedback;

|#LightYellow|Monitoring Phase|
:Track progress;
:Generate analytics;
:Performance monitoring;

stop
@enduml
```

---

## Appendix B: Key System Components

```plantuml
@startuml System_Components
!theme plain
title System Component Interaction
skinparam activityFontSize 10
skinparam defaultFontSize 10
skinparam backgroundColor #FFFFFF

start
partition "MVC Architecture" {
  :Controllers|
  :Handle Requests;
  :Process Logic;
  -->
  :Models|
  :Data Management;
  :Database Operations;
  -->
  :Views|
  :User Interface;
  :Display Data;
}

partition "Service Layer" {
  :API Services|
  :External Integration;
  -->
  :Assignment Service|
  :Lottery Algorithm;
  -->
  :Monitoring Service|
  :Performance Tracking;
}

partition "Middleware" {
  :Authentication;
  :Authorization;
  :Role Verification;
}

stop
@enduml
```

---

## Summary

These compact activity diagrams are optimized for A4 IEEE standard reports while maintaining comprehensive coverage of the system's functionality. Each diagram:

1. **Fits on a single A4 page** when rendered
2. **Uses simplified notation** for better readability
3. **Focuses on core workflows** without excessive detail
4. **Maintains IEEE compliance** for academic documentation
5. **Groups related processes** to reduce diagram count

### Key Design Decisions:

- **Combined related workflows** (e.g., all report operations in one diagram)
- **Used swimlanes sparingly** to show role interactions without clutter
- **Simplified decision points** to essential branches only
- **Removed redundant details** while keeping critical paths
- **Optimized font sizes** for print readability (10pt standard)

### Recommended Diagrams for Report:

For your IEEE report, I recommend including these primary diagrams:
1. **Diagram 5** (Integrated Workflow) - Shows complete system flow
2. **Diagram 3** (Group Assignment) - Highlights unique lottery feature
3. **Diagram 2** (Report Management) - Core functionality
4. **Diagram 10** (Lottery Algorithm) - Technical implementation detail

These provide comprehensive coverage while remaining readable in standard academic format.