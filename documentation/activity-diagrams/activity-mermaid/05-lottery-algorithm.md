# Supervisor Assignment Algorithm (SupervisorAssignmentService)

```mermaid
flowchart TD
    Start([Start SupervisorAssignmentService]) --> LoadData[Load Groups, Supervisors, AreaOfInterests]
    LoadData --> CheckRelations[Check Group-AreaOfInterest Relations<br/>(Many-to-Many)]
    CheckRelations --> LoadSupervisors[Load Supervisor-AreaOfInterest Relations<br/>(Many-to-Many)]
    
    LoadSupervisors --> SelectMode{Assignment Strategy}
    
    SelectMode -->|AOI Matching| AOIProcess[Match via AreaOfInterest Model]
    AOIProcess --> QueryPrimary[Query Primary AOI Matches]
    QueryPrimary --> PrimaryMatch{Supervisors Found?}
    
    PrimaryMatch -->|No| QuerySecondary[Query Secondary AOI Matches]
    PrimaryMatch -->|Yes| FilterAvailable1[Filter by Capacity]
    QuerySecondary --> FilterAvailable1
    
    SelectMode -->|Ranking| RankProcess[Sort Supervisors by Designation]
    RankProcess --> RankOrder[Professor → Associate → Assistant → Lecturer]
    RankOrder --> RoundRobin[Round-Robin Assignment]
    
    SelectMode -->|Combined| CombinedProcess[Match AOI + Rank Weight]
    CombinedProcess --> CalculateScores[Calculate Assignment Scores]
    CalculateScores --> PreventOverload[Apply Load Balancing]
    
    FilterAvailable1 --> CheckCapacity[Check Supervisor Current Load]
    RoundRobin --> CheckCapacity
    PreventOverload --> CheckCapacity
    
    CheckCapacity --> CanAssign{Within Capacity Limit?}
    
    CanAssign -->|Yes| CreateAssignment[Create Group-Supervisor Assignment]
    CreateAssignment --> CheckCoSupervisor{Assign Co-Supervisor?}
    
    CheckCoSupervisor -->|Yes| FindCoSupervisor[Find Different AOI Supervisor]
    CheckCoSupervisor -->|No| LogHistory
    FindCoSupervisor --> LogHistory[Log to AssignmentHistory Model]
    
    CanAssign -->|No| MarkPending[Mark Group as Pending]
    MarkPending --> LogHistory
    
    LogHistory --> UpdateCounts[Update Assignment Statistics]
    UpdateCounts --> MoreGroups{More Groups to Process?}
    
    MoreGroups -->|Yes| NextGroup[Get Next Group]
    NextGroup --> SelectMode
    
    MoreGroups -->|No| HandleUnassigned{Any Unassigned Groups?}
    
    HandleUnassigned -->|Yes| ManualQueue[Queue for Manual Assignment]
    HandleUnassigned -->|No| GenerateReport
    
    ManualQueue --> GenerateReport[Generate Assignment Report]
    GenerateReport --> CallAPIs[Sync via External APIs]
    
    CallAPIs --> BatchAPI[BatchApiService::syncAssignments()]
    CallAPIs --> StudentAPI[StudentApiService::notifyAssignments()]
    CallAPIs --> SupervisorAPI[SupervisorApiService::updateLoads()]
    
    BatchAPI --> SendNotifications[Trigger NewReportAssigned Notifications]
    StudentAPI --> SendNotifications
    SupervisorAPI --> SendNotifications
    
    SendNotifications --> End([Assignment Complete])
    
    style Start fill:#4CAF50,color:#fff
    style End fill:#f44336,color:#fff
    style SelectMode fill:#FFE082
    style PrimaryMatch fill:#FFE082
    style CanAssign fill:#FFE082
    style MoreGroups fill:#FFE082
    style HandleUnassigned fill:#FFE082
    style CheckCoSupervisor fill:#FFE082
    style CallAPIs fill:#E1F5FE
```

## Description
The SupervisorAssignmentService implementation for automatic supervisor assignment with support for co-supervisors and external API integration.

## Key Models & Services
- **SupervisorAssignmentService**: Main assignment logic (app/Services/)
- **Group Model**: Groups requiring supervisors
- **Supervisor Model**: Available supervisors with capacities
- **AreaOfInterest Model**: Research areas for matching
- **AssignmentHistory Model**: Audit trail of all assignments
- **External APIs**: BatchApiService, StudentApiService, SupervisorApiService

## Assignment Strategies

### AOI Matching
- Uses many-to-many relations from group_area_of_interest table
- Matches with supervisor_area_of_interest table
- Supports primary and secondary area fallback

### Rank Priority
- Uses Supervisor model designation field
- Implements round-robin for fairness
- Tracks assignments per rank level

### Combined Mode
- Weighted scoring system
- Considers both AOI match and rank
- Prevents senior faculty overload via capacity checks

## Co-Supervisor Assignment
- Optional based on configuration
- Selects from different AOI for diversity
- Logged separately in AssignmentHistory

## Audit & Tracking
- All assignments logged to AssignmentHistory table
- Includes timestamp, assigner, method used
- Supports rollback and historical analysis