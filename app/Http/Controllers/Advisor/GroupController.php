<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\AreaOfInterest;
use App\Services\StudentApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GroupAssignmentImport;
use App\Exports\GroupTemplateExport;

class GroupController extends Controller
{
    protected StudentApiService $studentApiService;

    public function __construct(StudentApiService $studentApiService)
    {
        $this->studentApiService = $studentApiService;
    }

    /**
     * Get the advisor's API ID for external system integration
     */
    private function getAdvisorApiId()
    {
        $user = auth()->user();
        if (!$user->api_id) {
            throw new \Exception('Your account is not properly linked to the external system. Please contact administrator.');
        }
        return $user->api_id;
    }

    /**
     * Display the group management page
     */
    public function index(Request $request)
    {
        try {
            $advisorApiId = $this->getAdvisorApiId();
            $advisorLocalId = auth()->id(); // For local database operations
            $selectedBatch = $request->get('batch');
            
            // Get batches where this advisor has students (active batches only)
            $advisorStudentsResult = $this->studentApiService->getStudentsByAdvisor($advisorApiId);
            $batches = [];
            
            if ($advisorStudentsResult['success']) {
                $batches = $advisorStudentsResult['batches']; // These are the batches where advisor has students
            }
            
            $groups = [];
            $students = [];
            $unassignedStudents = [];
            $allAvailableStudents = [];
            
            if ($selectedBatch) {
                // Verify that the selected batch is one where the advisor has students
                if (!in_array($selectedBatch, $batches)) {
                    return redirect()->route('advisor.groups.index')
                                   ->with('error', 'You do not have students in the selected batch.');
                }
                
                // Get existing groups for this batch and advisor (using local ID for database)
                $groups = Group::where('batch_number', $selectedBatch)
                              ->where('advisor_id', $advisorLocalId)
                              ->with(['students', 'areaOfInterest'])
                              ->get()
                              ->sortBy(function ($group) {
                                  // Extract number from group name for sorting
                                  if (preg_match('/(\d+)/', $group->name, $matches)) {
                                      return (int) $matches[1];
                                  }
                                  return 0;
                              });
                
                // Get students for this batch (only those assigned to this advisor using API ID)
                $studentsResult = $this->studentApiService->getStudentsByBatch($selectedBatch);
                if ($studentsResult['success']) {
                    $allStudents = collect($studentsResult['students'])
                        ->filter(function ($student) use ($advisorApiId) {
                            return isset($student['advisor_id']) && $student['advisor_id'] == $advisorApiId;
                        });
                    
                    $students = $allStudents->toArray();
                    
                    // Get assigned student IDs (using roll as student_id)
                    $assignedStudentIds = $groups->flatMap(function ($group) {
                        return $group->students->pluck('student_id');
                    })->toArray();
                    
                    // Get unassigned students from current batch
                    $unassignedStudents = $allStudents->filter(function ($student) use ($assignedStudentIds) {
                        return !in_array($student['roll'], $assignedStudentIds);
                    })->values()->toArray();
                }
                
                // Get all available students from all batches where advisor is assigned
                $allAvailableStudents = $this->getAllAvailableStudents($advisorApiId, $advisorLocalId, $selectedBatch);
            }
            
            // Get all active areas of interest
            $areasOfInterest = AreaOfInterest::where('is_active', true)->orderBy('name')->get();
            
            return view('advisor.groups.index', compact(
                'batches', 
                'selectedBatch', 
                'groups', 
                'students', 
                'unassignedStudents',
                'allAvailableStudents',
                'areasOfInterest'
            ));
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create groups for a batch
     */
    public function createGroups(Request $request)
    {
        $request->validate([
            'batch' => 'required|integer'
        ]);

        try {
            $advisorApiId = $this->getAdvisorApiId();
            $advisorLocalId = auth()->id();
            $batch = $request->batch;

            DB::beginTransaction();

            // Get students for this batch using API ID
            $studentsResult = $this->studentApiService->getStudentsByBatch($batch);
            if (!$studentsResult['success']) {
                throw new \Exception('Failed to fetch students: ' . $studentsResult['error']);
            }

            $students = collect($studentsResult['students'])
                ->filter(function ($student) use ($advisorApiId) {
                    return isset($student['advisor_id']) && $student['advisor_id'] == $advisorApiId;
                });

            $studentCount = $students->count();
            
            if ($studentCount == 0) {
                throw new \Exception('No students found for this batch and advisor');
            }

            // Calculate number of groups needed
            $groupCount = ceil($studentCount / 3);

            // Check if groups already exist (using local ID for database)
            $existingGroups = Group::where('batch_number', $batch)
                                  ->where('advisor_id', $advisorLocalId)
                                  ->count();

            if ($existingGroups > 0) {
                throw new \Exception('Groups already exist for this batch');
            }

            // Create groups (using local ID for database)
            for ($i = 1; $i <= $groupCount; $i++) {
                Group::create([
                    'name' => "Group {$i}",
                    'batch_number' => $batch,
                    'advisor_id' => $advisorLocalId,
                    'max_students' => 3
                ]);
            }

            DB::commit();

            return redirect()->route('advisor.groups.index', ['batch' => $batch])
                           ->with('success', "Successfully created {$groupCount} groups for batch {$batch}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Group creation failed', [
                'advisor_local_id' => auth()->id(),
                'batch' => $request->batch,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->with('error', 'Failed to create groups: ' . $e->getMessage());
        }
    }

    /**
     * Assign student to group manually
     */
    public function assignStudent(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'student_id' => 'required|string'
        ]);

        try {
            $advisorApiId = $this->getAdvisorApiId();
            $advisorLocalId = auth()->id();

            DB::beginTransaction();

            $group = Group::findOrFail($request->group_id);
            
            // Check if group belongs to current advisor (using local ID)
            if ($group->advisor_id !== $advisorLocalId) {
                throw new \Exception('Unauthorized access to group');
            }

            // Check if group is full
            if ($group->isFull()) {
                throw new \Exception('Group is already full (maximum 3 students)');
            }

            // Check if student is already assigned to any group across all batches for this advisor
            $existingAssignment = GroupStudent::whereHas('group', function ($query) use ($advisorLocalId) {
                $query->where('advisor_id', $advisorLocalId);
            })->where('student_id', $request->student_id)->first();

            if ($existingAssignment) {
                throw new \Exception('Student is already assigned to a group in batch ' . $existingAssignment->group->batch_number);
            }

            // Get student details from API - check all batches where advisor is assigned
            $student = null;
            $advisorStudentsResult = $this->studentApiService->getStudentsByAdvisor($advisorApiId);
            
            if ($advisorStudentsResult['success']) {
                foreach ($advisorStudentsResult['batches'] as $batch) {
                    $studentsResult = $this->studentApiService->getStudentsByBatch($batch);
                    if ($studentsResult['success']) {
                        $foundStudent = collect($studentsResult['students'])
                            ->filter(function ($s) use ($advisorApiId) {
                                return isset($s['advisor_id']) && $s['advisor_id'] == $advisorApiId;
                            })
                            ->firstWhere('roll', $request->student_id);
                        
                        if ($foundStudent) {
                            $student = $foundStudent;
                            break;
                        }
                    }
                }
            }

            if (!$student) {
                throw new \Exception('Student not found or not assigned to you in any batch');
            }

            // Create assignment
            GroupStudent::create([
                'group_id' => $group->id,
                'student_id' => $student['roll'],
                'student_name' => $student['name'],
                'student_email' => null // No email field in API response
            ]);

            DB::commit();

            return redirect()->back()
                           ->with('success', 'Student assigned to group successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Student assignment failed', [
                'group_id' => $request->group_id,
                'student_id' => $request->student_id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->with('error', 'Failed to assign student: ' . $e->getMessage());
        }
    }

    /**
     * Add a single additional group
     */
    public function addGroup(Request $request)
    {
        $request->validate([
            'batch' => 'required|integer',
            'group_name' => 'required|string|max:255'
        ]);

        try {
            $advisorLocalId = auth()->id();
            $batch = $request->batch;
            $groupName = trim($request->group_name);

            DB::beginTransaction();

            // Check if group name already exists for this batch and advisor
            $existingGroup = Group::where('batch_number', $batch)
                                 ->where('advisor_id', $advisorLocalId)
                                 ->where('name', $groupName)
                                 ->first();

            if ($existingGroup) {
                throw new \Exception("Group '{$groupName}' already exists for this batch");
            }

            // Create the new group
            Group::create([
                'name' => $groupName,
                'batch_number' => $batch,
                'advisor_id' => $advisorLocalId,
                'max_students' => 3
            ]);

            DB::commit();

            return redirect()->route('advisor.groups.index', ['batch' => $batch])
                           ->with('success', "Group '{$groupName}' added successfully");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Add group failed', [
                'advisor_local_id' => auth()->id(),
                'batch' => $request->batch,
                'group_name' => $request->group_name,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->with('error', 'Failed to add group: ' . $e->getMessage());
        }
    }

    /**
     * Get all available students from all batches where advisor is assigned
     */
    private function getAllAvailableStudents($advisorApiId, $advisorLocalId, $currentBatch)
    {
        $allAvailableStudents = [];
        
        // Get all batches where this advisor has students
        $advisorStudentsResult = $this->studentApiService->getStudentsByAdvisor($advisorApiId);
        if (!$advisorStudentsResult['success']) {
            return [];
        }
        
        $batches = $advisorStudentsResult['batches'];
        
        // Get all assigned student IDs across all batches for this advisor
        $allAssignedStudentIds = GroupStudent::whereHas('group', function ($query) use ($advisorLocalId) {
            $query->where('advisor_id', $advisorLocalId);
        })->pluck('student_id')->toArray();
        
        foreach ($batches as $batch) {
            $studentsResult = $this->studentApiService->getStudentsByBatch($batch);
            if ($studentsResult['success']) {
                $batchStudents = collect($studentsResult['students'])
                    ->filter(function ($student) use ($advisorApiId) {
                        return isset($student['advisor_id']) && $student['advisor_id'] == $advisorApiId;
                    })
                    ->filter(function ($student) use ($allAssignedStudentIds) {
                        // Only include unassigned students
                        return !in_array($student['roll'], $allAssignedStudentIds);
                    })
                    ->map(function ($student) use ($batch, $currentBatch) {
                        return [
                            'roll' => $student['roll'],
                            'name' => $student['name'],
                            'batch' => $batch,
                            'is_current_batch' => $batch == $currentBatch,
                            'advisor' => $student['advisor'] ?? 'Unknown'
                        ];
                    })
                    ->values()
                    ->toArray();
                
                $allAvailableStudents = array_merge($allAvailableStudents, $batchStudents);
            }
        }
        
        // Sort by batch (current batch first) then by name
        usort($allAvailableStudents, function ($a, $b) {
            if ($a['is_current_batch'] && !$b['is_current_batch']) {
                return -1;
            }
            if (!$a['is_current_batch'] && $b['is_current_batch']) {
                return 1;
            }
            if ($a['batch'] != $b['batch']) {
                return $a['batch'] <=> $b['batch'];
            }
            return $a['name'] <=> $b['name'];
        });
        
        return $allAvailableStudents;
    }

    /**
     * Get next available group number
     */
    private function getNextGroupNumber($batch, $advisorLocalId)
    {
        $existingGroups = Group::where('batch_number', $batch)
                              ->where('advisor_id', $advisorLocalId)
                              ->get();

        $maxNumber = 0;
        foreach ($existingGroups as $group) {
            if (preg_match('/Group (\d+)/', $group->name, $matches)) {
                $number = (int) $matches[1];
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }

        return $maxNumber + 1;
    }

    /**
     * Assign area of interest to group
     */
    public function assignAreaOfInterest(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'area_of_interest_id' => 'nullable|exists:area_of_interests,id'
        ]);

        try {
            $advisorLocalId = auth()->id();
            $group = Group::findOrFail($request->group_id);
            
            // Check if group belongs to current advisor
            if ($group->advisor_id !== $advisorLocalId) {
                throw new \Exception('Unauthorized access to group');
            }

            DB::beginTransaction();

            // Update the group's area of interest
            $group->update([
                'area_of_interest_id' => $request->area_of_interest_id
            ]);

            DB::commit();

            $message = $request->area_of_interest_id 
                ? 'Area of interest assigned to group successfully'
                : 'Area of interest removed from group successfully';

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Area of interest assignment failed', [
                'group_id' => $request->group_id,
                'area_of_interest_id' => $request->area_of_interest_id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->with('error', 'Failed to assign area of interest: ' . $e->getMessage());
        }
    }

    /**
     * Remove student from group
     */
    public function removeStudent(Request $request)
    {
        $request->validate([
            'group_student_id' => 'required|exists:group_students,id'
        ]);

        try {
            $advisorLocalId = auth()->id();
            $groupStudent = GroupStudent::findOrFail($request->group_student_id);
            
            // Check if group belongs to current advisor (using local ID)
            if ($groupStudent->group->advisor_id !== $advisorLocalId) {
                throw new \Exception('Unauthorized access');
            }

            $groupStudent->delete();

            return redirect()->back()
                           ->with('success', 'Student removed from group successfully');

        } catch (\Exception $e) {
            Log::error('Student removal failed', [
                'group_student_id' => $request->group_student_id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->with('error', 'Failed to remove student: ' . $e->getMessage());
        }
    }

    /**
     * Detect column structure in Excel file
     */
    private function detectColumnStructure($rows)
    {
        if (empty($rows)) {
            return ['hasHeader' => false, 'studentCol' => 0, 'groupCol' => 1];
        }

        $firstRow = $rows[0];
        $hasHeader = false;
        $studentCol = 0;
        $groupCol = 1;

        // Check if first row contains headers
        if (isset($firstRow[0]) && isset($firstRow[1])) {
            $cell1 = strtolower(trim($firstRow[0]));
            $cell2 = strtolower(trim($firstRow[1]));
            
            // Common header patterns
            $studentHeaders = ['student_id', 'student id', 'roll', 'roll no', 'roll number', 'id', 'student'];
            $groupHeaders = ['group_name', 'group name', 'group', 'group_no', 'group no', 'group number'];
            
            if (in_array($cell1, $studentHeaders) || in_array($cell2, $groupHeaders)) {
                $hasHeader = true;
            }
            
            // If second column looks like group header, columns might be swapped
            if (in_array($cell1, $groupHeaders) && in_array($cell2, $studentHeaders)) {
                $studentCol = 1;
                $groupCol = 0;
                $hasHeader = true;
            }
        }

        return [
            'hasHeader' => $hasHeader,
            'studentCol' => $studentCol,
            'groupCol' => $groupCol
        ];
    }

    /**
     * Normalize group name from Excel
     */
    private function normalizeGroupName($groupName)
    {
        $groupName = trim($groupName);
        
        // Extract number from group name (e.g., "Group 1", "1", "Group1" -> "Group 1")
        if (preg_match('/(\d+)/', $groupName, $matches)) {
            return "Group " . $matches[1];
        }
        
        return $groupName;
    }

    /**
     * Auto-create groups based on Excel data
     */
    private function autoCreateGroups($batch, $advisorLocalId, $groupNames)
    {
        $createdGroups = [];
        
        foreach ($groupNames as $groupName) {
            $normalizedName = $this->normalizeGroupName($groupName);
            
            // Check if group already exists
            $existingGroup = Group::where('batch_number', $batch)
                                 ->where('advisor_id', $advisorLocalId)
                                 ->where('name', $normalizedName)
                                 ->first();
            
            if (!$existingGroup) {
                $group = Group::create([
                    'name' => $normalizedName,
                    'batch_number' => $batch,
                    'advisor_id' => $advisorLocalId,
                    'max_students' => 3
                ]);
                $createdGroups[] = $normalizedName;
            }
        }
        
        return $createdGroups;
    }

    /**
     * Upload Excel file for bulk assignment
     */
    public function uploadExcel(Request $request)
    {
        $request->validate([
            'batch' => 'required|integer',
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $advisorApiId = $this->getAdvisorApiId();
            $advisorLocalId = auth()->id();
            $batch = $request->batch;
            $file = $request->file('excel_file');

            DB::beginTransaction();

            // Read Excel file using Laravel Excel
            $rows = Excel::toArray(new GroupAssignmentImport, $file)[0]; // Get first sheet
            
            // Auto-detect column structure and skip header if exists
            $columnMapping = $this->detectColumnStructure($rows);
            if ($columnMapping['hasHeader']) {
                array_shift($rows);
            }

            // Get students for validation (using API ID)
            $studentsResult = $this->studentApiService->getStudentsByBatch($batch);
            if (!$studentsResult['success']) {
                throw new \Exception('Failed to fetch students for validation');
            }

            $validStudents = collect($studentsResult['students'])
                ->filter(function ($student) use ($advisorApiId) {
                    return isset($student['advisor_id']) && $student['advisor_id'] == $advisorApiId;
                })
                ->keyBy('roll'); // Use roll as the key

            // First pass: collect all unique group names from Excel
            $uniqueGroupNames = [];
            foreach ($rows as $row) {
                if (!empty($row[$columnMapping['groupCol']])) {
                    $groupName = trim($row[$columnMapping['groupCol']]);
                    $normalizedName = $this->normalizeGroupName($groupName);
                    $uniqueGroupNames[$normalizedName] = true;
                }
            }

            // Auto-create groups if they don't exist
            $createdGroups = $this->autoCreateGroups($batch, $advisorLocalId, array_keys($uniqueGroupNames));
            
            // Get all groups (existing + newly created)
            $groups = Group::where('batch_number', $batch)
                          ->where('advisor_id', $advisorLocalId)
                          ->get()
                          ->keyBy('name');

            // Validate and prepare assignments
            $assignments = [];
            $groupCounts = [];
            $errors = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + ($columnMapping['hasHeader'] ? 2 : 1);
                
                // Check if row has enough columns
                if (!isset($row[$columnMapping['studentCol']]) || !isset($row[$columnMapping['groupCol']])) {
                    continue; // Skip incomplete rows
                }
                
                $studentId = trim($row[$columnMapping['studentCol']]);
                $groupName = trim($row[$columnMapping['groupCol']]);
                
                if (empty($studentId) || empty($groupName)) {
                    continue; // Skip empty rows
                }

                // Normalize group name
                $normalizedGroupName = $this->normalizeGroupName($groupName);

                // Validate student exists
                if (!$validStudents->has($studentId)) {
                    $errors[] = "Row {$rowNumber}: Student ID '{$studentId}' not found or not assigned to you";
                    continue;
                }

                // Validate group exists (should exist now after auto-creation)
                if (!$groups->has($normalizedGroupName)) {
                    $errors[] = "Row {$rowNumber}: Group '{$normalizedGroupName}' could not be created";
                    continue;
                }

                // Count students per group
                if (!isset($groupCounts[$normalizedGroupName])) {
                    $groupCounts[$normalizedGroupName] = 0;
                }
                $groupCounts[$normalizedGroupName]++;

                // Check group capacity
                if ($groupCounts[$normalizedGroupName] > 3) {
                    $errors[] = "Row {$rowNumber}: Group '{$normalizedGroupName}' would exceed maximum capacity of 3 students";
                    continue;
                }

                $assignments[] = [
                    'group_id' => $groups[$normalizedGroupName]->id,
                    'student_id' => $studentId,
                    'student_name' => $validStudents[$studentId]['name'],
                    'student_email' => null // No email field in API response
                ];
            }

            if (!empty($errors)) {
                $errorMessage = "Excel validation failed with " . count($errors) . " error(s):\n" . implode("\n", $errors);
                throw new \Exception($errorMessage);
            }

            if (empty($assignments)) {
                throw new \Exception('No valid assignments found in the Excel file. Please check the format and ensure Student IDs and Group Names are correct.');
            }

            // Log successful processing info
            Log::info('Excel upload processing', [
                'advisor_local_id' => auth()->id(),
                'batch' => $batch,
                'total_assignments' => count($assignments),
                'groups_affected' => array_keys($groupCounts)
            ]);

            // Clear existing assignments for this batch and advisor (using local ID)
            GroupStudent::whereHas('group', function ($query) use ($batch, $advisorLocalId) {
                $query->where('batch_number', $batch)
                      ->where('advisor_id', $advisorLocalId);
            })->delete();

            // Create new assignments
            foreach ($assignments as $assignment) {
                GroupStudent::create($assignment);
            }

            DB::commit();

            $successMessage = 'Excel file uploaded and students assigned successfully';
            if (!empty($createdGroups)) {
                $successMessage .= '. Created new groups: ' . implode(', ', $createdGroups);
            }
            $successMessage .= '. Total assignments: ' . count($assignments);

            return redirect()->route('advisor.groups.index', ['batch' => $batch])
                           ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excel upload failed', [
                'advisor_local_id' => auth()->id(),
                'batch' => $request->batch,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->with('error', 'Failed to upload Excel file: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate(Request $request)
    {
        $batch = $request->get('batch');
        if (!$batch) {
            return redirect()->back()->with('error', 'Batch is required');
        }

        try {
            $advisorApiId = $this->getAdvisorApiId();
            $advisorLocalId = auth()->id();
            
            // Get students for this batch (using API ID)
            $studentsResult = $this->studentApiService->getStudentsByBatch($batch);
            if (!$studentsResult['success']) {
                return redirect()->back()->with('error', 'Failed to fetch students');
            }

            $students = collect($studentsResult['students'])
                ->filter(function ($student) use ($advisorApiId) {
                    return isset($student['advisor_id']) && $student['advisor_id'] == $advisorApiId;
                });

            // Get groups (using local ID)
            $groups = Group::where('batch_number', $batch)
                          ->where('advisor_id', $advisorLocalId)
                          ->get();

            // Create template data
            $templateData = [
                ['Student_ID', 'Group_Name', 'Student_Name'] // Header
            ];

            foreach ($students as $student) {
                $templateData[] = [
                    $student['roll'], // Use roll as student ID
                    '', // Empty group name for user to fill
                    $student['name'] // Use name field
                ];
            }

            // Add available groups as reference
            $templateData[] = []; // Empty row
            $templateData[] = ['Available Groups:'];
            foreach ($groups as $group) {
                $templateData[] = ['', $group->name];
            }

            return Excel::download(new GroupTemplateExport($templateData), "group_assignment_template_batch_{$batch}.xlsx");
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate template: ' . $e->getMessage());
        }
    }
}