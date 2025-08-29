<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\AreaOfInterest;
use App\Models\Supervisor;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelPdf\Facades\Pdf;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check if user logged in via external API (student)
        if ($user && $user->login_type === 'student' && session()->has('external_student_data')) {
            // Use external API data
            $userType = 'external';
            $userData = session('external_student_data');
            $studentId = $userData['Roll'] ?? $userData['roll'] ?? null;
        } else {
            // Use model data (local database)
            $userType = 'model';
            $userData = $user;
            $studentId = $user->roll ?? $user->student_id ?? null;
        }
        
        // Get student's group information
        $groupInfo = $this->getStudentGroupInfo($studentId);
        
        return view('student.dashboard', [
            'user' => $userData,
            'userType' => $userType,
            'groupInfo' => $groupInfo
        ]);
    }
    
    /**
     * Show meetings page
     */
    public function meetings()
    {
        $user = Auth::user();
        
        // Get student ID
        if ($user && $user->login_type === 'student' && session()->has('external_student_data')) {
            $userData = session('external_student_data');
            $studentId = $userData['Roll'] ?? $userData['roll'] ?? null;
        } else {
            $userData = $user;
            $studentId = $user->roll ?? $user->student_id ?? null;
        }

        // Get student's group information
        $groupInfo = $this->getStudentGroupInfo($studentId);
        
        // Get meetings if student has a group
        $meetings = collect();
        if ($groupInfo && $groupInfo['hasGroup']) {
            $meetings = Meeting::with(['attendances.groupStudent'])
                ->where('group_id', $groupInfo['group']['id'])
                ->orderBy('meeting_date', 'desc')
                ->get();
        }

        return view('student.meetings', [
            'groupInfo' => $groupInfo,
            'meetings' => $meetings
        ]);
    }
    
    /**
     * Get student's group information including group members, area of interest, and supervisor
     */
    private function getStudentGroupInfo($studentId)
    {
        if (!$studentId) {
            return null;
        }
        
        // Find the group student record
        $groupStudent = GroupStudent::where('student_id', $studentId)->first();
        
        if (!$groupStudent) {
            return [
                'hasGroup' => false,
                'message' => 'You are not assigned to any group yet.'
            ];
        }
        
        // Get the group with all related information
        $group = Group::with(['students', 'matchedAreaOfInterest', 'supervisor', 'advisor'])
                     ->find($groupStudent->group_id);
        
        if (!$group) {
            return [
                'hasGroup' => false,
                'message' => 'Group information not found.'
            ];
        }
        
        // Get all group members
        $groupMembers = $group->students->map(function ($student) use ($studentId) {
            return [
                'student_id' => $student->student_id,
                'name' => $student->student_name,
                'email' => $student->student_email,
                'is_current_user' => $student->student_id === $studentId
            ];
        });
        
        $area = $group->matchedAreaOfInterest;

        return [
            'hasGroup' => true,
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'batch_number' => $group->batch_number,
                'max_students' => $group->max_students,
                'student_count' => $group->students->count(),
                'created_at' => $group->created_at
            ],
            'members' => $groupMembers,
            'areaOfInterest' => $area ? [
                'id' => $area->id,
                'name' => $area->name,
                'description' => $area->description
            ] : null,
            'supervisor' => $group->supervisor ? [
                'id' => $group->supervisor->id,
                'name' => $group->supervisor->fullname,
                'email' => $group->supervisor->email,
                'designation' => $group->supervisor->designation,
                'department' => $group->supervisor->department,
                'thesis_limit' => $group->supervisor->thesis_limit
            ] : null,
            'advisor' => $group->advisor ? [
                'id' => $group->advisor->id,
                'name' => $group->advisor->name,
                'email' => $group->advisor->email
            ] : null
        ];
    }
    
    /**
     * Fetch student data from external API (optional)
     */
    private function fetchStudentDataFromAPI($user)
    {
        try {
            $studentApiUrl = config('app.external_api_student_url');
            
            if (!$studentApiUrl) {
                return null;
            }
            
            $response = Http::timeout(30)->get($studentApiUrl, [
                'student_id' => $user->student_id ?? $user->id,
                // Add other required parameters
            ]);
            
            if ($response->successful()) {
                $studentData = $response->json();
                
                // Store in session for future use
                session(['external_student_data' => $studentData]);
                
                return $studentData;
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch student data from external API:', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
        }
        
        return null;
    }

    /**
     * Download meetings PDF report
     */
    public function downloadMeetingsPdf()
    {
        try {
            $user = Auth::user();
            
            // Get student ID
            if ($user && $user->login_type === 'student' && session()->has('external_student_data')) {
                $userData = session('external_student_data');
                $studentId = $userData['Roll'] ?? $userData['roll'] ?? null;
                $studentName = $userData['Name'] ?? 'Student';
            } else {
                $userData = $user;
                $studentId = $user->roll ?? $user->student_id ?? null;
                $studentName = $user->name ?? 'Student';
            }

            // Get student's group information
            $groupInfo = $this->getStudentGroupInfo($studentId);
            
            if (!$groupInfo || !$groupInfo['hasGroup']) {
                return redirect()->route('student.meetings.index')->with('error', 'You are not assigned to any group yet.');
            }

            // Get all meetings for the group
            $meetings = Meeting::with(['attendances.groupStudent'])
                ->where('group_id', $groupInfo['group']['id'])
                ->orderBy('meeting_date', 'asc')
                ->get();

            // Get all group members for attendance tracking
            $groupMembers = collect($groupInfo['members']);

            // Prepare data for PDF
            $pdfData = [
                'universityName' => 'Premier University Chattogram',
                'departmentName' => 'Department of Computer Science & Engineering',
                'logoPath' => public_path('Picture1.png'),
                'supervisor' => $groupInfo['supervisor'],
                'areaOfInterest' => $groupInfo['areaOfInterest'],
                'studentIds' => $groupMembers->pluck('student_id')->implode(', '),
                'groupName' => $groupInfo['group']['name'],
                'meetings' => $meetings,
                'groupMembers' => $groupMembers,
                'generatedDate' => now()->format('F d, Y')
            ];

            // Generate PDF
            $pdf = Pdf::view('student.meetings-pdf', $pdfData)
                ->format('a4')
                ->margins(15, 15, 15, 15);

            $filename = 'meetings_report_' . str_replace([' ', '/'], '_', $groupInfo['group']['name']) . '_' . now()->format('Y_m_d') . '.pdf';

            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('Failed to generate meetings PDF:', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'student_id' => $studentId ?? null
            ]);
            
            return redirect()->route('student.meetings.index')->with('error', 'Failed to generate PDF report. Please try again or contact support.');
        }
    }
}