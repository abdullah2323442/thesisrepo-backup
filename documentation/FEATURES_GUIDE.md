# Thesis Management System - Features Guide

This document provides comprehensive documentation of all features in the Thesis Management System.

Last Updated: January 2025

---

## Table of Contents

1. [User Roles & Access](#user-roles--access)
2. [Authentication System](#authentication-system)
3. [Admin Features](#admin-features)
4. [Advisor Features](#advisor-features)
5. [Supervisor Features](#supervisor-features)
6. [Student Features](#student-features)
7. [Meeting Management](#meeting-management)
8. [Group Management](#group-management)
9. [Supervisor Assignment Algorithm](#supervisor-assignment-algorithm)
10. [Excel Integration](#excel-integration)
11. [Performance Monitoring](#performance-monitoring)
12. [Security Features](#security-features)

---

## User Roles & Access

### Available Roles

| Role | Description | Access Level |
|------|-------------|--------------|
| **Admin** | System administrator | Full system control, user management, performance monitoring |
| **Advisor** | Faculty advisor | Student groups, supervisor assignment, Excel operations |
| **Supervisor** | Thesis supervisor | Assigned groups, meeting management, progress tracking |
| **Student** | Thesis student | Dashboard, group info, meetings, PDF reports |
| **Teacher** | Multi-role faculty | Can act as advisor and/or supervisor |

### Role-Based Dashboards

- **Admin Dashboard**: `/admin/dashboard` - System statistics, user management
- **Advisor Dashboard**: `/advisor/dashboard` - Group management, student lists
- **Supervisor Dashboard**: `/supervisor/dashboard` - Assigned groups, meetings
- **Student Dashboard**: `/student/dashboard` - Group info, supervisor details
- **Teacher Dashboard**: `/teacher/dashboard` - Multi-role access hub

---

## Authentication System

### Multi-Factor Authentication

1. **Local Authentication**
   - Email and password for admin users
   - Secure password hashing with bcrypt
   - Session management

2. **External API Authentication**
   - Student login via university API
   - Teacher login via university API
   - Automatic user creation on first login

3. **Security Features**
   - Rate limiting (5 attempts per minute)
   - CSRF protection on all forms
   - Secure session cookies
   - Password reset functionality

### Logout Functionality

- Consistent logout buttons across all panels
- Secure session termination
- CSRF-protected logout forms
- Automatic redirect to login page

---

## Admin Features

### 1. Group Management

#### Creating Groups
- Select batch and enter group name
- Optional area of interest assignment
- Optional supervisor pre-assignment
- Support for up to 4 students per group
- Automatic advisor detection from student assignment

#### Deleting Groups
- Single group deletion with confirmation
- Automatic group renumbering after deletion
- Cannot delete groups with students
- Bulk deletion support
- Complete audit trail

#### Student Assignment
- Assign students from any batch
- Automatic advisor detection
- Same-advisor validation per group
- Remove students from groups
- Cross-batch assignment support

### 2. Area of Interest Management

- Create new research areas
- Edit existing areas
- Bulk creation support
- Active/inactive status management
- Delete unused areas

### 3. Supervisor Management

- Sync supervisors from external API
- Edit supervisor details (designation, rank, thesis limit)
- Bulk update thesis limits
- Toggle active/inactive status
- Refresh individual supervisor data

### 4. Batch Management

- Sync batches from external API
- Toggle batch status (active/inactive)
- Bulk actions on multiple batches
- Compare local vs API data
- Edit batch details

### 5. Performance Monitoring

- System health dashboard
- Real-time metrics
- API performance tracking
- Security monitoring
- Database statistics
- Export performance reports

---

## Advisor Features

### 1. Student Management

- View all assigned students
- Filter by batch and status
- Refresh student data from API
- View individual student details
- Export student lists

### 2. Group Creation & Management

#### Manual Group Creation
- Create multiple groups at once
- Set group capacity (max 3 students)
- Assign students to groups
- Assign areas of interest
- Remove students from groups

#### Excel Upload
- Bulk group assignment via Excel
- Automatic group creation
- Random group assignment for fairness
- Download template with current students
- Validation and error reporting

### 3. Supervisor Assignment

#### Manual Assignment
- Select group and area of interest
- Choose from available supervisors
- View supervisor capacity
- Unassign supervisors

#### Lottery Assignment
- Three modes: AOI-based, Ranking-based, Combined
- Preview assignments before applying
- Automatic fair distribution
- Respects capacity limits
- Comprehensive statistics

---

## Supervisor Features

### 1. Dashboard

- View assigned thesis groups
- Group member details
- Meeting statistics
- Quick actions menu

### 2. Group Management

- View all assigned groups
- Access student information
- Track group progress
- Contact group members

### 3. Meeting Management

- Schedule meetings with groups
- Record meeting details
- Track attendance
- Document discussion topics
- Record outcomes
- Edit past meetings
- Filter by date and group

---

## Student Features

### 1. Enhanced Dashboard

#### Group Information
- View assigned group name
- See all group members
- Current user highlighting
- Batch information
- Group capacity status

#### Area of Interest
- View assigned research areas
- Multiple areas support
- Area descriptions
- Visual indicators

#### Supervisor Details
- Supervisor name and designation
- Department information
- Contact email
- Professional profile

#### Statistics Cards
- Batch number
- Department info
- Group status
- Supervisor status

### 2. Meeting Features

- View all group meetings
- Check attendance records
- Review discussion topics
- See meeting outcomes
- Download meetings as PDF
- Meeting history tracking

### 3. Group Notification System

#### Real-time Notifications
- **Instant Delivery**: All group members receive notifications immediately
- **Smart Targeting**: Only relevant group members are notified
- **Multiple Channels**: Database notifications with real-time UI updates

#### Notification Types
- **🟣 Report Assigned** (Purple icon): New reports created by supervisor
- **🔵 Report Comment** (Blue icon): Supervisor adds comments to reports  
- **🟠 Report Updated** (Orange icon): Supervisor modifies existing reports

#### Notification Features
- **Change Detection**: Only sends update notifications for meaningful changes
- **Rich Content**: Includes supervisor messages, change details, and context
- **Student ID Flexibility**: Works with both `roll` and `student_id` formats
- **Group Coverage**: Ensures all group members receive notifications

#### User Interface
- **Notification Bell**: Real-time dropdown with unread count badge
- **Notification Center**: Full history with pagination and filtering
- **Mark as Read**: Individual and bulk read status management
- **Visual Indicators**: Color-coded icons and unread highlighting

#### Technical Implementation
- **Database Storage**: Persistent notification storage
- **Transaction Safety**: All notifications sent within database transactions
- **Error Handling**: Graceful failure with comprehensive logging
- **Performance**: Efficient querying and caching

### 4. Report Approval Status Display

#### Visual Status Indicators
- **Approval Badges**: Green "✓ Approved" badges for approved final reports
- **Status Integration**: Seamlessly integrated into report headers and listings
- **Color Coding**: Consistent green color scheme for approved status

#### Congratulations Section
- **Celebration Message**: 🎉 Congratulations message for approved final reports
- **Approval Details**: Shows approval date, time, and approving supervisor
- **Public Access**: Direct link to public thesis page
- **Share Functionality**: One-click copy link feature with visual feedback

#### Student Experience
- **Report Index**: Approval status visible in report listings
- **Report Details**: Comprehensive approval information on detail pages
- **Public Visibility**: Easy access to published thesis
- **Achievement Recognition**: Clear acknowledgment of successful completion

#### Technical Features
- **Smart Display**: Only shows for final reports that are approved
- **Real-time Updates**: Status updates immediately upon supervisor approval
- **Link Generation**: Automatic public thesis URL generation
- **Clipboard Integration**: Modern browser clipboard API for link sharing

### 5. PDF Reports

- Generate meeting reports
- Professional formatting
- Complete attendance records
- Downloadable format
- Print-ready layout

---

## Meeting Management

### For Supervisors

#### Creating Meetings
1. Select group from assigned groups
2. Set meeting date
3. Add discussion topics
4. Record outcomes
5. Mark student attendance
6. Save meeting record

#### Managing Meetings
- Edit existing meetings
- Update attendance
- Modify discussion topics
- Change outcomes
- Filter by date range
- Search by group

### For Students

#### Viewing Meetings
- Access all group meetings
- Check personal attendance
- Review discussion points
- See meeting outcomes
- Track meeting history

#### PDF Reports
- Download comprehensive reports
- All meetings in one document
- Attendance summary
- Professional formatting
- Share with advisors

### Meeting Features

- **Attendance Tracking**: Mark present/absent for each student
- **Topic Documentation**: Record what was discussed
- **Outcome Recording**: Document decisions and next steps
- **History Preservation**: Complete meeting archive
- **Access Control**: Only assigned supervisors can create/edit

---

## Group Management

### Admin Group Creation

#### Enhanced Features
- Create groups with pre-assigned areas
- Auto-detect advisor from students
- Support 4 students (vs 3 for advisors)
- Track creation source
- Audit trail with timestamps

#### Advisor Auto-Detection
1. Admin creates group without advisor
2. First student assignment triggers detection
3. System queries student's advisor from API
4. Advisor automatically assigned to group
5. Subsequent students must have same advisor

### Group Deletion

#### Automatic Renumbering
- Groups renumbered sequentially after deletion
- Example: Delete Group 3 → Group 4 becomes Group 3
- Maintains clean numbering sequence
- Works across all batches

#### Safety Features
- Cannot delete groups with students
- Confirmation modal required
- Transaction-safe operations
- Complete audit logging

### Multiple Areas of Interest

#### Implementation
- Groups can have multiple research areas
- Checkbox selection interface
- Badge display for all areas
- Backward compatible with single area

#### Benefits
- Interdisciplinary project support
- Better supervisor matching
- Flexible research scope
- Cross-domain collaboration

---

## Supervisor Assignment Algorithm

> **📘 For complete algorithm documentation with flowcharts, see [SUPERVISOR_ASSIGNMENT_ALGORITHM.md](SUPERVISOR_ASSIGNMENT_ALGORITHM.md)**

### Overview

The Supervisor Assignment Algorithm is a sophisticated system that automatically assigns thesis supervisors to student groups based on various criteria including Area of Interest (AOI), academic rank, and capacity constraints.

### Three Assignment Modes

#### 1. AOI-Based Lottery
- **Purpose**: Match supervisors based on research expertise
- **Selection**: Random from matching supervisors
- **Rotation**: Excludes last assigned if alternatives exist
- **Result**: Different on each run (true randomization)

#### 2. Ranking-Based Lottery
- **Purpose**: Distribute by academic seniority
- **Selection**: Round-robin by rank (Professor → Associate → Assistant → Lecturer)
- **Fairness**: Nobody gets 2nd group until all have 1
- **Result**: Deterministic (same input = same output)

#### 3. Combined Mode
- **Purpose**: Balance expertise with fairness
- **Selection**: AOI match first, rank as tiebreaker
- **Ultra-Fair**: Area-specific round-robin
- **Smart**: Avoids consecutive assignments within AOI

### Key Features

- **Capacity Management**: Never exceeds supervisor thesis limits
- **Fair Distribution**: Ensures equitable allocation
- **Smart Rotation**: Avoids consecutive assignments when possible
- **Preview Mode**: Test assignments without saving to database
- **Audit Trail**: Complete assignment history tracking
- **Transaction Safety**: All operations wrapped in database transactions

### Assignment Workflow

1. **Select Groups**: Choose eligible unassigned groups with areas
2. **Choose Mode**: Select AOI, Ranking, or Combined mode
3. **Preview** (Optional): Test assignment without persistence
4. **Run Assignment**: Execute the lottery algorithm
5. **Review Results**: Check statistics and unassigned groups

### Visual Flowcharts

The complete algorithm documentation includes detailed flowcharts for:
- Main assignment process flow
- AOI-based assignment logic
- Ranking-based round-robin flow
- Combined mode decision tree

**[View Complete Algorithm Documentation →](SUPERVISOR_ASSIGNMENT_ALGORITHM.md)**

---

## Excel Integration

### Upload Features

#### File Format Support
- Excel files (.xlsx, .xls)
- CSV files (.csv)
- Flexible column detection
- Auto-identifies Student ID and Group columns

#### Random Group Assignment
- Prevents bias in group numbering
- Excel determines groupings only
- System randomly assigns actual numbers
- Maintains sorted display

#### Validation
- Student ID verification
- Group capacity checks
- Batch validation
- Duplicate detection
- Comprehensive error messages

### Download Features

#### Template Generation
- Current student list
- Existing group assignments
- Proper column headers
- Ready for editing

#### Export Options
- Student lists
- Group assignments
- Meeting reports
- Performance data

---

## Performance Monitoring

### Dashboard Components

#### System Health
- Overall status indicator
- Component health checks
- Real-time monitoring
- Alert thresholds

#### Key Metrics
- PHP version and configuration
- Memory usage
- Database performance
- API response times
- User statistics

#### Detailed Tabs
- **Database**: Connection, queries, table stats
- **API**: External API performance
- **Security**: Failed logins, rate limiting
- **System**: Environment info, PHP settings
- **Errors**: Error tracking and trends

### Features

- **Auto-Refresh**: Updates every 5 minutes
- **Manual Refresh**: On-demand updates
- **Export Reports**: JSON format
- **System Tests**: Component testing
- **Cache Management**: Clear performance cache

---

## Security Features

### Rate Limiting

#### Protected Endpoints
- Login: 5 attempts/minute
- Student Dashboard: 60 requests/minute
- Advisor Dashboard: 60 requests/minute
- API Sync: 10 requests/5 minutes
- All other endpoints configured

#### Configuration
- Environment-based limits
- Customizable per endpoint
- IP-based throttling
- Automatic reset after decay

### Authentication Security

- **Password Hashing**: Bcrypt with salt
- **Session Security**: Regeneration after login
- **CSRF Protection**: All forms protected
- **HTTPS Enforcement**: Production only
- **Secure Cookies**: HTTPOnly, Secure flags

### Access Control

- **Role-Based Access**: Middleware protection
- **Route Guards**: Authentication required
- **API Authentication**: Token-based for external
- **Audit Logging**: All critical actions logged

---

## Additional Features

### Responsive Design
- Mobile-optimized interfaces
- Touch-friendly controls
- Adaptive layouts
- Cross-browser compatibility

### Professional UI
- Admin-style layouts
- Consistent theming
- Color-coded panels
- Intuitive navigation

### Data Integrity
- Foreign key constraints
- Transaction safety
- Validation rules
- Error handling

### Audit Trail
- User action logging
- Timestamp tracking
- Change history
- Security events

---

## Future Enhancements

### Planned Features
- Thesis document management
- Progress tracking system
- Notification system
- Advanced analytics
- Mobile applications
- API for third-party integration

### Under Consideration
- Video meeting integration
- Plagiarism checking
- Peer review system
- Research collaboration tools
- Publication tracking

---

## Support & Help

### Getting Help
- Check this documentation
- Review error messages
- Contact system administrator
- Submit support tickets

### Common Issues
- Login problems → Check credentials and API
- Group assignment → Verify student batch
- Excel upload → Check file format
- Performance → Clear cache and refresh

---

**Document Version**: 2.0  
**Last Updated**: January 2025  
**System Version**: 1.0.0