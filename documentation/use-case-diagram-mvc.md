# Thesis Management System - UML Use Case Diagram

## Standard UML Use Case Diagram (A4-Friendly)

```mermaid
%%{init: {'theme':'base', 'themeVariables': { 'fontSize':'14px'}}}%%
graph LR
    %% Left Side Actors
    Student([👤 Student])
    Advisor([👤 Advisor])
    
    %% Right Side Actors
    Admin([👤 Admin])
    Supervisor([👤 Supervisor])
    ExtAPI([🌐 External API])
    
    %% System Boundary
    subgraph System["Thesis Management System"]
        direction TB
        
        %% Row 1 - Core Authentication & Management
        subgraph Row1[" "]
            direction LR
            UC1((Authenticate))
            UC2((Manage<br/>System))
            UC3((Sync External<br/>Data))
        end
        
        %% Row 2 - Group & Assignment
        subgraph Row2[" "]
            direction LR
            UC4((Manage<br/>Groups))
            UC5((Assign<br/>Supervisors))
            UC6((Run Lottery<br/>Assignment))
        end
        
        %% Row 3 - Academic Activities
        subgraph Row3[" "]
            direction LR
            UC7((Submit<br/>Reports))
            UC8((Review<br/>Reports))
            UC9((Schedule<br/>Meetings))
        end
        
        %% Include/Extend Relationships
        UC6 -.->|<<include>>| UC5
        UC8 -.->|<<extend>>| UC10((Approve<br/>Thesis))
    end
    
    %% Actor Connections
    Student --> UC1
    Student --> UC7
    
    Advisor --> UC1
    Advisor --> UC4
    Advisor --> UC5
    Advisor --> UC6
    
    Admin --> UC1
    Admin --> UC2
    Admin --> UC3
    
    Supervisor --> UC1
    Supervisor --> UC8
    Supervisor --> UC9
    Supervisor --> UC10
    
    ExtAPI -.->|provides data| UC3
    
    %% Styling
    classDef actorStyle fill:#4A90E2,stroke:#2E5C8A,stroke-width:3px,color:#fff
    classDef useCaseStyle fill:#5DADE2,stroke:#2874A6,stroke-width:2px,color:#fff
    classDef systemStyle fill:#ffffff,stroke:#34495E,stroke-width:3px
    classDef rowStyle fill:none,stroke:none
    
    class Student,Advisor,Admin,Supervisor actorStyle
    class ExtAPI actorStyle
    class UC1,UC2,UC3,UC4,UC5,UC6,UC7,UC8,UC9,UC10 useCaseStyle
    class Row1,Row2,Row3 rowStyle
```

## Simplified High-Level Use Case Diagram

### Core System Goals
This simplified diagram shows only the **main goals** of the Thesis Management System:

1. **Authentication & System Management** - Secure access and administration
2. **Group Management & Supervisor Assignment** - Core thesis group operations
3. **Academic Activities** - Reports and meetings management

### Actors (Blue Rectangles)
- **Student**: Submit reports and participate in thesis activities
- **Advisor**: Manage groups and assign supervisors
- **Admin**: System administration and data synchronization
- **Supervisor**: Review reports and schedule meetings
- **External API**: Provides university data

### Core Use Cases (Blue Ovals)
| Use Case | Main Goal |
|----------|-----------|
| Authenticate | Secure system access for all users |
| Manage System | Administrative control and monitoring |
| Sync External Data | Integration with university systems |
| Manage Groups | Create and organize thesis groups |
| Assign Supervisors | Manual supervisor assignment |
| Run Lottery Assignment | Automated fair assignment algorithm |
| Submit Reports | Student thesis submissions |
| Review Reports | Supervisor evaluation process |
| Schedule Meetings | Thesis progress meetings |
| Approve Thesis | Final thesis approval (extends Review) |

### Key Relationships
- **Lottery includes Assignment**: The lottery algorithm performs supervisor assignment
- **Review extends to Approval**: Approval is the final step of review process
- **External API provides data**: University system integration

## Why This Simplified Approach?

### ✅ Benefits for Thesis Evaluation

1. **Clear Main Goals**: Shows the system's primary purpose at a glance
2. **Easy to Understand**: Evaluators can quickly grasp the system scope
3. **Fits on Page**: Compact 3x3 grid layout perfect for A4 printing
4. **Professional**: Clean, uncluttered design
5. **Focus on Value**: Highlights what the system achieves, not implementation details

### 📊 System Overview

The diagram captures the **three pillars** of the thesis management system:

1. **Administrative Layer**
   - User authentication
   - System management
   - External data integration

2. **Organizational Layer**
   - Group formation
   - Supervisor assignment
   - Lottery algorithm (key innovation)

3. **Academic Layer**
   - Report submission
   - Review process
   - Meeting coordination
   - Thesis approval

## MVC Architecture Alignment

### How Core Use Cases Map to MVC

| Use Case | Controller | Service | Model |
|----------|------------|---------|-------|
| Authenticate | AuthController | - | User |
| Manage System | AdminController | PerformanceService | User, Batch |
| Sync External Data | AdminController | External API Services | Batch, Supervisor |
| Manage Groups | AdvisorController | - | Group |
| Assign Supervisors | AdvisorController | - | Group, Supervisor |
| Run Lottery | AdvisorController | SupervisorAssignmentService | Group, Supervisor |
| Submit Reports | StudentController | - | Report |
| Review Reports | SupervisorController | - | Report |
| Schedule Meetings | SupervisorController | - | Meeting |
| Approve Thesis | SupervisorController | - | Report |

### Key System Features

✅ **Lottery Algorithm** - Fair automated supervisor assignment  
✅ **External Integration** - University API synchronization  
✅ **Role-Based Access** - Multi-user authentication  
✅ **Report Workflow** - Submit → Review → Approve  
✅ **Group Management** - Thesis group organization

---

**Document Version**: 2.0  
**Created**: January 2025  
**Format**: Standard UML Use Case Diagram (Mermaid)  
**Optimized For**: A4 Report Printing  
**Compliant With**: UML 2.5 Use Case Diagram Standards
