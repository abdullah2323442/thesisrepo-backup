# System Diagrams Documentation

This directory contains comprehensive system diagrams (Activity, Use Case, and Data Flow) for the Thesis Management System, designed for production-level documentation.

## 📊 Diagram Overview

### Activity Diagrams

#### 1. System Overview (`01-system-overview.puml`)
- High-level view of all system actors and their interactions
- Shows the complete workflow from system initialization to thesis completion
- Includes all 5 main roles: Admin, Advisor, Supervisor, Student, Panel Member

#### 2. Student Workflow (`02-student-workflow.puml`)
- Detailed student journey from login to thesis completion
- Covers group assignment, meetings, report submission, feedback cycles
- Includes error handling and revision processes
- Shows notification interactions

#### 3. Supervisor Workflow (`03-supervisor-workflow.puml`)
- Complete supervisor activities including group management
- Report review and annotation processes
- Meeting scheduling and management
- Co-supervisor and panel member coordination
- Report finalization workflow

#### 4. Advisor Workflow (`04-advisor-workflow.puml`)
- Group creation (manual and Excel import)
- Area of interest assignment
- Supervisor lottery assignment system
- Post-assignment management
- Co-supervisor and panel assignment

#### 5. Lottery Assignment Algorithm (`05-lottery-assignment-algorithm.puml`)
- Detailed algorithm flow for three assignment modes:
  - AOI (Area of Interest) matching with randomization
  - Ranking-based priority with round-robin
  - Combined intelligent mode with fair distribution
- Shows decision logic and fallback mechanisms

#### 6. Report Lifecycle (`06-report-lifecycle.puml`)
- Complete report submission and review cycle
- Annotation process details
- Multiple review states (Under Review, Needs Revision, Approved, Rejected)
- Co-supervisor and panel review integration
- Revision and resubmission workflows

#### 7. Admin Management (`07-admin-management.puml`)
- System configuration and management
- Area of Interest CRUD operations
- Supervisor synchronization from external API
- Batch management
- Performance monitoring
- User management

#### 8. API Integration (`08-api-integration.puml`)
- External API integration workflows
- Student, Batch, and Supervisor API synchronization
- Rate limiting and error handling
- Caching strategies
- API monitoring and degraded mode handling

#### 9. Notification System (`09-notification-system.puml`)
- Notification trigger events
- Multi-channel delivery (In-app, Email, Real-time)
- User interaction flows
- Notification types and their specific workflows
- Analytics and tracking

### Use Case Diagram

#### 10. Use Case Diagram (`16-use-case-diagram.puml`)
- Complete use case diagram showing all actors
- System features and functionalities
- Actor-use case relationships
- Include and extend relationships
- System boundary definition

### Data Flow Diagrams

#### 11. Data Flow Diagram - Context Level (`17-data-flow-diagram.puml`)
- Level 0 (Context) DFD
- System as single process
- All external entities
- Major data flows in/out of system

#### 12. Data Flow Diagram - Level 1 (`18-data-flow-diagram-level1.puml`)
- Detailed system decomposition
- 9 major processes
- Data stores
- Inter-process data flows
- External entity interactions

#### 13. Data Flow Diagram - Level 2 (`19-data-flow-diagram-level2.puml`)
- Report Management subsystem detail
- Submission process breakdown
- Review process breakdown
- Detailed data stores and flows

## 📁 Directory Structure

```
activity-diagrams/
├── *.puml                    # Full PlantUML diagrams
├── compact/                  # A4-optimized compact versions
│   └── *.puml
├── activity-mermaid/         # Mermaid format diagrams
│   ├── *.md                  # Mermaid diagrams in markdown
│   └── view-all-diagrams.html
└── documentation/
    └── README files
```

## 🛠️ How to Use These Diagrams

### Viewing the Diagrams

1. **PlantUML Online Editor**
   - Visit [PlantUML Online Server](http://www.plantuml.com/plantuml/uml/)
   - Copy the content of any `.puml` file
   - Paste and view the rendered diagram

2. **VS Code Extension**
   - Install "PlantUML" extension
   - Open any `.puml` file
   - Press `Alt+D` to preview

3. **Generate Images**
   ```bash
   # Install PlantUML
   java -jar plantuml.jar *.puml
   ```
   This will generate PNG/SVG files for all diagrams

### Exporting for Documentation

1. **PNG Export** (Recommended for documents)
   ```bash
   java -jar plantuml.jar -tpng *.puml
   ```

2. **SVG Export** (Scalable, good for web)
   ```bash
   java -jar plantuml.jar -tsvg *.puml
   ```

3. **PDF Export** (For formal documentation)
   ```bash
   java -jar plantuml.jar -tpdf *.puml
   ```

## 📋 Diagram Standards Used

- **Activity Diagrams**: UML 2.0 activity notation
- **Use Case Diagrams**: Standard UML use case elements
- **Data Flow Diagrams**: Gane-Sarson notation
- **Swimlanes**: Separate actor responsibilities
- **Colors**: Consistent color coding for different actors
- **Notes**: Provide context and additional information
- **Decision Points**: Clear if/else branches
- **Parallel Activities**: Fork/join for concurrent processes
- **Partitions**: Group related activities
- **Error Handling**: Shows failure paths and recovery

## 🎨 Color Scheme

- **Light Blue (#E3F2FD)**: Primary actors/supervisors
- **Light Green (#E8F5E9)**: System processes
- **Light Yellow (#FFF3E0)**: Secondary processes
- **Light Pink (#FFE0B2)**: Student activities
- **Light Gray (#EFEBE9)**: Panel/auxiliary activities
- **Light Cyan (#E0F2F1)**: Monitoring/analytics
- **Light Orange (#FFF8E1)**: External entities
- **Light Purple (#E1BEE7)**: Panel members
- **Light Beige (#FFF3E0)**: Data stores

## 📝 Customization

To customize these diagrams for your specific needs:

1. **Modify Actor Names**: Update the swimlane labels
2. **Add/Remove Steps**: Insert or delete activity nodes
3. **Change Flow Logic**: Modify decision conditions
4. **Update Notes**: Edit the note content for your context
5. **Adjust Colors**: Change the skinparam color values
6. **Add New Use Cases**: Extend the use case diagram
7. **Refine Data Flows**: Add more detail to DFDs

## 🔄 Keeping Diagrams Updated

1. Review diagrams when code changes affect workflows
2. Update after adding new features
3. Verify accuracy during code reviews
4. Version control all diagram files
5. Generate new images after updates
6. Update both PlantUML and Mermaid versions

## 📚 Additional Resources

- [PlantUML Activity Diagram Guide](https://plantuml.com/activity-diagram-beta)
- [PlantUML Use Case Diagram Guide](https://plantuml.com/use-case-diagram)
- [PlantUML Component Diagram Guide](https://plantuml.com/component-diagram)
- [Data Flow Diagram Tutorial](https://www.visual-paradigm.com/guide/data-flow-diagram/what-is-data-flow-diagram/)
- [Mermaid Documentation](https://mermaid-js.github.io/mermaid/)
- [PlantUML Themes](https://plantuml.com/theme)

## ✅ Validation Checklist

Before using these diagrams in production documentation:

- [ ] Verify all user roles are represented
- [ ] Check workflow accuracy against actual code
- [ ] Ensure error paths are documented
- [ ] Validate API endpoints and parameters
- [ ] Confirm notification types match implementation
- [ ] Review use cases with stakeholders
- [ ] Validate data flows with system architecture
- [ ] Test diagram rendering in target format
- [ ] Add version numbers and dates

## 📌 Integration with Documentation

These diagrams can be integrated into:

1. **Technical Specification Documents**
2. **User Manuals**
3. **API Documentation**
4. **Training Materials**
5. **System Architecture Documents**
6. **Code Comments** (link to diagrams)
7. **README files**
8. **Wiki Pages**
9. **Requirements Documents**
10. **Design Documents**

---

*Last Updated: Current Date*
*Version: 1.1*
*System: Thesis Management System*