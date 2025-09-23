# Supervisor Lottery Assignment Algorithm

```mermaid
flowchart TD
    Start([Start Lottery]) --> LoadData[Load Groups & Supervisors]
    LoadData --> SelectMode{Select Mode}
    
    SelectMode -->|AOI| AOIProcess[Match by Area of Interest]
    AOIProcess --> TryPrimary[Try Primary Area First]
    TryPrimary --> PrimaryMatch{Match Found?}
    
    PrimaryMatch -->|No| TrySecondary[Try Secondary Areas]
    PrimaryMatch -->|Yes| RandomSelect1[Random Selection in Area]
    TrySecondary --> RandomSelect1
    
    SelectMode -->|Ranking| RankProcess[Sort by Designation]
    RankProcess --> RankOrder[Professor → Associate → Assistant → Lecturer]
    RankOrder --> RoundRobin[Round-Robin Assignment]
    
    SelectMode -->|Combined| CombinedProcess[Match Area + Rank]
    CombinedProcess --> FairDist[Fair Distribution]
    FairDist --> PreventOverload[Prevent Overload]
    
    RandomSelect1 --> CheckCapacity[Check Supervisor Capacity]
    RoundRobin --> CheckCapacity
    PreventOverload --> CheckCapacity
    
    CheckCapacity --> CanAssign{Can Assign?}
    
    CanAssign -->|Yes| AssignToGroup[Assign to Group]
    AssignToGroup --> UpdateCounts[Update Assignment Counts]
    
    CanAssign -->|No| MarkUnassigned[Mark as Unassigned]
    
    UpdateCounts --> MoreGroups{More Groups?}
    MarkUnassigned --> MoreGroups
    
    MoreGroups -->|Yes| NextGroup[Process Next Group]
    NextGroup --> SelectMode
    
    MoreGroups -->|No| GenerateReport[Generate Report]
    GenerateReport --> End([End])
    
    style Start fill:#4CAF50,color:#fff
    style End fill:#f44336,color:#fff
    style SelectMode fill:#FFE082
    style PrimaryMatch fill:#FFE082
    style CanAssign fill:#FFE082
    style MoreGroups fill:#FFE082
```

## Description
The lottery algorithm for automatic supervisor assignment with three different modes.

## Assignment Modes

### AOI Matching
- Matches groups to supervisors by area of interest
- Tries primary area first, then secondary areas
- Random selection within matching supervisors

### Rank Priority
- Assigns based on academic rank
- Round-robin to ensure fair distribution
- Ignores area of interest

### Combined Mode
- Considers both area and rank
- Ensures fair load distribution
- Prevents senior faculty overload