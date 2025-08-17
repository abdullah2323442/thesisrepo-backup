# Excel Upload Guide for Thesis Group Assignment

## File Format Requirements

Your Excel file (like "Thesis-group-39th.xlsx") should follow this format:

### Required Columns:
- **Student ID Column**: Student Roll Number (can be in any column)
- **Group Name Column**: Group Name/Number (can be in any column)
- **Student Name Column**: Student Name (Optional, for reference only)

### Flexible Format:
The system automatically detects which columns contain Student IDs and Group Names, so your Excel file can have:
- Headers like: "Student_ID", "Roll", "ID", "Student" for student column
- Headers like: "Group_Name", "Group", "Group_No" for group column
- Columns can be in any order (Student ID first or Group Name first)

### Example Format:

| Student_ID | Group_Name | Student_Name    |
|------------|------------|-----------------|
| 2019001    | Group 1    | John Doe        |
| 2019002    | Group 1    | Jane Smith      |
| 2019003    | Group 1    | Bob Johnson     |
| 2019004    | Group 2    | Alice Brown     |
| 2019005    | Group 2    | Charlie Wilson  |
| 2019006    | Group 2    | Diana Davis     |

## Steps to Upload:

1. **Select Batch**: Choose the appropriate batch from the dropdown
2. **Prepare Excel File**: 
   - Ensure your file has Student IDs and Group Names/Numbers
   - Student IDs must match the roll numbers in the system
   - Group names can be any format (e.g., "1", "Group 1", "Group1" - all become "Group 1")
3. **Upload**: Click "Upload Excel" button and select your file
4. **Auto-Creation**: Groups will be created automatically if they don't exist
5. **Download Template**: Use "Download Template" to get the correct format with your students (if groups exist)

## Important Notes:

- **Student IDs**: Must exactly match the roll numbers in the external system
- **Group Names**: Must exactly match the group names you created (case-sensitive)
- **Maximum Students**: Each group can have maximum 3 students
- **Batch Validation**: Only students assigned to you in the selected batch will be processed
- **Overwrite**: Uploading will replace all existing group assignments for that batch

## Validation Rules:

- File must be .xlsx, .xls, or .csv format
- Maximum file size: 2MB
- Student ID must exist and be assigned to you
- Group name must exist for the selected batch
- No group can exceed 3 students
- No student can be assigned to multiple groups

## Error Handling:

The system will show detailed error messages if:
- Student ID not found
- Group name doesn't exist
- Group capacity exceeded
- File format issues

## Troubleshooting:

1. **"Student not found"**: Check if the Student ID matches exactly with the roll number
2. **"Group not found"**: Ensure group names match exactly (including case)
3. **"Group full"**: Check that no group has more than 3 students in your Excel file
4. **"No valid assignments"**: Verify your Excel format and data

## Sample File Structure:

```
Thesis-group-39th.xlsx
├── Sheet1
│   ├── Row 1: Headers (Student_ID, Group_Name, Student_Name)
│   ├── Row 2: 2019001, Group 1, John Doe
│   ├── Row 3: 2019002, Group 1, Jane Smith
│   └── ... (more student assignments)
```