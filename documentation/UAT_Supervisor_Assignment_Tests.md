# User Acceptance Testing (UAT) for Supervisor Assignment Algorithm

## Overview

This document provides comprehensive User Acceptance Testing (UAT) test cases for the Supervisor Assignment Algorithm in the Thesis Management System. The algorithm uses a lottery-based assignment system with rank priority and area of interest matching.

## Table of Contents

1. [Test Environment Setup](#test-environment-setup)
2. [Test Data Requirements](#test-data-requirements)
3. [Functional Test Cases](#functional-test-cases)
4. [Edge Case Test Scenarios](#edge-case-test-scenarios)
5. [Performance Test Cases](#performance-test-cases)
6. [Security Test Cases](#security-test-cases)
7. [Integration Test Cases](#integration-test-cases)
8. [User Interface Test Cases](#user-interface-test-cases)
9. [Regression Test Cases](#regression-test-cases)
10. [Test Execution Checklist](#test-execution-checklist)

---

## Test Environment Setup

### Prerequisites
- Laravel application running with database
- Test user accounts with advisor role
- Sample data for students, supervisors, groups, and areas of interest
- Browser for UI testing
- API testing tool (Postman/Insomnia) for API endpoints

### Test Data Setup Commands
```bash
# Run migrations
php artisan migrate

# Seed test data
php artisan db:seed

# Create test advisor user
php artisan tinker
User::create([
    'name' => 'Test Advisor',
    'email' => 'advisor@test.com',
    'password' => Hash::make('password'),
    'role' => 'advisor'
]);
```

---

## Test Data Requirements

### Required Test Data Structure

#### Supervisors
- **Professor A**: Professor, AI/ML expertise, capacity: 5, available slots: 3
- **Professor B**: Associate Professor, Security expertise, capacity: 4, available slots: 2
- **Professor C**: Assistant Professor, HCI expertise, capacity: 3, available slots: 1
- **Professor D**: Lecturer, Database expertise, capacity: 2, available slots: 0 (full)
- **Professor E**: Professor, AI/ML + Security expertise, capacity: 6, available slots: 4

#### Areas of Interest
- Artificial Intelligence & Machine Learning
- Cybersecurity & Network Security
- Human-Computer Interaction
- Database Systems & Data Mining
- Software Engineering

#### Groups
- **Group 1**: AI/ML area, 3 students, no supervisor
- **Group 2**: Security area, 2 students, no supervisor
- **Group 3**: HCI area, 4 students, no supervisor
- **Group 4**: Database area, 2 students, no supervisor
- **Group 5**: AI/ML area, 3 students, manually assigned to Professor A
- **Group 6**: Security area, 2 students, no area assigned
- **Group 7**: AI/ML area, 3 students, no supervisor
- **Group 8**: No area assigned, 2 students, no supervisor

---

## Functional Test Cases

### TC-F001: Basic Lottery Assignment
**Objective**: Verify that the lottery assignment correctly assigns supervisors to eligible groups

**Preconditions**:
- At least 3 unassigned groups with areas of interest
- At least 2 supervisors with available slots and matching expertise

**Test Steps**:
1. Login as advisor
2. Navigate to Supervisor Assignment page
3. Verify eligible groups are displayed
4. Click "Run Lottery Assignment"
5. Confirm assignment in popup
6. Verify assignment results

**Expected Results**:
- Groups are assigned to supervisors based on rank priority
- Higher rank supervisors (Professor) get priority over lower ranks
- Assignment respects supervisor capacity limits
- Success message displays assignment statistics
- Database is updated with assignments

**Test Data**:
```
Groups: Group 1 (AI/ML), Group 2 (Security), Group 3 (HCI)
Supervisors: Professor A (AI/ML, 3 slots), Professor B (Security, 2 slots), Professor C (HCI, 1 slot)
Expected: Group 1 → Professor A, Group 2 → Professor B, Group 3 → Professor C
```

---

### TC-F002: Rank Priority Assignment
**Objective**: Verify that supervisors are assigned based on rank priority when multiple supervisors have the same expertise

**Preconditions**:
- Multiple supervisors with same area of interest but different ranks
- Groups requiring that area of interest

**Test Steps**:
1. Setup: Professor (rank 1) and Assistant Professor (rank 3) both have AI/ML expertise
2. Create Group with AI/ML area of interest
3. Run lottery assignment
4. Verify assignment goes to higher rank supervisor

**Expected Results**:
- Group is assigned to Professor (higher rank) instead of Assistant Professor
- Assignment method shows "rank_priority"

**Test Data**:
```
Supervisors: 
- Professor E (AI/ML, rank 1, 4 slots)
- Assistant Professor F (AI/ML, rank 3, 3 slots)
Groups: Group 7 (AI/ML)
Expected: Group 7 → Professor E
```

---

### TC-F003: Random Selection Among Same Rank
**Objective**: Verify random selection when multiple supervisors have same rank and expertise

**Preconditions**:
- Multiple supervisors with same rank and area of interest
- Group requiring that expertise

**Test Steps**:
1. Setup: Two Professors with AI/ML expertise
2. Create Group with AI/ML area
3. Run lottery assignment multiple times (if possible to reset)
4. Verify random selection occurs

**Expected Results**:
- Assignment method shows "random_selection"
- Either supervisor could be assigned (randomness)

**Test Data**:
```
Supervisors: 
- Professor A (AI/ML, rank 1, 3 slots)
- Professor G (AI/ML, rank 1, 2 slots)
Groups: Group 1 (AI/ML)
Expected: Group 1 → Professor A OR Professor G (random)
```

---

### TC-F004: Manual Assignment
**Objective**: Verify manual supervisor assignment functionality

**Preconditions**:
- Unassigned group with area of interest
- Available supervisor with matching expertise

**Test Steps**:
1. Navigate to Supervisor Assignment page
2. Select unassigned group
3. Choose area of interest from dropdown
4. Select supervisor from filtered list
5. Click "Assign Supervisor"
6. Verify assignment

**Expected Results**:
- Supervisor dropdown filters by area of interest
- Assignment is marked as manual (is_manual_assignment = true)
- Success message confirms assignment
- Group shows assigned supervisor

**Test Data**:
```
Group: Group 2 (Security area)
Supervisor: Professor B (Security expertise, 2 slots)
Expected: Manual assignment successful, is_manual_assignment = true
```

---

### TC-F005: Unassign Supervisor
**Objective**: Verify supervisor unassignment functionality

**Preconditions**:
- Group with assigned supervisor

**Test Steps**:
1. Navigate to assigned group
2. Click "Unassign" button
3. Confirm unassignment in popup
4. Verify supervisor is removed

**Expected Results**:
- Supervisor is removed from group
- Supervisor's available slots increase by 1
- Group becomes eligible for lottery assignment again
- Success message confirms unassignment

---

### TC-F006: Preview Lottery Assignment
**Objective**: Verify preview functionality shows expected assignments without saving

**Preconditions**:
- Multiple unassigned groups with areas of interest
- Available supervisors

**Test Steps**:
1. Click "Preview Lottery Assignment"
2. Review preview results
3. Verify no actual assignments are made
4. Run actual lottery and compare results

**Expected Results**:
- Preview shows expected assignments
- No database changes occur during preview
- Actual lottery results match preview (deterministic algorithm)

---

### TC-F007: Batch Filtering
**Objective**: Verify batch filtering works for assignments

**Preconditions**:
- Groups from different batches
- Batch filter dropdown available

**Test Steps**:
1. Select specific batch from filter
2. Verify only groups from that batch are shown
3. Run lottery assignment
4. Verify only selected batch groups are assigned

**Expected Results**:
- Filter correctly shows only selected batch groups
- Assignment only affects filtered groups
- Other batch groups remain unaffected

---

## Edge Case Test Scenarios

### TC-E001: No Available Supervisors
**Objective**: Verify system behavior when no supervisors have available slots

**Preconditions**:
- All supervisors at maximum capacity
- Unassigned groups exist

**Test Steps**:
1. Ensure all supervisors are at capacity
2. Attempt lottery assignment
3. Verify error handling

**Expected Results**:
- Error message: "No supervisors have available slots"
- No assignments are made
- System remains stable

---

### TC-E002: No Matching Expertise
**Objective**: Verify behavior when no supervisors match group's area of interest

**Preconditions**:
- Group with area of interest not covered by any supervisor
- Available supervisors exist

**Test Steps**:
1. Create group with unique area of interest
2. Ensure no supervisors have that expertise
3. Run lottery assignment
4. Verify handling

**Expected Results**:
- Group remains unassigned
- Statistics show "no_matches" count
- Error message explains no matching supervisors found

---

### TC-E003: Supervisor Capacity Exceeded
**Objective**: Verify system prevents over-assignment of supervisors

**Preconditions**:
- Supervisor with 1 available slot
- Multiple groups requiring that supervisor's expertise

**Test Steps**:
1. Setup supervisor with limited capacity
2. Create multiple groups needing that expertise
3. Run lottery assignment
4. Verify capacity limits are respected

**Expected Results**:
- Only one group gets assigned to the supervisor
- Supervisor's available slots become 0
- Other groups remain unassigned or get alternative supervisors

---

### TC-E004: Group Without Area of Interest
**Objective**: Verify groups without areas of interest are handled correctly

**Preconditions**:
- Group with no area_of_interest_id set

**Test Steps**:
1. Create group without area of interest
2. Attempt lottery assignment
3. Verify group is skipped

**Expected Results**:
- Group is not eligible for lottery assignment
- Group appears in "unassigned" list with reason
- No error occurs during assignment process

---

### TC-E005: Inactive Supervisor
**Objective**: Verify inactive supervisors are not considered for assignment

**Preconditions**:
- Supervisor marked as inactive (is_active = false)
- Group requiring that supervisor's expertise

**Test Steps**:
1. Set supervisor as inactive
2. Run lottery assignment
3. Verify inactive supervisor is not assigned

**Expected Results**:
- Inactive supervisor is not considered
- Group gets assigned to alternative supervisor or remains unassigned
- No assignments to inactive supervisors

---

### TC-E006: Empty Groups (No Students)
**Objective**: Verify groups without students are not assigned supervisors

**Preconditions**:
- Group with no students enrolled

**Test Steps**:
1. Create group with no students
2. Run lottery assignment
3. Verify group is ignored

**Expected Results**:
- Empty groups are not eligible for assignment
- Only groups with students are processed
- No errors occur

---

### TC-E007: Concurrent Assignment Attempts
**Objective**: Verify system handles concurrent assignment attempts correctly

**Preconditions**:
- Multiple advisors attempting assignments simultaneously

**Test Steps**:
1. Have two advisors attempt lottery assignment at same time
2. Verify database consistency
3. Check for race conditions

**Expected Results**:
- Database transactions prevent conflicts
- No supervisor is over-assigned
- Both assignments complete successfully or fail gracefully

---

## Performance Test Cases

### TC-P001: Large Dataset Assignment
**Objective**: Verify system performance with large number of groups and supervisors

**Test Data**:
- 1000 groups
- 100 supervisors
- Various areas of interest

**Test Steps**:
1. Create large dataset
2. Run lottery assignment
3. Measure execution time
4. Verify memory usage

**Expected Results**:
- Assignment completes within 30 seconds
- Memory usage remains reasonable (<500MB)
- All eligible groups are processed
- Database performance is acceptable

---

### TC-P002: Preview Performance
**Objective**: Verify preview functionality performance with large datasets

**Test Steps**:
1. Use large dataset from TC-P001
2. Run preview assignment
3. Measure response time
4. Verify accuracy

**Expected Results**:
- Preview completes within 10 seconds
- Results are accurate
- No database modifications occur
- UI remains responsive

---

### TC-P003: Concurrent User Load
**Objective**: Test system behavior under multiple concurrent users

**Test Steps**:
1. Simulate 10 advisors accessing assignment page simultaneously
2. Have 5 advisors run assignments concurrently
3. Monitor system performance
4. Verify data integrity

**Expected Results**:
- System remains responsive
- No data corruption occurs
- All assignments are valid
- Database locks work correctly

---

## Security Test Cases

### TC-S001: Authorization Check
**Objective**: Verify only authorized advisors can access assignment functionality

**Test Steps**:
1. Attempt to access assignment page without login
2. Login as student user and attempt access
3. Login as advisor and verify access

**Expected Results**:
- Unauthenticated users are redirected to login
- Non-advisor users receive authorization error
- Advisors can access functionality

---

### TC-S002: Cross-Advisor Data Access
**Objective**: Verify advisors can only manage their own groups

**Test Steps**:
1. Login as Advisor A
2. Attempt to assign supervisor to Advisor B's group
3. Verify access is denied

**Expected Results**:
- Advisor A cannot see Advisor B's groups
- API calls with other advisor's group IDs fail
- Error messages don't reveal sensitive information

---

### TC-S003: Input Validation
**Objective**: Verify all inputs are properly validated

**Test Steps**:
1. Submit invalid group IDs
2. Submit invalid supervisor IDs
3. Submit malformed requests
4. Test SQL injection attempts

**Expected Results**:
- Invalid inputs are rejected
- Appropriate error messages are shown
- No SQL injection vulnerabilities
- System remains stable

---

### TC-S004: CSRF Protection
**Objective**: Verify CSRF protection is implemented

**Test Steps**:
1. Attempt assignment without CSRF token
2. Use invalid CSRF token
3. Verify protection is active

**Expected Results**:
- Requests without valid CSRF tokens are rejected
- Error messages indicate CSRF failure
- Legitimate requests with tokens succeed

---

## Integration Test Cases

### TC-I001: Database Consistency
**Objective**: Verify database remains consistent after assignments

**Test Steps**:
1. Run multiple assignment operations
2. Check foreign key constraints
3. Verify data integrity
4. Test rollback scenarios

**Expected Results**:
- All foreign keys are valid
- No orphaned records exist
- Constraints are enforced
- Rollbacks work correctly

---

### TC-I002: Email Notifications (if implemented)
**Objective**: Verify email notifications are sent correctly

**Test Steps**:
1. Run assignment
2. Check if notifications are queued
3. Verify email content
4. Test notification failures

**Expected Results**:
- Notifications are sent to assigned supervisors
- Email content is accurate
- Failed notifications are handled gracefully

---

### TC-I003: Audit Trail
**Objective**: Verify assignment actions are logged

**Test Steps**:
1. Perform various assignment operations
2. Check application logs
3. Verify audit information

**Expected Results**:
- All assignment actions are logged
- Logs contain sufficient detail
- User actions are traceable
- Timestamps are accurate

---

## User Interface Test Cases

### TC-UI001: Responsive Design
**Objective**: Verify UI works on different screen sizes

**Test Steps**:
1. Test on desktop (1920x1080)
2. Test on tablet (768x1024)
3. Test on mobile (375x667)
4. Verify all functionality is accessible

**Expected Results**:
- UI adapts to different screen sizes
- All buttons and forms are usable
- Text is readable on all devices
- No horizontal scrolling required

---

### TC-UI002: Loading States
**Objective**: Verify loading indicators work correctly

**Test Steps**:
1. Click "Run Lottery Assignment"
2. Observe loading indicators
3. Test preview functionality loading
4. Test supervisor dropdown loading

**Expected Results**:
- Loading indicators appear during operations
- Users cannot trigger duplicate operations
- Loading states are cleared after completion
- Error states are handled properly

---

### TC-UI003: Form Validation
**Objective**: Verify client-side form validation

**Test Steps**:
1. Submit forms with missing required fields
2. Test invalid input formats
3. Verify validation messages
4. Test form reset functionality

**Expected Results**:
- Required field validation works
- Validation messages are clear
- Forms prevent invalid submissions
- Reset functionality works correctly

---

### TC-UI004: Data Display Accuracy
**Objective**: Verify all data is displayed correctly

**Test Steps**:
1. Compare displayed data with database
2. Verify statistics calculations
3. Check supervisor availability counts
4. Verify group information accuracy

**Expected Results**:
- All displayed data matches database
- Statistics are calculated correctly
- Real-time updates work properly
- No data inconsistencies

---

## Regression Test Cases

### TC-R001: Previous Assignment Preservation
**Objective**: Verify existing assignments are not affected by new lottery runs

**Test Steps**:
1. Create initial assignments
2. Add new unassigned groups
3. Run lottery assignment
4. Verify existing assignments unchanged

**Expected Results**:
- Previously assigned groups remain unchanged
- Only new eligible groups are assigned
- Manual assignments are preserved
- Assignment history is maintained

---

### TC-R002: Configuration Changes
**Objective**: Verify system works after configuration changes

**Test Steps**:
1. Change supervisor capacities
2. Modify areas of interest
3. Update supervisor expertise
4. Run assignments and verify behavior

**Expected Results**:
- System adapts to configuration changes
- New constraints are respected
- No legacy data issues occur
- Assignments reflect current configuration

---

### TC-R003: Database Schema Changes
**Objective**: Verify system works after database migrations

**Test Steps**:
1. Run database migrations
2. Test assignment functionality
3. Verify data integrity
4. Check backward compatibility

**Expected Results**:
- All functionality works after migrations
- Data is preserved correctly
- No breaking changes occur
- Performance is maintained

---

## Test Execution Checklist

### Pre-Test Setup
- [ ] Test environment is set up and accessible
- [ ] Test data is loaded and verified
- [ ] All required user accounts are created
- [ ] Database is in clean state
- [ ] Application is running without errors

### Test Execution
- [ ] All functional test cases executed
- [ ] Edge cases tested thoroughly
- [ ] Performance benchmarks met
- [ ] Security tests passed
- [ ] Integration tests completed
- [ ] UI tests verified on multiple browsers
- [ ] Regression tests confirm no breaking changes

### Post-Test Verification
- [ ] Test results documented
- [ ] Defects logged and prioritized
- [ ] Database state verified
- [ ] Performance metrics recorded
- [ ] Security scan completed
- [ ] User acceptance criteria met

---

## Test Result Template

### Test Case: [TC-ID]
**Date**: [Date]  
**Tester**: [Name]  
**Environment**: [Environment Details]

**Test Steps Executed**:
1. [Step 1 - Result]
2. [Step 2 - Result]
3. [Step 3 - Result]

**Actual Results**:
[Description of actual results]

**Status**: [PASS/FAIL/BLOCKED]

**Defects Found**:
- [Defect 1 - Severity]
- [Defect 2 - Severity]

**Notes**:
[Additional observations]

---

## Acceptance Criteria

### Must Have (Critical)
- [ ] All eligible groups can be assigned supervisors
- [ ] Rank priority is respected in assignments
- [ ] Supervisor capacity limits are enforced
- [ ] Manual assignments work correctly
- [ ] System prevents invalid assignments
- [ ] Data integrity is maintained

### Should Have (Important)
- [ ] Preview functionality works accurately
- [ ] Batch filtering operates correctly
- [ ] Performance meets requirements
- [ ] UI is responsive and user-friendly
- [ ] Error messages are clear and helpful
- [ ] Audit trail is maintained

### Could Have (Nice to Have)
- [ ] Advanced filtering options
- [ ] Export functionality for assignments
- [ ] Detailed assignment analytics
- [ ] Email notifications
- [ ] Assignment history tracking
- [ ] Bulk operations support

---

## Risk Assessment

### High Risk Areas
1. **Concurrent Access**: Multiple advisors modifying assignments simultaneously
2. **Data Integrity**: Ensuring supervisor capacity limits are never exceeded
3. **Performance**: Large datasets causing timeouts or memory issues
4. **Security**: Unauthorized access to assignment functionality

### Mitigation Strategies
1. **Database Transactions**: Use proper locking and transactions
2. **Input Validation**: Comprehensive server-side validation
3. **Performance Testing**: Regular load testing with realistic data
4. **Security Reviews**: Regular security audits and penetration testing

---

## Test Environment Requirements

### Hardware Requirements
- **CPU**: Minimum 4 cores, 2.5GHz
- **RAM**: Minimum 8GB
- **Storage**: Minimum 100GB SSD
- **Network**: Stable internet connection

### Software Requirements
- **OS**: Windows 10/11, macOS 10.15+, or Ubuntu 20.04+
- **Browser**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **PHP**: Version 8.1+
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Apache 2.4+ or Nginx 1.18+

### Test Tools
- **API Testing**: Postman or Insomnia
- **Database Tool**: phpMyAdmin, MySQL Workbench, or pgAdmin
- **Performance Testing**: Apache JMeter or LoadRunner
- **Browser Testing**: Selenium WebDriver (optional)

---

## Conclusion

This comprehensive UAT test suite covers all aspects of the Supervisor Assignment Algorithm, from basic functionality to edge cases, performance, security, and user experience. Regular execution of these tests will ensure the system meets user requirements and maintains high quality standards.

The test cases should be executed in the order presented, starting with functional tests, followed by edge cases, and then specialized testing areas. Any failures should be documented, analyzed, and resolved before proceeding to production deployment.

**Document Version**: 1.0  
**Last Updated**: January 2025  
**Prepared By**: QA Team  
**Approved By**: Project Manager