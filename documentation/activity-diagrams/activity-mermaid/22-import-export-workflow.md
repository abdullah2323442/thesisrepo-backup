# Import/Export Data Workflow

```mermaid
flowchart TD
    Start([Data Operation Request]) --> OperationType{Operation Type}
    
    OperationType -->|Import| ImportFlow[Import Process]
    OperationType -->|Export| ExportFlow[Export Process]
    
    %% Import Flow
    ImportFlow --> SelectImportType{Import Type}
    
    SelectImportType -->|Group Assignment| GroupImport[GroupAssignmentImport]
    SelectImportType -->|Batch Data| BatchImport[Batch Import via API]
    SelectImportType -->|Student List| StudentImport[Student Import]
    
    GroupImport --> UploadExcel[Upload Excel File]
    UploadExcel --> ValidateFormat{Valid Excel Format?}
    
    ValidateFormat -->|No| ShowFormatError[Show Format Error]
    ShowFormatError --> UploadExcel
    
    ValidateFormat -->|Yes| ParseExcel[Parse Excel with Maatwebsite\Excel]
    ParseExcel --> ValidateHeaders{Headers Match Template?}
    
    ValidateHeaders -->|No| ShowHeaderError[Show Header Mismatch]
    ShowHeaderError --> DownloadTemplate[Download Template]
    DownloadTemplate --> UploadExcel
    
    ValidateHeaders -->|Yes| ProcessRows[Process Each Row]
    ProcessRows --> ValidateRow{Validate Row Data}
    
    ValidateRow -->|Invalid| LogRowError[Log Row Error]
    LogRowError --> ContinueNext{More Rows?}
    
    ValidateRow -->|Valid| CheckExistence{Check Existing Records}
    
    CheckExistence -->|Student Not Found| CreateStudent[Create User with student role]
    CheckExistence -->|Group Not Found| CreateGroup[Create Group]
    CheckExistence -->|Exists| UpdateRecord[Update Existing]
    
    CreateStudent --> CreateGroupStudent[Create GroupStudent Entry]
    CreateGroup --> CreateGroupStudent
    UpdateRecord --> CreateGroupStudent
    
    CreateGroupStudent --> UpdateRelations[Update Relations:<br/>- Group-AreaOfInterest<br/>- Supervisor Assignment]
    UpdateRelations --> LogSuccess[Log Successful Import]
    
    LogSuccess --> ContinueNext
    ContinueNext -->|Yes| ProcessRows
    ContinueNext -->|No| GenerateImportReport[Generate Import Report]
    
    %% Batch Import via API
    BatchImport --> CallBatchAPI[Call BatchApiService::import()]
    CallBatchAPI --> ProcessAPIResponse[Process API Response]
    ProcessAPIResponse --> SyncBatchModel[Sync with Batch Model]
    SyncBatchModel --> GenerateImportReport
    
    %% Student Import
    StudentImport --> CallStudentAPI[Call StudentApiService::import()]
    CallStudentAPI --> ValidateStudentData[Validate Student Data]
    ValidateStudentData --> CreateUserRecords[Create/Update User Records]
    CreateUserRecords --> GenerateImportReport
    
    %% Export Flow
    ExportFlow --> SelectExportType{Export Type}
    
    SelectExportType -->|Group Template| TemplateExport[GroupTemplateExport]
    SelectExportType -->|Assignment Report| AssignmentExport[Assignment History Export]
    SelectExportType -->|Student List| StudentExport[Student List Export]
    SelectExportType -->|Supervisor Load| SupervisorExport[Supervisor Load Export]
    
    TemplateExport --> GenerateTemplate[Generate Excel Template]
    GenerateTemplate --> AddHeaders[Add Required Headers:<br/>- Student Name<br/>- Registration<br/>- Group Name<br/>- Area of Interest]
    AddHeaders --> AddSampleData[Add Sample Data Row]
    AddSampleData --> AddValidation[Add Data Validation Rules]
    AddValidation --> SaveExcel[Save as .xlsx]
    
    AssignmentExport --> QueryAssignmentHistory[Query AssignmentHistory Model]
    QueryAssignmentHistory --> JoinRelations[Join Group, Supervisor, Batch]
    JoinRelations --> FormatExportData[Format Export Data]
    
    StudentExport --> QueryGroupStudent[Query GroupStudent with Relations]
    QueryGroupStudent --> IncludeUserData[Include User Details]
    IncludeUserData --> FormatExportData
    
    SupervisorExport --> QuerySupervisor[Query Supervisor Model]
    QuerySupervisor --> CalculateLoads[Calculate Current Loads]
    CalculateLoads --> IncludeAOI[Include Area of Interests]
    IncludeAOI --> FormatExportData
    
    FormatExportData --> GenerateExcel[Generate Excel with Maatwebsite\Excel]
    SaveExcel --> SetHeaders[Set Download Headers]
    GenerateExcel --> SetHeaders
    
    SetHeaders --> StreamDownload[Stream Download to Browser]
    StreamDownload --> LogExport[Log Export Activity]
    
    GenerateImportReport --> ShowSummary[Show Import Summary:<br/>- Total Processed<br/>- Successful<br/>- Failed<br/>- Errors]
    LogExport --> End([Operation Complete])
    ShowSummary --> End
    
    style Start fill:#4CAF50,color:#fff
    style End fill:#4CAF50,color:#fff
    style OperationType fill:#FFE082
    style SelectImportType fill:#FFE082
    style SelectExportType fill:#FFE082
    style ValidateFormat fill:#FFE082
    style ValidateHeaders fill:#FFE082
    style ValidateRow fill:#FFE082
    style CheckExistence fill:#FFE082
    style ContinueNext fill:#FFE082
```

## Description
Complete import/export workflow using Laravel Excel (Maatwebsite\Excel) package.

## Import Classes (app/Imports/)
- **GroupAssignmentImport**: Imports student-group assignments from Excel
- Handles validation, duplicate checking, and relationship creation

## Export Classes (app/Exports/)
- **GroupTemplateExport**: Generates Excel template for group imports
- Includes headers, sample data, and validation rules

## Import Features
1. **Excel Validation**: Format, headers, data types
2. **Row-by-row Processing**: With error logging
3. **Relationship Management**: Creates GroupStudent, updates AOI links
4. **API Integration**: Batch and Student imports via services
5. **Transaction Support**: Rollback on critical errors

## Export Features
1. **Template Generation**: With validation rules
2. **Data Exports**: Assignment history, student lists, supervisor loads
3. **Relationship Inclusion**: Joins related data
4. **Streaming**: Large dataset support
5. **Activity Logging**: Tracks all exports

## Error Handling
- Row-level validation with detailed error messages
- Partial import support (skip invalid rows)
- Comprehensive import report generation
- Rollback capability for failed imports

## File Formats
- **Import**: .xlsx, .xls, .csv
- **Export**: .xlsx with formatting
- **Templates**: .xlsx with data validation