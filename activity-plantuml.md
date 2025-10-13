# Activity Diagrams - Thesis Repository Management System (PlantUML)

## Important Note
These diagrams use PlantUML syntax which generates proper UML 2.5 activity diagrams with:
- Swimlanes
- Fork/Join bars for parallel activities
- Proper start/end nodes
- Decision nodes
- Merge nodes

To render these diagrams:
1. Use PlantUML online editor: https://www.plantuml.com/plantuml/uml/
2. Or install PlantUML plugin in your IDE
3. Or use PlantUML command line tool

---

## 1. User Authentication Activity with Swimlanes

```plantuml
@startuml
!theme plain
title User Authentication Activity Diagram

|#LightBlue|User|
start
:Enter Credentials;
:Submit Login Form;

|#LightGreen|System|
:Receive Credentials;
:Check ID Format;
if (ID Type?) then (Numeric)
  :Student Authentication Path;
  :Call Student API;
else (Alphanumeric)
  :Faculty Authentication Path;
  :Call Faculty API;
  :Parse TypeId Array;
endif

if (Valid Credentials?) then (yes)
  fork
    :Create Session;
  fork again
    :Log Activity;
  fork again
    :Set Timestamp;
  end fork
  
  |#LightYellow|Database|
  :Store/Update User Record;
  :Set Active Status;
  
  |#LightBlue|User|
  :View Dashboard;
else (no)
  |#LightBlue|User|
  :View Error Message;
  :Enter Credentials;
  detach
endif

stop

@enduml
```

---

## 2. Report Submission with Parallel Validation

```plantuml
@startuml
!theme plain
title Report Submission Activity Diagram

|#LightBlue|Student|
start
:Access Dashboard;
:View Assigned Reports;

if (Report Available?) then (yes)
  :Select Report;
  :Choose PDF File;
  :Click Upload;
  
  |#LightGreen|System|
  fork
    :Validate File Format;
    if (Valid PDF?) then (yes)
      :Format OK;
    else (no)
      :Format Error;
    endif
  fork again
    :Check File Size;
    if (Size < 10MB?) then (yes)
      :Size OK;
    else (no)
      :Size Error;
    endif
  fork again
    :Scan for Viruses;
    if (Clean?) then (yes)
      :Scan OK;
    else (no)
      :Virus Detected;
    endif
  end fork
  
  if (All Validations Pass?) then (yes)
    fork
      :Store File in System;
    fork again
      :Generate Thumbnail;
    fork again
      :Extract Metadata;
    end fork
    
    |#LightYellow|Database|
    :Save Submission Record;
    :Update Report Status;
    :Create Notification;
    
    |#LightBlue|Student|
    :View Success Message;
  else (no)
    |#LightBlue|Student|
    :View Error Details;
    :Choose PDF File;
    detach
  endif
else (no)
  :Wait for Assignment;
endif

stop

@enduml
```

---

## 3. Supervisor Assignment Lottery with Three Parallel Modes

```plantuml
@startuml
!theme plain
title Supervisor Assignment Lottery Activity

|#LightBlue|Advisor|
start
:Initiate Lottery Assignment;
:Select Assignment Mode;

|#LightGreen|System|
split
  -[#blue]-> AOI Mode;
  :Get Groups with AOI;
  :Get Matching Supervisors;
  while (More Groups?) is (yes)
    :Match Areas of Interest;
    :Filter Available Supervisors;
    :Random Selection;
    :Assign Supervisor;
  endwhile (no)
  :AOI Assignment Complete;
  
split again
  -[#green]-> Ranking Mode;
  :Get All Groups;
  :Sort Supervisors by Rank;
  :Initialize Round-Robin;
  while (More Groups?) is (yes)
    :Select Next in Rotation;
    if (Has Capacity?) then (yes)
      :Assign Supervisor;
      :Update Counter;
    else (no)
      :Skip to Next;
    endif
  endwhile (no)
  :Ranking Assignment Complete;
  
split again
  -[#orange]-> Hybrid Mode;
  fork
    :Get Unassigned Groups;
  fork again
    :Get Supervisors with AOI;
  end fork
  
  while (More Groups?) is (yes)
    :Find AOI Matches;
    :Check Workload Balance;
    :Apply Ranking Priority;
    :Select Optimal Supervisor;
    :Assign Supervisor;
  endwhile (no)
  :Hybrid Assignment Complete;
  
end split

|#LightYellow|Database|
:Save All Assignments;
:Update Assignment History;
:Generate Statistics;

|#LightBlue|Advisor|
:View Assignment Results;
:Download Report;

stop

@enduml
```

---

## 4. Report Lifecycle with State Transitions

```plantuml
@startuml
!theme plain
title Report Lifecycle Activity Diagram

start

partition "Report Creation" {
  :Supervisor Creates Report;
  :Set Status = DRAFT;
  :Notify Students;
}

partition "Student Submission" {
  if (Student Submits?) then (yes)
    :Upload PDF;
    :Set Status = SUBMITTED;
    :Notify Supervisor;
  else (no)
    if (Deadline Passed?) then (yes)
      :Set Status = OVERDUE;
      #pink:End Process;
      kill
    else (no)
      :Wait for Submission;
      detach
    endif
  endif
}

partition "Supervisor Review" {
  :Supervisor Reviews;
  
  fork
    :Can Add Annotations;
  fork again
    :Can Add Comments;
  fork again
    :Can Make Decision;
  end fork
  
  if (Decision?) then (Approve)
    :Set Status = APPROVED;
    if (Panel Review Required?) then (yes)
      :Set Status = PANEL_REVIEW;
      
      partition "Panel Review" {
        :Panel Reviews Report;
        if (Panel Decision?) then (Approve)
          :Set Status = APPROVED;
        else (Reject)
          :Set Status = REVISION_NEEDED;
          :Notify Student;
          detach
        endif
      }
    else (no)
      :Continue to Complete;
    endif
  elseif (Decision?) then (Reject)
    :Set Status = REVISION_NEEDED;
    :Notify Student;
    detach
  else (Pending)
    :Continue Review;
    detach
  endif
}

partition "Completion" {
  :Set Status = COMPLETE;
  fork
    :Archive Report;
  fork again
    :Update Statistics;
  fork again
    :Generate Certificate;
  end fork
}

stop

@enduml
```

---

## 5. Meeting Management with Parallel Data Entry

```plantuml
@startuml
!theme plain
title Meeting Management Activity Diagram

|#LightBlue|Supervisor|
start
:Select Student Group;
:Click "Record Meeting";

|#LightGreen|System|
:Load Group Students;
:Initialize Meeting Form;

|#LightBlue|Supervisor|
fork
  :Enter Meeting Date;
fork again
  :Enter Topics Discussed;
fork again
  :Enter Outcomes;
fork again
  :Enter Action Items;
end fork

:Mark Attendance;

|#LightGreen|System|
while (More Students?) is (yes)
  :Display Student Name;
  |#LightBlue|Supervisor|
  if (Student Status?) then (Present)
    :Mark Present;
  elseif (Status?) then (Absent)
    :Mark Absent;
  else (Excused)
    :Mark Excused;
  endif
  |#LightGreen|System|
endwhile (no)

|#LightBlue|Supervisor|
:Submit Meeting Record;

|#LightGreen|System|
fork
  :Validate Data;
fork again
  :Calculate Statistics;
end fork

|#LightYellow|Database|
fork
  :Save Meeting Record;
fork again
  :Update Attendance Stats;
fork again
  :Update Group Progress;
end fork

|#LightBlue|Supervisor|
if (Generate PDF Report?) then (yes)
  |#LightGreen|System|
  :Generate PDF;
  |#LightBlue|Supervisor|
  :Download Report;
else (no)
  :View Confirmation;
endif

stop

@enduml
```

---

## 6. Notification System with Concurrent Processing

```plantuml
@startuml
!theme plain
title Notification System Activity Diagram

start

partition "Event Detection" {
  :System Event Occurs;
  
  split
    :Report Created;
  split again
    :Annotation Added;
  split again
    :Comment Added;
  split again
    :Meeting Scheduled;
  split again
    :Status Changed;
  end split
}

partition "Notification Creation" {
  fork
    :Identify Recipients;
    :Get User List;
  fork again
    :Prepare Content;
    :Format Message;
  fork again
    :Set Priority;
    :Determine Urgency;
  fork again
    :Set Expiry;
    :Calculate Timeout;
  end fork
}

partition "Storage & Delivery" {
  :Store in Database;
  :Update User Counters;
  
  while (For Each Recipient) is (more)
    if (User Online?) then (yes)
      fork
        :Push to Dashboard;
      fork again
        :Update Badge Count;
      fork again
        :Play Sound Alert;
      end fork
    else (no)
      :Queue for Later;
      :Set Pending Flag;
    endif
  endwhile (done)
}

partition "User Interaction" {
  if (User Views Notification?) then (yes)
    fork
      :Mark as Read;
    fork again
      :Update Counter;
    fork again
      :Log Interaction;
    end fork
  else (no)
    :Keep as Unread;
  endif
}

stop

@enduml
```

---

## 7. Data Synchronization with Parallel API Calls

```plantuml
@startuml
!theme plain
title Data Synchronization Activity Diagram

|#LightBlue|Administrator|
start
:Initiate Sync;
:Select Sync Type;

|#LightGreen|System|
fork
  -[#blue]-> Supervisor Sync;
  :Call Supervisor API;
  :Receive JSON Data;
  :Parse Supervisor List;
  
  while (For Each Supervisor) is (more)
    if (Exists in DB?) then (yes)
      if (Data Changed?) then (yes)
        :Update Record;
      else (no)
        :Skip;
      endif
    else (no)
      :Create New Record;
    endif
  endwhile (done)
  
fork again
  -[#green]-> Student Sync;
  :Call Student API;
  :Receive JSON Data;
  :Parse Student List;
  
  while (For Each Student) is (more)
    if (Exists in DB?) then (yes)
      :Update Record;
    else (no)
      :Create New Record;
    endif
  endwhile (done)
  
fork again
  -[#orange]-> Batch Sync;
  :Call Batch API;
  :Receive JSON Data;
  :Parse Batch List;
  
  while (For Each Batch) is (more)
    if (Exists in DB?) then (yes)
      :Update Status;
    else (no)
      :Create New Batch;
    endif
  endwhile (done)
  
end fork

|#LightYellow|Database|
:Commit All Changes;
:Update Sync Log;
:Calculate Statistics;

|#LightBlue|Administrator|
:View Sync Report;
:Download Log File;

stop

@enduml
```

---

## How to Use These Diagrams

### Option 1: PlantUML Online Editor
1. Go to https://www.plantuml.com/plantuml/uml/
2. Copy any diagram code above
3. Paste into the editor
4. View the generated diagram
5. Export as PNG/SVG/PDF

### Option 2: VS Code Extension
1. Install "PlantUML" extension
2. Create a `.puml` file
3. Paste the diagram code
4. Press `Alt+D` to preview
5. Right-click to export

### Option 3: Command Line
```bash
# Install PlantUML
java -jar plantuml.jar activity-diagram.puml

# Generate PNG
java -jar plantuml.jar -tpng activity-diagram.puml

# Generate SVG
java -jar plantuml.jar -tsvg activity-diagram.puml
```

### Option 4: Integration Tools
- **IntelliJ IDEA**: PlantUML Integration plugin
- **Eclipse**: PlantUML plugin
- **Confluence**: PlantUML macro
- **GitLab/GitHub**: PlantUML in markdown

---

## Key Features Demonstrated

### 1. **Swimlanes**
- Vertical lanes showing actor responsibilities
- Clear separation of concerns
- Cross-lane communication

### 2. **Fork/Join Bars**
- True parallel activities
- Concurrent processing
- Synchronization points

### 3. **Decision Nodes**
- Branching logic
- Multiple paths
- Guard conditions

### 4. **Loops**
- While loops for iterations
- For-each processing
- Conditional repetition

### 5. **Partitions**
- Logical grouping of activities
- Phase separation
- Sub-process organization

### 6. **Split/Merge**
- Multiple parallel paths
- Alternative flows
- Path convergence

---

## Notes
These PlantUML diagrams generate proper UML 2.5 activity diagrams that:
- Follow IEEE standards for software documentation
- Include proper fork/join bars for parallel activities
- Use swimlanes to show actor responsibilities
- Display correct UML notation (not flowcharts)
- Can be exported to various formats for inclusion in academic reports

Unlike Mermaid.js, PlantUML can render true UML activity diagrams with all standard elements including concurrent activities, swimlanes, and proper notation.