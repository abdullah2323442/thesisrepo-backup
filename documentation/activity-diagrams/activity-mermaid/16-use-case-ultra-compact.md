# Use Case Diagram - Ultra Compact (Single A4 Page)

```mermaid
mindmap
  root((TMS))
    Student
      Submit Reports
      View Feedback
      Attend Meetings
      Track Progress
    Supervisor
      Review Reports
      Annotate PDFs
      Provide Feedback
      Schedule Meetings
      Finalize Thesis
    Advisor
      Create Groups
      Import Excel Data
      Assign Students
      Set Areas of Interest
      Run Supervisor Lottery
    Admin
      Manage Users
      Configure System
      Sync External APIs
      Monitor Performance
      Backup Data
    Panel
      Review Final Reports
      Conduct Defense
      Evaluate Thesis
      Assign Grades
```

## Alternative Compact Representation

```mermaid
graph LR
    TMS[Thesis Management System]
    
    TMS --> S[Student<br/>Features]
    TMS --> T[Supervisor<br/>Features]
    TMS --> A[Advisor<br/>Features]
    TMS --> D[Admin<br/>Features]
    TMS --> P[Panel<br/>Features]
    
    S --> S1[Submit]
    S --> S2[View]
    S --> S3[Track]
    
    T --> T1[Review]
    T --> T2[Annotate]
    T --> T3[Guide]
    
    A --> A1[Groups]
    A --> A2[Assign]
    A --> A3[Lottery]
    
    D --> D1[Users]
    D --> D2[Config]
    D --> D3[Monitor]
    
    P --> P1[Evaluate]
    P --> P2[Grade]
    
    style TMS fill:#E3F2FD,stroke:#1976D2,stroke-width:3px
    style S fill:#FFE0B2
    style T fill:#C8E6C9
    style A fill:#C5CAE9
    style D fill:#FFCDD2
    style P fill:#E1BEE7
```

## Quick Reference Card

### 🎯 Core System Functions

| **Students** | **Supervisors** | **Advisors** | **Admin** | **Panel** |
|:------------|:---------------|:------------|:----------|:---------|
| ✓ Submit | ✓ Review | ✓ Create Groups | ✓ Manage | ✓ Evaluate |
| ✓ View | ✓ Annotate | ✓ Assign | ✓ Configure | ✓ Grade |
| ✓ Track | ✓ Guide | ✓ Lottery | ✓ Monitor | ✓ Assess |
| ✓ Attend | ✓ Schedule | ✓ Import | ✓ Sync | ✓ Decide |

### 📊 System Statistics
- **5** Actor Types
- **25+** Use Cases
- **10** Core Workflows
- **3** Assignment Modes
- **4** Report States

### 🔄 Primary Workflows
1. **Submit** → **Review** → **Feedback** → **Revise**
2. **Groups** → **Assign** → **Supervise** → **Complete**
3. **Configure** → **Operate** → **Monitor** → **Optimize**