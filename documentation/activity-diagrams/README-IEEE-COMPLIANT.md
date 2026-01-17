# IEEE-Compliant Activity Diagrams

## Overview

This directory contains **IEEE standard-compliant** activity diagrams for the University Thesis Management System. These diagrams are specifically designed for inclusion in A4-sized IEEE software project reports.

## Design Principles

### IEEE Compliance
- **Standard UML 2.0 notation**
- **Clean, professional appearance**
- **Black and white color scheme** (printer-friendly)
- **Arial font, 10pt** (readable when printed)
- **Simplified structure** (fits A4 page)
- **Minimal partitions** (reduces complexity)
- **Clear decision points**
- **Proper start/stop nodes**

### A4 Page Optimization
- Each diagram fits comfortably on a single A4 page
- Adequate white space for readability
- Concise activity labels
- Strategic use of notes for additional information
- No excessive nesting or complexity

## Diagram List

### 1. Authentication & Access Control
| File | Title | Description |
|------|-------|-------------|
| `01-authentication-activity.puml` | User Authentication Process | Login flow with role-based routing |

### 2. Student Operations
| File | Title | Description |
|------|-------|-------------|
| `02-student-report-submission.puml` | Student Report Submission Process | Report upload and validation |
| `03-student-view-feedback.puml` | Student View Feedback Process | Accessing annotated reports |
| `04-notification-system.puml` | Dashboard Notification System | Notification display and management |

### 3. Supervisor Operations
| File | Title | Description |
|------|-------|-------------|
| `05-supervisor-report-assignment.puml` | Supervisor Report Assignment Process | Creating report assignments |
| `06-supervisor-annotation.puml` | Document Annotation Process | PDF annotation workflow |
| `07-meeting-documentation.puml` | Meeting Documentation Process | Recording supervision meetings |

### 4. Advisor Operations
| File | Title | Description |
|------|-------|-------------|
| `08-manual-group-creation.puml` | Manual Group Creation Process | Interactive group formation |
| `09-excel-group-import.puml` | Excel-Based Group Import Process | Bulk group import via Excel |
| `10-template-export.puml` | Template Export Process | Generating Excel templates |
| `11-aoi-assignment.puml` | Area of Interest Based Assignment | Expertise-based supervisor assignment |
| `12-ranking-assignment.puml` | Ranking-Based Assignment | Fair distribution by designation |
| `13-hybrid-assignment.puml` | Hybrid Assignment Strategy | Balanced assignment approach |

### 5. Administrative Functions
| File | Title | Description |
|------|-------|-------------|
| `14-data-synchronization.puml` | External Data Synchronization Process | API data sync workflow |
| `15-system-monitoring.puml` | System Monitoring Process | Performance monitoring dashboard |

## Usage Instructions

### Generating PNG Images for IEEE Report

#### Method 1: Using PlantUML Online
1. Visit: https://www.plantuml.com/plantuml/uml/
2. Copy the content of any `.puml` file
3. Paste into the online editor
4. Download as PNG (300 DPI recommended for print)

#### Method 2: Using PlantUML Command Line
```bash
# Install PlantUML (requires Java)
# Then run:
java -jar plantuml.jar -tpng *.puml
```

#### Method 3: Using VS Code Extension
1. Install "PlantUML" extension
2. Open any `.puml` file
3. Press `Alt+D` to preview
4. Right-click → Export → PNG

### Recommended Export Settings
- **Format**: PNG
- **DPI**: 300 (for print quality)
- **Background**: White
- **Size**: Auto (will fit A4 when inserted)

## Including in IEEE Report

### LaTeX Example
```latex
\begin{figure}[h]
\centering
\includegraphics[width=0.8\textwidth]{01-authentication-activity.png}
\caption{User Authentication Activity Diagram}
\label{fig:auth-activity}
\end{figure}
```

### Microsoft Word
1. Insert → Pictures
2. Select the PNG file
3. Resize to fit page width (typically 6-6.5 inches)
4. Add caption: "Figure X: [Diagram Title]"
5. Ensure "In Line with Text" wrapping

### Page Layout Recommendations
- **One diagram per page** for clarity
- **Caption below** the diagram
- **Reference in text** before the figure appears
- **Explain key decision points** in the text

## Mapping to Sequence Diagrams

Each activity diagram corresponds to sequence diagrams in `sequence.md`:

| Activity Diagram | Sequence Diagram Reference |
|-----------------|---------------------------|
| 01 | Section 1.1 - User Authentication Process |
| 02 | Section 2.1 - Report Submission Process |
| 03 | Section 2.2 - View Feedback and Annotations |
| 04 | Section 2.3 - Dashboard Notification System |
| 05 | Section 3.1 - Report Management |
| 06 | Section 3.2.1, 3.2.2 - Document Annotation |
| 07 | Section 3.3 - Meeting Documentation |
| 08 | Section 4.1.1 - Manual Group Creation |
| 09 | Section 4.1.2 - Excel-Based Group Import |
| 10 | Section 4.1.3 - Template Export |
| 11 | Section 4.2.2 - AOI-Based Assignment |
| 12 | Section 4.2.3 - Ranking-Based Assignment |
| 13 | Section 4.2.4 - Hybrid Assignment |
| 14 | Section 5.1 - External Data Synchronization |
| 15 | Section 5.2 - System Monitoring |

## IEEE Report Structure Suggestion

### Chapter 4: System Design

#### 4.1 Activity Diagrams
Activity diagrams illustrate the workflow and control flow of various system processes.

##### 4.1.1 Authentication Process
[Insert Figure: 01-authentication-activity.png]

The authentication process begins when a user submits credentials...

##### 4.1.2 Student Operations
[Insert Figures: 02, 03, 04]

Students interact with the system through three primary workflows...

##### 4.1.3 Supervisor Operations
[Insert Figures: 05, 06, 07]

Supervisors manage reports, provide feedback, and document meetings...

##### 4.1.4 Advisor Operations
[Insert Figures: 08-13]

Advisors handle group formation and supervisor assignment...

##### 4.1.5 Administrative Functions
[Insert Figures: 14, 15]

Administrators maintain system integrity through synchronization and monitoring...

## Quality Checklist

Before including in your report, verify:

- [ ] Diagram renders correctly at 300 DPI
- [ ] All text is readable when printed
- [ ] Diagram fits within page margins
- [ ] Title is clear and descriptive
- [ ] Decision points are properly labeled
- [ ] Start and stop nodes are present
- [ ] Flow direction is top-to-bottom
- [ ] Notes provide necessary context
- [ ] No overlapping elements
- [ ] Consistent styling across all diagrams

## Customization

If you need to modify diagrams for your specific report:

1. **Font Size**: Change `defaultFontSize` (currently 10)
2. **Font Family**: Change `defaultFontName` (currently Arial)
3. **Colors**: Modify `activityBackgroundColor` (currently white)
4. **Borders**: Adjust `activityBorderColor` (currently black)

Example:
```plantuml
skinparam defaultFontSize 11
skinparam defaultFontName Times New Roman
```

## Troubleshooting

### Diagram Too Large
- Remove unnecessary notes
- Combine sequential activities
- Simplify decision labels

### Text Not Readable
- Increase font size to 11 or 12
- Use shorter activity labels
- Move detailed info to notes

### Doesn't Fit A4
- Reduce number of activities
- Split into multiple diagrams
- Remove partition blocks

## References

- **UML 2.0 Specification**: https://www.omg.org/spec/UML/2.0/
- **PlantUML Documentation**: https://plantuml.com/activity-diagram-beta
- **IEEE Software Documentation Standards**: IEEE Std 1016-2009

## Support

For questions or modifications, refer to:
- Main documentation: `../README.md`
- Sequence diagrams: `../../sequence.md`
- Mapping document: `../../SEQUENCE-TO-ACTIVITY-MAPPING.md`

---

**Note**: These diagrams are optimized for IEEE standard software project reports and academic publications. They prioritize clarity, simplicity, and printability over decorative elements.
