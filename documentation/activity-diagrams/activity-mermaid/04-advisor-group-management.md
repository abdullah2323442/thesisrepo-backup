# Advisor Group Management

```mermaid
flowchart TD
    Start([Access Advisor Panel]) --> CreateMethod{Creation Method?}
    
    CreateMethod -->|Manual| AddGroups[Add Groups One by One]
    AddGroups --> AssignStudentsMan[Assign Students Manually]
    
    CreateMethod -->|Excel Import| DownloadTemp[Download Template]
    DownloadTemp --> FillData[Fill Student Data]
    FillData --> UploadExcel[Upload Excel File]
    UploadExcel --> Validate[Validate & Import]
    
    AssignStudentsMan --> SetAOI[Set Areas of Interest]
    Validate --> SetAOI
    
    SetAOI --> AssignType{Assignment Type?}
    
    AssignType -->|Manual| SelectSup[Select Supervisor]
    SelectSup --> CheckCap[Check Capacity]
    CheckCap --> AssignGroup[Assign to Group]
    
    AssignType -->|Lottery| SelectMode[Select Mode]
    SelectMode --> ModeChoice{Mode?}
    
    ModeChoice -->|AOI| AOIMatch[AOI Matching]
    ModeChoice -->|Rank| RankPriority[Rank Priority]
    ModeChoice -->|Combined| CombinedMode[Combined Mode]
    
    AOIMatch --> Preview[Preview Results]
    RankPriority --> Preview
    CombinedMode --> Preview
    
    Preview --> RunAssign[Run Assignment]
    AssignGroup --> ReviewResults[Review Results]
    RunAssign --> ReviewResults
    
    ReviewResults --> CheckUnassigned{Unassigned Groups?}
    
    CheckUnassigned -->|Yes| HandleManually[Handle Manually]
    CheckUnassigned -->|No| Complete[Assignment Complete]
    
    HandleManually --> Complete
    Complete --> End([End])
    
    style Start fill:#4CAF50,color:#fff
    style End fill:#f44336,color:#fff
    style CreateMethod fill:#FFE082
    style AssignType fill:#FFE082
    style ModeChoice fill:#FFE082
    style CheckUnassigned fill:#FFE082
```

## Description
Advisor workflow for creating groups and assigning supervisors through manual or lottery methods.

## Creation Methods
- **Manual**: Add groups individually
- **Excel Import**: Bulk import via template

## Assignment Modes
- **AOI Match**: Match by Area of Interest
- **Rank Priority**: Professor → Associate → Assistant → Lecturer
- **Combined**: Both AOI and rank considered