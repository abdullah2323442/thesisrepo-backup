<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meetings Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }
        
        .university-name {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .department-name {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .info-row {
            margin-bottom: 8px;
            overflow: hidden;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
            float: left;
        }
        
        .info-value {
            margin-left: 150px;
            word-wrap: break-word;
        }
        
        .meetings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .meetings-table th,
        .meetings-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        .meetings-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        
        .date-column {
            width: 12%;
        }
        
        .topics-column {
            width: 25%;
        }
        
        .outcomes-column {
            width: 25%;
        }
        
        .student-signature-column {
            width: 20%;
        }
        
        .supervisor-signature-column {
            width: 10%;
        }
        
        .attendance-column {
            width: 8%;
            text-align: center;
        }
        
        .signature-lines {
            margin-top: 5px;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 8px;
            height: 20px;
            font-size: 5px;
            padding-top: 2px;
        }
        
        .attendance-mark {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }
        
        .present {
            color: green;
        }
        
        .absent {
            color: red;
        }
        
        .no-meetings {
            text-align: center;
            padding: 40px;
            font-style: italic;
            color: #666;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        @if(file_exists($logoPath))
            <img src="{{ $logoPath }}" alt="University Logo" class="logo">
        @endif
        <div class="university-name">{{ $universityName }}</div>
        <div class="department-name">{{ $departmentName }}</div>
        <div style="margin-top: 15px; font-size: 16px; font-weight: bold;">
            Thesis Meetings Report
        </div>
    </div>

    <!-- Information Section -->
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Group Name:</div>
            <div class="info-value">{{ $groupName }}</div>
        </div>
        
        @if($supervisor)
        <div class="info-row">
            <div class="info-label">Supervisor Name:</div>
            <div class="info-value">{{ $supervisor['name'] }}</div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Supervisor Designation:</div>
            <div class="info-value">{{ $supervisor['designation'] ?? 'N/A' }}</div>
        </div>
        @endif
        
        @if($areaOfInterest)
        <div class="info-row">
            <div class="info-label">Area of Interest:</div>
            <div class="info-value">{{ $areaOfInterest['name'] }}</div>
        </div>
        @endif
        
        <div class="info-row">
            <div class="info-label">Student IDs:</div>
            <div class="info-value">{{ $studentIds }}</div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Report Generated:</div>
            <div class="info-value">{{ $generatedDate }}</div>
        </div>
    </div>

    <!-- Meetings Table -->
    @if($meetings->count() > 0)
        <table class="meetings-table">
            <thead>
                <tr>
                    <th class="date-column">Date</th>
                    <th class="topics-column">Discussed Topics</th>
                    <th class="outcomes-column">Outcomes</th>
                    <th class="student-signature-column">Students Signature</th>
                    <th class="supervisor-signature-column">Supervisor Signature</th>
                    <!-- <th class="attendance-column">Attendance</th> -->
                </tr>
            </thead>
            <tbody>
                @foreach($meetings as $meeting)
                <tr>
                    <td class="date-column">
                        {{ $meeting->meeting_date->format('M d, Y') }}
                    </td>
                    <td class="topics-column">
                        {{ $meeting->discussed_topics ?? 'N/A' }}
                    </td>
                    <td class="outcomes-column">
                        {{ $meeting->outcomes ?? 'N/A' }}
                    </td>
                    <td class="student-signature-column">
                        <div class="signature-lines">
                            @foreach($groupMembers as $member)
                                <div class="signature-line">
                                    <!-- {{ $member['name'] }} ({{ $member['student_id'] }}): -->
                                </div>
                            @endforeach
                        </div>
                    </td>
                    <td class="supervisor-signature-column">
                        <div style="height: {{ $groupMembers->count() * 28 }}px; border-bottom: 1px solid #333; margin-top: 5px;"></div>
                    </td>
                    <!-- <td class="attendance-column">
                        @php
                            $attendanceMap = [];
                            foreach($meeting->attendances as $attendance) {
                                $attendanceMap[$attendance->groupStudent->student_id] = $attendance->present;
                            }
                        @endphp
                        
                        @foreach($groupMembers as $member)
                            <div class="attendance-mark" style="margin-bottom: 8px; height: 20px; line-height: 20px;">
                                @if(isset($attendanceMap[$member['student_id']]))
                                    @if($attendanceMap[$member['student_id']])
                                        <span class="present">✓</span>
                                    @else
                                        <span class="absent">✗</span>
                                    @endif
                                @else
                                    <span style="color: #ccc;">-</span>
                                @endif
                            </div>
                        @endforeach
                    </td> -->
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-meetings">
            <h3>No Meetings Conducted Yet</h3>
            <p>No meetings have been recorded for this group. This report will be updated as meetings are conducted and recorded by the supervisor.</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This report was generated automatically from the thesis management system.</p>
        <p>For any discrepancies, please contact your supervisor or the department office.</p>
    </div>
</body>
</html>