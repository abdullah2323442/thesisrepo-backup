# Activity Diagrams - University Thesis Management System (PlantUML)
## IEEE Standard UML 2.5 Compliant Activity Diagrams

### Document Information
- **System**: University Thesis Management System
- **Standard**: IEEE 1016-2009 (Software Design Descriptions)
- **Notation**: UML 2.5 Activity Diagrams
- **Tool**: PlantUML
- **Version**: 2.0
- **Last Updated**: January 2025

### Diagram Conventions
- **Swimlanes**: Actor/Component responsibilities
- **Fork/Join Bars**: Parallel activities (IEEE 1471-2000)
- **Decision Nodes**: Conditional branching
- **Merge Nodes**: Path convergence
- **Activity Partitions**: Logical grouping
- **Guard Conditions**: [condition] notation

---

## 1. System Authentication Flow (IEEE 1471-2000 Compliant)

```plantuml
@startuml
!theme plain
skinparam backgroundColor #FEFEFE
skinparam activity {
  BackgroundColor #E8F5E9
  BorderColor #4CAF50
  FontColor #1B5E20
}
title <b>User Authentication Activity Diagram</b>\n<i>IEEE Standard Compliant</i>

|#E3F2FD|User|
start
:Access System URL;
:Enter Credentials\n(Username/ID & Password);
note right: Students use numeric ID\nFaculty use alphanumeric ID

|#F3E5F5|Authentication Controller|
:Receive Authentication Request;
:Validate Input Format;

if (Input Valid?) then (yes)
  :Identify User Type by ID Pattern;
  
  |#FFF3E0|External API Service|
  if (User Type?) then (Student)
    :Call StudentApiService;
    :Authenticate via Student API\n(config/external_api.php);
    :Parse Student Data;
  else (Faculty)
    :Call SupervisorApiService;
    :Authenticate via Faculty API\n(config/external_api.php);
    :Parse TypeId Array;
    :Determine Role (Supervisor/Advisor);
  endif
  
  if (API Authentication Success?) then (yes)
    |#E8F5E9|User Model|
    fork
      :Create/Update User Record;
      :Set user_type Field;
    fork again
      :Generate Session Token;
      :Set session_id;
    fork again
      :Log Authentication Event;
      :Record IP & Timestamp;
    end fork
    
    |#FFF9C4|Database Layer|
    :BEGIN TRANSACTION;
    :Store User Data in users table;
    :Update last_login timestamp;
    :Set is_active = true;
    :COMMIT TRANSACTION;
    
    |#E3F2FD|User|
    :Redirect to Role Dashboard;
    note right: Student → Student Dashboard\nSupervisor → Supervisor Dashboard\nAdvisor → Advisor Dashboard
  else (no)
    |#FFEBEE|Error Handler|
    :Log Failed Attempt;
    :Increment Failure Counter;
    
    |#E3F2FD|User|
    :Display Error Message;
    if (Attempts > 3?) then (yes)
      :Lock Account Temporarily;
      #pink:Terminate Session;
      kill
    else (no)
      :Return to Login;
      detach
    endif
  endif
else (no)
  |#E3F2FD|User|
  :Display Validation Error;
  :Return to Login Form;
  detach
endif

stop

@enduml
```

---

## 2. Report Submission Workflow (IEEE 830-1998 Compliant)

```plantuml
@startuml
!theme plain
skinparam backgroundColor #FEFEFE
title <b>Student Report Submission Activity Diagram</b>\n<i>IEEE 830-1998 Requirements Specification</i>

|#E3F2FD|Student|
start
:Login to System;
:Navigate to Dashboard;
:View Assigned Reports\n(from reports table);

|#F3E5F5|Report Controller|
:Fetch Active Reports;
:Check Submission Status;

if (Unsubmitted Reports Exist?) then (yes)
  |#E3F2FD|Student|
  :Select Report to Submit;
  :Click "Upload Document";
  :Select PDF File from Computer;
  note right: Max file size: 10MB\nFormat: PDF only
  
  |#F3E5F5|Validation Service|
  fork
    :Validate File Extension;
    if (Extension == .pdf?) then (yes)
      :✓ Format Valid;
    else (no)
      #pink:✗ Invalid Format;
    endif
  fork again
    :Check File Size;
    if (Size <= 10MB?) then (yes)
      :✓ Size Valid;
    else (no)
      #pink:✗ File Too Large;
    endif
  fork again
    :Scan File Content;
    if (Valid PDF Structure?) then (yes)
      :✓ Structure Valid;
    else (no)
      #pink:✗ Corrupted File;
    endif
  fork again
    :Check Mime Type;
    if (application/pdf?) then (yes)
      :✓ Mime Valid;
    else (no)
      #pink:✗ Wrong Mime Type;
    endif
  end fork
  
  if (All Validations Pass?) then (yes)
    |#E8F5E9|StudentReportSubmission Model|
    fork
      :Store File in storage/app/reports;
      :Generate Unique Filename;
    fork again
      :Create Submission Record;
      :Set submitted_at timestamp;
    fork again
      :Update Report Status;
      :Set status = 'submitted';
    end fork
    
    |#FFF9C4|Database Transaction|
    :BEGIN TRANSACTION;
    :INSERT INTO student_report_submissions;
    :UPDATE reports SET status = 'submitted';
    :COMMIT TRANSACTION;
    
    |#FFE0B2|Notification Service|
    fork
      :Create NewReportSubmission Notification;
      :Queue for Supervisor;
    fork again
      :Send Email to Supervisor;
      :Include Submission Details;
    fork again
      :Update Dashboard Counter;
      :Increment Unread Count;
    end fork
    
    |#E3F2FD|Student|
    :View Success Message;
    :Return to Dashboard;
  else (no)
    |#FFEBEE|Error Handler|
    :Compile Validation Errors;
    :Generate Error Report;
    
    |#E3F2FD|Student|
    :View Error Details;
    :Fix Issues and Retry;
    detach
  endif
else (no)
  |#E3F2FD|Student|
  :No Reports to Submit;
  :Wait for New Assignments;
endif

stop

@enduml
```

---

## 3. Supervisor Assignment Algorithm (IEEE 12207 Process Compliant)

```plantuml
@startuml
!theme plain
skinparam backgroundColor #FEFEFE
title <b>Supervisor Assignment Lottery System</b>\n<i>IEEE 12207 Software Life Cycle Process</i>

|#E3F2FD|Advisor|
start
:Access Assignment Module;
:Select Unassigned Groups;
:Choose Assignment Strategy;

|#F3E5F5|SupervisorAssignmentService|
split
  -[#2196F3]-> <b>AOI Mode</b>;
  :buildAOIPoolsWithRandomization();
  :Retrieve Groups with Areas;
  :Load Supervisors with Expertise;
  
  partition "AOI Matching Process" {
    while (Unassigned Groups Remain?) is (yes)
      :Get Group's Area IDs\n(Primary & Secondary);
      :Find Matching Supervisors;
      
      if (Matches Found?) then (yes)
        :Filter by Available Slots;
        :Apply Random Selection;
        :Exclude Last Assigned;
        :Assign to Group;
        :Record in AssignmentHistory;
      else (no)
        :Mark as No Match;
        :Try Secondary Areas;
      endif
    endwhile (no)
  }
  :Return AOI Results;
  
split again
  -[#4CAF50]-> <b>Ranking Mode</b>;
  :Load All Active Supervisors;
  :Sort by rank_priority;
  note right: 1=Professor, 2=Associate\n3=Assistant, 4=Lecturer
  
  partition "Round-Robin Process" {
    :Initialize Round Counter = 0;
    :Set Supervisor Index = 0;
    
    while (Groups to Assign?) is (yes)
      :Select Next Supervisor;
      
      if (Supervisor Assignment Count <= Round?) then (yes)
        if (Has Available Slots?) then (yes)
          :Assign Supervisor;
          :Increment Assignment Count;
        else (no)
          :Skip to Next;
        endif
      else (no)
        :Move to Next Supervisor;
      endif
      
      if (Completed Full Rotation?) then (yes)
        :Increment Round Counter;
        note right: Ensures fair distribution\nEveryone gets 1 before anyone gets 2
      else (no)
        :Continue Rotation;
      endif
    endwhile (no)
  }
  :Return Ranking Results;
  
split again
  -[#FF9800]-> <b>Hybrid Mode</b>;
  :buildAOIPoolsWithoutRandomization();
  :Initialize Global Counters;
  
  partition "Intelligent Assignment" {
    while (Groups Remain?) is (yes)
      fork
        :Find AOI Matches;
        :Get Matching Supervisors;
      fork again
        :Check Workload Balance;
        :Calculate Min Assignments;
      fork again
        :Apply Rank Priority;
        :Sort by Designation;
      end fork
      
      :selectSupervisorWithIntelligentRoundRobin();
      
      if (Multiple Candidates?) then (yes)
        :Select Lowest Load;
        if (Same Load?) then (yes)
          :Apply Rank Priority;
        else (no)
          :Use Load Balance;
        endif
      else (no)
        :Assign Available;
      endif
      
      :Update Global Counters;
      :Record Assignment;
    endwhile (no)
  }
  :Return Hybrid Results;
  
end split

|#FFF9C4|Database Operations|
:BEGIN TRANSACTION;
fork
  :UPDATE groups SET supervisor_id;
  :Set assignment timestamps;
fork again
  :INSERT INTO assignment_history;
  :Record assignment method;
fork again
  :UPDATE supervisor statistics;
  :Increment assigned_count;
end fork
:COMMIT TRANSACTION;

|#FFE0B2|Report Generation|
:Generate Assignment Report;
:Calculate Statistics;
:Create PDF Summary;

|#E3F2FD|Advisor|
:View Assignment Results;
:Download Report;

stop

@enduml
```

---

## 4. Report Lifecycle Management (IEEE 1074 Standard)

```plantuml
@startuml
!theme plain
skinparam backgroundColor #FEFEFE
title <b>Report Lifecycle State Transitions</b>\n<i>IEEE 1074 Software Life Cycle Process</i>

start

partition "Initialization Phase" #E8F5E9 {
  |#F3E5F5|Supervisor|
  :Create New Report;
  :Set Title & Description;
  :Set Deadline;
  
  |#E8F5E9|Report Model|
  :Initialize Report Record;
  :Set status = 'draft';
  :Set created_by = supervisor_id;
  
  |#FFE0B2|Notification System|
  :Create NewReportAssigned;
  :Queue for Students;
}

partition "Submission Phase" #E3F2FD {
  |#E3F2FD|Student|
  if (Before Deadline?) then (yes)
    :Upload PDF Document;
    :Submit Report;
    
    |#E8F5E9|System|
    :Validate Submission;
    :Store Document;
    :Set status = 'submitted';
    :Create Notification;
  else (no)
    if (Grace Period?) then (yes)
      :Late Submission;
      :Set status = 'late';
    else (no)
      :Set status = 'overdue';
      #pink:Mark as Failed;
      kill
    endif
  endif
}

partition "Review Phase" #FFF3E0 {
  |#F3E5F5|Supervisor|
  :Access Submitted Report;
  :Review Document;
  
  fork
    :Add PDF Annotations;
    :Store in ReportAnnotationSession;
  fork again
    :Add Text Comments;
    :Store in ReportComment;
  fork again
    :Evaluate Quality;
    :Determine Decision;
  end fork
  
  if (Decision?) then (Approve)
    :Set status = 'approved';
    
    if (Panel Review Required?) then (yes)
      partition "Panel Review" #FFE0B2 {
        :Set status = 'panel_review';
        
        |#FFE0B2|Panel Members|
        :Review Report;
        :Add Comments;
        :Vote on Decision;
        
        if (Majority Approval?) then (yes)
          :Set status = 'panel_approved';
        else (no)
          :Set status = 'revision_needed';
          :Add Revision Comments;
          |#E3F2FD|Student|
          :Receive Feedback;
          :Make Revisions;
          detach
        endif
      }
    else (no)
      :Continue to Completion;
    endif
    
  elseif (Decision?) then (Reject)
    :Set status = 'rejected';
    :Add Rejection Reason;
    |#E3F2FD|Student|
    :View Rejection;
    :Prepare New Submission;
    detach
    
  else (Needs Revision)
    :Set status = 'revision_needed';
    :Add Revision Comments;
    |#E3F2FD|Student|
    :View Feedback;
    :Make Changes;
    :Resubmit;
    detach
  endif
}

partition "Completion Phase" #C8E6C9 {
  |#E8F5E9|System|
  :Set status = 'complete';
  
  fork
    :Archive Report;
    :Move to Completed;
  fork again
    :Update Statistics;
    :Calculate Metrics;
  fork again
    :Generate Certificate;
    :Create PDF;
  fork again
    :Update Transcript;
    :Add to Records;
  end fork
  
  |#FFE0B2|Notification|
  :Notify All Parties;
  :Send Completion Email;
}

stop

@enduml
```

---

## 5. Meeting Management System (IEEE 1012 V&V Standard)

```plantuml
@startuml
!theme plain
skinparam backgroundColor #FEFEFE
title <b>Meeting Management Activity Diagram</b>\n<i>IEEE 1012 Verification & Validation</i>

|#F3E5F5|Supervisor|
start
:Access Meeting Module;
:Select Student Group;

|#E8F5E9|Meeting Controller|
:Load Group Information;
:Fetch Student List from GroupStudent;
:Initialize Meeting Form;

|#F3E5F5|Supervisor|
partition "Meeting Data Entry" #E3F2FD {
  fork
    :Enter Meeting Date;
    :Select from Calendar;
  fork again
    :Enter Meeting Time;
    :Set Duration;
  fork again
    :Enter Meeting Venue;
    :Physical/Online;
  fork again
    :Enter Topics Discussed;
    :Add Bullet Points;
  fork again
    :Enter Outcomes;
    :List Decisions;
  fork again
    :Enter Action Items;
    :Set Deadlines;
  end fork
}

partition "Attendance Recording" #FFF3E0 {
  |#E8F5E9|System|
  :Display Student Roster;
  
  while (More Students in Group?) is (yes)
    :Show Student Name & ID;
    
    |#F3E5F5|Supervisor|
    if (Student Attendance?) then (Present)
      :Mark as Present;
      :Add Participation Note;
    elseif (Attendance?) then (Absent)
      :Mark as Absent;
      :Add Reason if Known;
    else (Excused)
      :Mark as Excused;
      :Add Documentation;
    endif
    
    |#E8F5E9|System|
    :Store in MeetingAttendance;
  endwhile (no)
}

|#F3E5F5|Supervisor|
:Review Meeting Details;
:Submit Meeting Record;

|#E8F5E9|Meeting Model|
fork
  :Validate Meeting Data;
  :Check Required Fields;
fork again
  :Calculate Statistics;
  :Update Attendance Rate;
fork again
  :Check Conflicts;
  :Verify No Overlaps;
end fork

if (Validation Success?) then (yes)
  |#FFF9C4|Database Transaction|
  :BEGIN TRANSACTION;
  :INSERT INTO meetings;
  :INSERT INTO meeting_attendances;
  :UPDATE group statistics;
  :UPDATE student attendance_rate;
  :COMMIT TRANSACTION;
  
  |#FFE0B2|Report Generator|
  if (Generate PDF Report?) then (yes)
    fork
      :Create PDF Document;
      :Add Meeting Details;
    fork again
      :Include Attendance List;
      :Add Statistics;
    fork again
      :Add Action Items;
      :Include Deadlines;
    end fork
    
    |#F3E5F5|Supervisor|
    :Download PDF Report;
    :Store for Records;
  else (no)
    :View Confirmation;
  endif
  
  |#FFE0B2|Notification Service|
  :Notify Absent Students;
  :Send Meeting Minutes;
  
else (no)
  |#FFEBEE|Error Handler|
  :Display Validation Errors;
  |#F3E5F5|Supervisor|
  :Correct Errors;
  :Resubmit;
  detach
endif

stop

@enduml
```

---

## 6. Notification System Architecture (IEEE 2675-2021)

```plantuml
@startuml
!theme plain
skinparam backgroundColor #FEFEFE
title <b>Real-time Notification System</b>\n<i>IEEE 2675-2021 DevOps Standard</i>

start

partition "Event Detection Layer" #E8F5E9 {
  :System Event Triggered;
  
  split
    -[#2196F3]-> Report Events;
    :Report Created;
    :Report Submitted;
    :Report Reviewed;
  split again
    -[#4CAF50]-> Annotation Events;
    :Annotation Added;
    :Annotation Updated;
    :Annotation Deleted;
  split again
    -[#FF9800]-> Comment Events;
    :Comment Posted;
    :Comment Replied;
    :Comment Resolved;
  split again
    -[#9C27B0]-> Meeting Events;
    :Meeting Scheduled;
    :Meeting Updated;
    :Meeting Cancelled;
  split again
    -[#F44336]-> Status Events;
    :Status Changed;
    :Deadline Approaching;
    :Deadline Passed;
  end split
}

partition "Notification Processing" #F3E5F5 {
  |#E8F5E9|Notification Factory|
  fork
    :Identify Recipients;
    :Query User Roles;
    :Check Preferences;
  fork again
    :Prepare Content;
    :Load Templates;
    :Inject Variables;
  fork again
    :Set Priority Level;
    if (Urgent?) then (yes)
      :Priority = HIGH;
    elseif (Important?) then (yes)
      :Priority = MEDIUM;
    else (no)
      :Priority = LOW;
    endif
  fork again
    :Calculate Expiry;
    :Set TTL Value;
    :Add Timestamp;
  end fork
  
  :Create Notification Object;
  :Implement Notifiable Interface;
}

partition "Storage & Delivery" #FFF3E0 {
  |#FFF9C4|Database Layer|
  :BEGIN TRANSACTION;
  :INSERT INTO notifications;
  :Update user notification_count;
  :Set unread_flag = true;
  :COMMIT TRANSACTION;
  
  |#FFE0B2|Delivery Service|
  while (For Each Recipient) is (more)
    fork
      :Check User Status;
      if (User Online?) then (yes)
        fork
          :Push to Dashboard;
          :WebSocket Broadcast;
        fork again
          :Update Badge Count;
          :Increment Counter;
        fork again
          :Trigger Alert;
          if (Sound Enabled?) then (yes)
            :Play Notification Sound;
          else (no)
            :Silent Update;
          endif
        end fork
      else (offline)
        :Queue for Later;
        :Set pending_delivery;
        :Schedule Retry;
      endif
    fork again
      :Check Email Preference;
      if (Email Enabled?) then (yes)
        :Queue Email Job;
        :Send via Mail Service;
      else (no)
        :Skip Email;
      endif
    end fork
  endwhile (done)
}

partition "User Interaction" #E3F2FD {
  |#E3F2FD|User|
  :Receive Notification;
  
  if (Click Notification?) then (yes)
    |#E8F5E9|System|
    fork
      :Mark as Read;
      :Set read_at timestamp;
    fork again
      :Update Counter;
      :Decrement unread_count;
    fork again
      :Log Interaction;
      :Track Engagement;
    fork again
      :Navigate to Content;
      :Load Related Resource;
    end fork
  else (no)
    :Keep as Unread;
    if (Auto-dismiss Time?) then (yes)
      :Mark as Expired;
      :Archive Notification;
    else (no)
      :Remain in Queue;
    endif
  endif
}

stop

@enduml
```

---

## 7. Data Synchronization Process (IEEE 15288 Systems Engineering)

```plantuml
@startuml
!theme plain
skinparam backgroundColor #FEFEFE
title <b>External API Data Synchronization</b>\n<i>IEEE 15288 Systems Engineering</i>

|#E3F2FD|Administrator|
start
:Access Sync Module;
:Select Sync Type;

|#F3E5F5|Sync Controller|
:Initialize Services;
:Load API Configurations;

fork
  -[#2196F3]-> <b>Supervisor Sync</b>;
  |#E8F5E9|SupervisorApiService|
  :Read config/external_api.php;
  :Build API Request;
  :Add Authentication Headers;
  :Call External API;
  
  if (API Response OK?) then (yes)
    :Parse JSON Response;
    :Extract Supervisor Array;
    
    partition "Supervisor Processing" {
      while (For Each Supervisor) is (more)
        :Extract Supervisor Data;
        :Map API Fields to Model;
        
        if (Exists in Database?) then (yes)
          if (Data Changed?) then (yes)
            :Update Supervisor Record;
            :Log Changes;
          else (no)
            :Skip Unchanged;
          endif
        else (no)
          :Create New Supervisor;
          :Set Initial Values;
        endif
        
        :Process Areas of Interest;
        :Sync supervisor_area_of_interest;
      endwhile (done)
    }
  else (no)
    #pink:Log API Error;
    :Retry with Backoff;
  endif
  
fork again
  -[#4CAF50]-> <b>Student Sync</b>;
  |#E8F5E9|StudentApiService|
  :Read config/external_api.php;
  :Build Batch Request;
  :Add Batch Parameter;
  :Call Student API;
  
  if (API Response OK?) then (yes)
    :Parse JSON Response;
    :Extract Student Array;
    
    partition "Student Processing" {
      while (For Each Student) is (more)
        :Extract Student Data;
        :Validate Student ID;
        
        if (In Current Batch?) then (yes)
          if (Exists in DB?) then (yes)
            :Update Student Record;
            :Sync Group Assignment;
          else (no)
            :Create Student User;
            :Generate Credentials;
          endif
        else (no)
          :Skip Other Batch;
        endif
      endwhile (done)
    }
  else (no)
    #pink:Log API Error;
    :Queue for Retry;
  endif
  
fork again
  -[#FF9800]-> <b>Batch Sync</b>;
  |#E8F5E9|BatchApiService|
  :Call Batch List API;
  :Retrieve Active Batches;
  
  if (API Response OK?) then (yes)
    :Parse Batch Data;
    
    partition "Batch Processing" {
      while (For Each Batch) is (more)
        :Extract Batch Info;
        
        if (Batch Exists?) then (yes)
          :Update Batch Status;
          if (Status Changed?) then (yes)
            :Trigger Cascade Updates;
            :Update Related Groups;
          else (no)
            :No Action Needed;
          endif
        else (no)
          :Create New Batch;
          :Set Default Settings;
        endif
      endwhile (done)
    }
  else (no)
    #pink:Log Batch API Error;
  endif
  
end fork

|#FFF9C4|Database Operations|
:BEGIN TRANSACTION;
:Execute Bulk Updates;
:Verify Data Integrity;
:Update Sync Timestamps;
:COMMIT TRANSACTION;

|#FFE0B2|Sync Report|
fork
  :Calculate Statistics;
  :Count Added/Updated/Deleted;
fork again
  :Generate Sync Log;
  :Record All Changes;
fork again
  :Check Data Consistency;
  :Validate Relationships;
end fork

|#E3F2FD|Administrator|
:View Sync Summary;
:Download Detailed Report;

if (Errors Occurred?) then (yes)
  :Review Error Log;
  :Take Corrective Action;
else (no)
  :Sync Complete;
endif

stop

@enduml
```

---

## 8. Group Formation Workflow (IEEE 29148 Requirements Engineering)

```plantuml
@startuml
!theme plain
skinparam backgroundColor #FEFEFE
title <b>Student Group Formation Process</b>\n<i>IEEE 29148 Requirements Engineering</i>

|#E3F2FD|Advisor|
start
:Access Group Management;
:Select Formation Method;

split
  -[#2196F3]-> <b>Manual Formation</b>;
  |#E8F5E9|System|
  :Fetch Batch Students via API;
  :Display Student List;
  
  |#E3F2FD|Advisor|
  :Specify Number of Groups;
  :Set Group Size (3-4 students);
  
  |#E8F5E9|System|
  :Create Empty Groups;
  :Generate Group Names;
  
  |#E3F2FD|Advisor|
  while (Students Unassigned?) is (yes)
    :Select Student;
    :Choose Target Group;
    :Assign to Group;
    
    |#E8F5E9|System|
    if (Group Full?) then (yes)
      :Disable Group;
      :Mark as Complete;
    else (no)
      :Update Count;
    endif
  endwhile (no)
  
split again
  -[#4CAF50]-> <b>Excel Import</b>;
  |#E3F2FD|Advisor|
  :Upload Excel File;
  
  |#E8F5E9|GroupAssignmentImport|
  :Validate File Format;
  :Check Required Columns;
  
  if (Valid Format?) then (yes)
    :Parse Excel Data;
    :Extract Mappings;
    
    fork
      :Validate Student IDs;
      :Check Against API;
    fork again
      :Validate Group Names;
      :Check Uniqueness;
    fork again
      :Check Group Sizes;
      :Verify 3-4 Range;
    end fork
    
    if (All Valid?) then (yes)
      :Clear Existing Assignments;
      :Create Missing Groups;
      :Bulk Insert Assignments;
      :Generate Import Report;
    else (no)
      :Return Validation Errors;
      :Highlight Problem Rows;
      detach
    endif
  else (no)
    :Show Format Error;
    detach
  endif
  
split again
  -[#FF9800]-> <b>Template Export</b>;
  |#E8F5E9|GroupTemplateExport|
  :Fetch Current Students;
  :Get Existing Groups;
  :Generate Excel Template;
  
  note right
    Template includes:
    • Student ID column
    • Student Name column
    • Empty Group column
    • Instructions sheet
  end note
  
  |#E3F2FD|Advisor|
  :Download Template;
  :Fill Offline;
  :Re-import Later;
  
end split

|#FFF9C4|Database|
:Store Group Structures;
:Update group_students table;
:Set Formation Timestamp;

|#E3F2FD|Advisor|
:View Formation Summary;
:Proceed to Supervisor Assignment;

stop

@enduml
```

---

## 9. Document Annotation Workflow (IEEE 26515 Documentation Standard)

```plantuml
@startuml
!theme plain
skinparam backgroundColor #FEFEFE
title <b>PDF Annotation System Workflow</b>\n<i>IEEE 26515 User Documentation</i>

|#F3E5F5|Supervisor/Panel Member|
start
:Access Submitted Report;
:Click "Add Annotations";

|#E8F5E9|Annotation Controller|
:Verify Access Rights;
:Load PDF Document;
:Initialize Annotation Session;

|#F3E5F5|User|
partition "Annotation Process" #E3F2FD {
  :View PDF in Browser;
  
  while (Adding Annotations?) is (yes)
    :Select Text/Area;
    :Choose Annotation Type;
    
    split
      :Highlight Text;
      :Select Color;
    split again
      :Add Comment;
      :Type Feedback;
    split again
      :Draw Shape;
      :Circle/Rectangle;
    split again
      :Add Sticky Note;
      :Position on Page;
    end split
    
    |#E8F5E9|System|
    :Store Annotation Locally;
    :Update Preview;
  endwhile (no)
}

|#F3E5F5|User|
:Review All Annotations;
:Click "Save Annotations";

|#E8F5E9|ReportAnnotationSession Model|
fork
  :Create Session Record;
  :Set created_by_type;
  :Store Session ID;
fork again
  :Serialize Annotations;
  :Convert to JSON;
  :Compress Data;
fork again
  :Generate Version;
  :Track Changes;
  :Create Diff;
end fork

|#FFF9C4|Database|
:BEGIN TRANSACTION;
:INSERT INTO report_annotation_sessions;
:Store annotation_data JSON;
:Update report modified_at;
:COMMIT TRANSACTION;

|#FFE0B2|Notification Service|
fork
  :Create NewReportAnnotation;
  :Queue for Students;
fork again
  :Update Activity Log;
  :Record Annotation Event;
end fork

partition "Student Access" #FFF3E0 {
  |#E3F2FD|Student|
  :Receive Notification;
  :Open Annotated Report;
  
  |#E8F5E9|System|
  :Load Latest Annotations;
  :Merge All Sessions;
  :Render Annotated PDF;
  
  |#E3F2FD|Student|
  :View Feedback;
  :Read Comments;
  
  if (Download PDF?) then (yes)
    |#E8F5E9|System|
    :Generate PDF with Annotations;
    :Include All Feedback;
    :Add Watermark;
    
    |#E3F2FD|Student|
    :Save to Computer;
  else (no)
    :Continue Viewing;
  endif
}

stop

@enduml
```

---

## 10. Performance Monitoring (IEEE 1061 Software Quality Metrics)

```plantuml
@startuml
!theme plain
skinparam backgroundColor #FEFEFE
title <b>System Performance Monitoring</b>\n<i>IEEE 1061 Software Quality Metrics</i>

start

partition "Monitoring Service" #E8F5E9 {
  |#E8F5E9|PerformanceMonitoringService|
  :Initialize Monitoring;
  :Set Sampling Interval;
  
  fork
    -[#2196F3]-> Response Time;
    while (Monitoring Active?) is (yes)
      :Measure Request Time;
      :Calculate Average;
      :Check Threshold;
      
      if (Time > 2s?) then (yes)
        #pink:Trigger Alert;
        :Log Slow Query;
      else (no)
        :Record Metric;
      endif
    endwhile (stop)
    
  fork again
    -[#4CAF50]-> Memory Usage;
    while (Monitoring Active?) is (yes)
      :Check Memory Usage;
      :Calculate Percentage;
      
      if (Usage > 80%?) then (yes)
        #pink:Memory Warning;
        :Trigger Cleanup;
      else (no)
        :Log Usage;
      endif
    endwhile (stop)
    
  fork again
    -[#FF9800]-> Database Performance;
    while (Monitoring Active?) is (yes)
      :Monitor Query Time;
      :Check Connection Pool;
      :Analyze Slow Queries;
      
      if (Issues Detected?) then (yes)
        :Optimize Queries;
        :Clear Cache;
      else (no)
        :Continue Monitoring;
      endif
    endwhile (stop)
    
  fork again
    -[#9C27B0]-> User Activity;
    while (Monitoring Active?) is (yes)
      :Track Active Users;
      :Monitor Sessions;
      :Log Actions;
      
      :Update Dashboard;
      :Generate Metrics;
    endwhile (stop)
    
  end fork
}

partition "Reporting" #FFF3E0 {
  |#FFE0B2|Report Generator|
  :Aggregate Metrics;
  :Calculate Statistics;
  :Generate Reports;
  
  fork
    :Daily Report;
    :Email to Admin;
  fork again
    :Weekly Summary;
    :Store in Database;
  fork again
    :Monthly Analysis;
    :Create PDF Report;
  end fork
}

stop

@enduml
```

---

## Rendering Instructions

### Online Tools
1. **PlantUML Web Server**: https://www.plantuml.com/plantuml/uml/
2. **PlantText**: https://www.planttext.com/
3. **PlantUML Editor**: https://plantuml-editor.kkeisuke.com/

### IDE Integration
- **VS Code**: Install "PlantUML" extension by jebbs
- **IntelliJ IDEA**: PlantUML Integration plugin
- **Eclipse**: PlantUML Eclipse Plugin
- **Atom**: plantuml-viewer package

### Command Line Usage
```bash
# Install PlantUML
brew install plantuml  # macOS
apt-get install plantuml  # Ubuntu/Debian
choco install plantuml  # Windows

# Generate diagrams
plantuml -tpng activity-plantuml.md  # PNG output
plantuml -tsvg activity-plantuml.md  # SVG output
plantuml -tpdf activity-plantuml.md  # PDF output
```

### Docker Usage
```bash
docker run -v $(pwd):/data plantuml/plantuml -tpng /data/activity-plantuml.md
```

---

## IEEE Standards Compliance

This document adheres to the following IEEE standards:

1. **IEEE 1016-2009**: Software Design Descriptions
2. **IEEE 1471-2000**: Architecture Descriptions
3. **IEEE 830-1998**: Software Requirements Specifications
4. **IEEE 12207**: Software Life Cycle Processes
5. **IEEE 1074**: Software Life Cycle Process
6. **IEEE 1012**: Verification and Validation
7. **IEEE 2675-2021**: DevOps Standards
8. **IEEE 15288**: Systems Engineering
9. **IEEE 29148**: Requirements Engineering
10. **IEEE 26515**: User Documentation
11. **IEEE 1061**: Software Quality Metrics

### UML 2.5 Compliance Features

- ✅ **Activity Partitions** (Swimlanes)
- ✅ **Fork/Join Nodes** for parallel activities
- ✅ **Decision/Merge Nodes** for branching
- ✅ **Initial/Final Nodes** 
- ✅ **Guard Conditions** in square brackets
- ✅ **Activity Parameters** and object flows
- ✅ **Interruptible Activity Regions**
- ✅ **Exception Handlers** (kill nodes)
- ✅ **Notes and Constraints**

---

## System Architecture Overview

The University Thesis Management System implements a Laravel-based MVC architecture with:

- **Models**: User, Supervisor, Group, Report, Meeting, Notification
- **Controllers**: Authentication, Report, Meeting, Assignment
- **Services**: API Integration, Assignment Algorithm, Performance Monitoring
- **External APIs**: Student API, Supervisor API, Batch API
- **Database**: MySQL with transaction support
- **Storage**: File system for PDF documents
- **Notifications**: Real-time dashboard updates

---

## Version History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 2.1 | Jan 2025 | System Architect | Added A4-optimized versions for IEEE reports |
| 2.0 | Jan 2025 | System Architect | Complete IEEE-compliant redesign |
| 1.0 | Dec 2024 | Development Team | Initial version |

---

## APPENDIX A: A4-OPTIMIZED VERSIONS FOR IEEE REPORTS

### Important Note for IEEE Reports
The diagrams above are comprehensive but too large for standard A4 pages. Use these compact versions for your IEEE-standard software project report.

### A.1 Compact Authentication Flow (Fits A4 Portrait)

```plantuml
@startuml
!theme plain
skinparam activityFontSize 10
skinparam defaultFontSize 10
skinparam arrowFontSize 9
skinparam noteFontSize 9
skinparam padding 2
skinparam nodesep 25
skinparam ranksep 30
scale 0.8

title <b>Figure 4.1:</b> User Authentication Activity Diagram

start
:User enters credentials;
:System identifies user type;

if (Student ID?) then (yes)
  :Call StudentApiService;
  :Authenticate via Student API;
else (no)
  :Call SupervisorApiService;
  :Authenticate via Faculty API;
endif

if (Valid?) then (yes)
  fork
    :Create session;
  fork again
    :Update database;
  fork again
    :Log activity;
  end fork
  :Redirect to dashboard;
else (no)
  :Display error;
  :Return to login;
endif

stop
@enduml
```

### A.2 Compact Report Submission (Fits A4 Portrait)

```plantuml
@startuml
!theme plain
skinparam activityFontSize 10
skinparam padding 2
scale 0.75

title <b>Figure 4.2:</b> Report Submission Process

start
:Student selects report;
:Upload PDF file;

fork
  :Validate format;
  if (PDF?) then (yes)
    :✓;
  else (no)
    :✗;
  endif
fork again
  :Check size;
  if (<10MB?) then (yes)
    :✓;
  else (no)
    :✗;
  endif
fork again
  :Scan content;
  if (Valid?) then (yes)
    :✓;
  else (no)
    :✗;
  endif
end fork

if (All valid?) then (yes)
  :Store file;
  :Update status;
  :Notify supervisor;
  :Success;
else (no)
  :Show errors;
  :Retry;
endif

stop
@enduml
```

### A.3 Supervisor Assignment Overview (Fits A4 Portrait)

```plantuml
@startuml
!theme plain
skinparam activityFontSize 10
scale 0.8

title <b>Figure 5.1:</b> Supervisor Assignment Strategies

start
:Advisor selects strategy;

split
  -[#2196F3]-> AOI Mode;
  :Match by expertise;
  :Random selection;
split again
  -[#4CAF50]-> Ranking Mode;
  :Sort by designation;
  :Round-robin assignment;
split again
  -[#FF9800]-> Hybrid Mode;
  :Balance expertise & load;
  :Optimize distribution;
end split

:Execute assignment;
:Store in database;
:Generate report;
stop
@enduml
```

### A.4 Report Lifecycle States (Fits 1/2 A4 Page)

```plantuml
@startuml
!theme plain
skinparam activityFontSize 10
scale 0.8

title <b>Figure 5.2:</b> Report State Transitions

start
:DRAFT;
→
:SUBMITTED;

if (Review) then (approve)
  :APPROVED;
  if (Panel?) then (yes)
    :PANEL_REVIEW;
    if (Pass?) then (yes)
      :PANEL_APPROVED;
    else (no)
      :REVISION_NEEDED;
      →
      :RESUBMIT;
    endif
  endif
  :COMPLETE;
elseif (Review) then (reject)
  :REJECTED;
  stop
else (revise)
  :REVISION_NEEDED;
  →
  :RESUBMIT;
endif

stop
@enduml
```

### A.5 Meeting Management (Compact Version)

```plantuml
@startuml
!theme plain
skinparam activityFontSize 10
scale 0.85

title <b>Figure 6.1:</b> Meeting Recording Process

|Supervisor|
start
:Select group;
:Enter details;

fork
  :Date/Time;
fork again
  :Topics;
fork again
  :Attendance;
end fork

:Submit;

|System|
:Validate;
:Store;
:Generate PDF;

stop
@enduml
```

### A.6 Notification Flow (Compact)

```plantuml
@startuml
!theme plain
skinparam activityFontSize 10
scale 0.8

title <b>Figure 6.2:</b> Notification System

start
:Event occurs;

fork
  :Identify recipients;
fork again
  :Prepare content;
fork again
  :Set priority;
end fork

:Store notification;

if (User online?) then (yes)
  :Push to dashboard;
  :Update badge;
else (no)
  :Queue for later;
endif

stop
@enduml
```

---

## APPENDIX B: INTEGRATION GUIDE FOR IEEE REPORTS

### B.1 LaTeX Integration

For IEEE conference/journal papers using LaTeX:

```latex
\documentclass[conference]{IEEEtran}
\usepackage{graphicx}
\usepackage{subcaption}

% Single diagram
\begin{figure}[htbp]
\centering
\includegraphics[width=0.48\textwidth]{diagrams/auth-compact.pdf}
\caption{User Authentication Activity Diagram}
\label{fig:auth}
\end{figure}

% Two diagrams side by side
\begin{figure}[htbp]
\centering
\begin{subfigure}[b]{0.48\textwidth}
    \includegraphics[width=\textwidth]{diagrams/auth.pdf}
    \caption{Authentication}
\end{subfigure}
\hfill
\begin{subfigure}[b]{0.48\textwidth}
    \includegraphics[width=\textwidth]{diagrams/submission.pdf}
    \caption{Report Submission}
\end{subfigure}
\caption{System Core Processes}
\label{fig:core-processes}
\end{figure}
```

### B.2 MS Word Integration

For IEEE Word templates:

1. **Generate high-DPI images**:
```bash
plantuml -tpng -dpi 300 -scale 0.8 diagram.puml
```

2. **Insert in Word**:
   - Single column: Width = 3.5 inches
   - Double column span: Width = 7 inches
   - Caption: Use IEEE style "Fig. X. Description"

### B.3 Generation Script for A4 Reports

Create `generate-a4-diagrams.bat`:

```batch
@echo off
echo Generating A4-optimized diagrams for IEEE report...

set PLANTUML=plantuml.jar

REM Generate compact versions at 300 DPI
java -jar %PLANTUML% -tpdf -dpi 300 -scale 0.8 auth-compact.puml
java -jar %PLANTUML% -tpdf -dpi 300 -scale 0.75 submission-compact.puml
java -jar %PLANTUML% -tpdf -dpi 300 -scale 0.8 assignment-compact.puml
java -jar %PLANTUML% -tpdf -dpi 300 -scale 0.85 meeting-compact.puml

echo Diagrams generated successfully!
pause
```

### B.4 Recommended Layout for IEEE Report

#### Chapter Structure with Diagrams:

**Chapter 4: System Design**
- Fig 4.1: Authentication (Compact) - 1/2 page
- Fig 4.2: Report Submission (Compact) - 1/2 page

**Chapter 5: Implementation**
- Fig 5.1: Assignment Strategies - 1/2 page
- Fig 5.2: Report States - 1/3 page
- Fig 5.3: AOI Algorithm (detailed) - Full page landscape

**Chapter 6: System Features**
- Fig 6.1: Meeting Management - 1/3 page
- Fig 6.2: Notification System - 1/3 page
- Fig 6.3: Data Sync - 1/3 page

### B.5 PlantUML Settings for A4 Optimization

```plantuml
@startuml
' Standard A4 Portrait Settings
!define A4_SCALE 0.8
!define A4_FONT 10
!define A4_PADDING 2

skinparam dpi 300
skinparam backgroundColor white
skinparam defaultFontSize A4_FONT
skinparam activityFontSize A4_FONT
skinparam padding A4_PADDING
skinparam nodesep 25
skinparam ranksep 30

scale A4_SCALE

' Your diagram here
@enduml
```

### B.6 Tips for A4 Page Fitting

1. **Use scale factor**: 0.7-0.9 for most diagrams
2. **Reduce font size**: 10-11pt for activities
3. **Minimize padding**: Set padding to 2
4. **Use abbreviations**: Shorten long text
5. **Split complex diagrams**: Create part 1, part 2
6. **Consider landscape**: For wide diagrams
7. **Use subfigures**: Group related small diagrams

---

## APPENDIX C: QUICK REFERENCE FOR REPORT WRITING

### Diagram Selection Guide:

| Diagram Type | Full Version | Compact Version | Page Space | Use Case |
|-------------|--------------|-----------------|------------|----------|
| Authentication | Section 1 | Appendix A.1 | 1/2 page | System overview |
| Report Submission | Section 2 | Appendix A.2 | 1/2 page | Core functionality |
| Assignment Algorithm | Section 3 | Appendix A.3 | 1/2 page | Algorithm overview |
| Report Lifecycle | Section 4 | Appendix A.4 | 1/3 page | State transitions |
| Meeting Management | Section 5 | Appendix A.5 | 1/3 page | Feature detail |
| Notifications | Section 6 | Appendix A.6 | 1/3 page | System feature |

### Export Commands:

```bash
# For IEEE LaTeX papers (vector graphics)
plantuml -tpdf -dpi 300 -scale 0.8 *.puml

# For MS Word reports (high-res raster)
plantuml -tpng -dpi 300 -scale 0.8 *.puml

# For presentations (medium-res)
plantuml -tpng -dpi 150 -scale 1.0 *.puml

# For web documentation (scalable)
plantuml -tsvg *.puml
```

---

*This document is part of the University Thesis Management System technical documentation suite.*