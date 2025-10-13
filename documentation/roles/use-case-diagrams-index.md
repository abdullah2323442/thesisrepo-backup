# Use Case Diagrams - Role-Based Index

## Overview
This document provides links to individual use case diagrams for each role in the Thesis Management System. Each diagram details the specific capabilities, workflows, and constraints for that role.

## Individual Role Use Case Diagrams

### 1. [Admin Use Case Diagram](./Admin-use-case-diagram.md)
- **Primary Focus**: System configuration and management
- **Key Features**: AOI management, supervisor sync, batch management, global groups, performance monitoring
- **Unique Capabilities**: Highest system access, API synchronization, performance monitoring

### 2. [Student Use Case Diagram](./Student-use-case-diagram.md)
- **Primary Focus**: Document submission and progress tracking
- **Key Features**: Dashboard view, document submission, notification management, annotation viewing
- **Unique Capabilities**: Submit thesis documents, receive feedback notifications

### 3. [Supervisor Use Case Diagram](./Supervisor-use-case-diagram.md)
- **Primary Focus**: Thesis supervision and approval
- **Key Features**: Meeting management, report creation, thesis approval, annotation
- **Unique Capabilities**: Final thesis approval, publish to repository, full meeting control

### 4. [Advisor Use Case Diagram](./Advisor-use-case-diagram.md)
- **Primary Focus**: Group formation and supervisor assignment
- **Key Features**: Student management, group creation, supervisor assignment, Excel integration
- **Unique Capabilities**: Lottery assignment system, bulk operations via Excel

### 5. [Co-Supervisor Use Case Diagram](./Co-Supervisor-use-case-diagram.md)
- **Primary Focus**: Assist in supervision with conditional permissions
- **Key Features**: Conditional meeting management, report review, annotation
- **Unique Capabilities**: Permission-based meeting management per group

### 6. [Panel Member Use Case Diagram](./PanelMember-use-case-diagram.md)
- **Primary Focus**: Review and feedback only
- **Key Features**: Report review, annotation, feedback delivery
- **Unique Capabilities**: Most limited role, focused purely on review

### 7. [Teacher Use Case Diagram](./Teacher-use-case-diagram.md)
- **Primary Focus**: Umbrella role for faculty with multiple assignments
- **Key Features**: Role switching, access to multiple dashboards, comment system
- **Unique Capabilities**: Dynamic role detection, seamless role switching

## Comparison Summary

| Role | Management | Review | Annotation | Approval | Special Features |
|------|------------|--------|------------|----------|------------------|
| **Admin** | Full System | - | - | - | Performance Monitoring, API Sync |
| **Student** | - | View Only | View Only | - | Document Submission |
| **Supervisor** | Full Group | Full | Full | Final Thesis | Meeting Export, Publishing |
| **Advisor** | Group Formation | - | - | - | Lottery System, Excel Import |
| **Co-Supervisor** | Conditional | Full | Full | - | Permission-based Meetings |
| **Panel Member** | - | Full | Full | - | Review-only Access |
| **Teacher** | Based on Role | Based on Role | Based on Role | Based on Role | Role Switching, Comments |

## System-Wide Use Case Overview

For a complete system overview showing all roles together, see:
- [Complete System Use Case Diagram](./use-case-diagram.md)

## Diagram Formats

Each role diagram includes:
1. **Detailed Version**: Comprehensive view of all use cases
2. **Simplified Version**: Condensed view for better readability
3. **Workflow Diagrams**: Process flows for key operations
4. **Comparison Tables**: Role capabilities and constraints

## Usage Guidelines

### For Thesis Documentation
- Use the **Simplified Versions** for A4 thesis reports
- Include **Workflow Diagrams** to explain complex processes
- Reference **Comparison Tables** to highlight role differences

### For Development Reference
- Use **Detailed Versions** for implementation guidance
- Refer to **Access Paths** for routing configuration
- Check **Permission Matrices** for authorization logic

## Notes
- All diagrams use Mermaid syntax for easy rendering
- Color coding is consistent across all diagrams
- Conditional features are marked with asterisks (*)
- Include/extend relationships show use case dependencies