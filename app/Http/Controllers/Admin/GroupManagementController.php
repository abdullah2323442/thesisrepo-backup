<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\AreaOfInterest;
use App\Models\Supervisor;
use App\Models\Batch;
use App\Models\User;
use App\Services\StudentApiService;
use App\Services\SupervisorApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GroupManagementController extends Controller
{
    protected StudentApiService $studentApiService;
    protected SupervisorApiService $supervisorApiService;

    public function __construct(StudentApiService $studentApiService, SupervisorApiService $supervisorApiService)
    {
        $this->studentApiService = $studentApiService;
        $this->supervisorApiService = $supervisorApiService;
    }

    /**
     * Display the admin group management page
     */
    public function index(Request $request)
    {
        try {
            $selectedBatch = $request->get('batch');
            
            // Get batches that already have groups
            $availableBatches = Group::select('batch_number')
                ->distinct()
                ->orderBy('batch_number', 'desc')
                ->pluck('batch_number');
            
            $groups = collect();
            $eligibleStudents = collect();
            
            if ($selectedBatch) {
                // Get all groups in the selected batch (across all advisors)
                $groups = Group::where('batch_number', $selectedBatch)
                    ->with(['students', 'areaOfInterest', 'areasOfInterest', 'supervisor', 'advisor'])
                    ->get()
                    ->sortBy(function ($group) {
                        // Extract number from group name for proper sorting
                        if (preg_match('/(\d+)/', $group->name, $matches)) {
                            return (int) $matches[1];
                        }
                        return 9999;
                    });

                // Get eligible students from batches < selected batch
                $eligibleStudents = $this->getEligibleStudents($selectedBatch);
            }
            
            // Get all active areas of interest
            $areasOfInterest = AreaOfInterest::where('is_active', true)->orderBy('name')->get();
            
            // Get supervisors with available slots
            $supervisors = Supervisor::withAvailableSlots()
                ->with('areasOfInterest')
                ->get()
                ->sortBy(function ($supervisor) {
                    return sprintf('%02d-%s', $supervisor->rank_priority, mb_strtolower($supervisor->fullname ?? ''));
                });

            return view('admin.groups.index', compact(
                'availableBatches',
                'selectedBatch',
                'groups',
                'eligibleStudents',
                'areasOfInterest',
                'supervisors'
            ));
            
        } catch (\Exception $e) {
            Log::error('Admin group management page failed', [
                'error' => $e->getMessage(),
                'batch' => $request->get('batch')
            ]);
            
            return redirect()->back()->with('error', 'Failed to load group management: ' . $e->getMessage());
        }
    }

    /**
     * Create a new group with enhanced admin functionality
     */
    public function createGroup(Request $request)
    {
        $request->validate([
            'batch' => 'required|integer',
            'group_name' => 'required|string|max:255',
            'area_of_interest_ids' => 'nullable|array',
            'area_of_interest_ids.*' => 'exists:area_of_interests,id',
            'supervisor_id' => 'nullable|exists:supervisors,id'
        ]);

        try {
            DB::beginTransaction();

            $batch = $request->batch;
            $groupName = trim($request->group_name);
            $areaOfInterestIds = $request->area_of_interest_ids ?? [];
            $supervisorId = $request->supervisor_id;

            // Check if group name already exists for this batch
            $existingGroup = Group::where('batch_number', $batch)
                                 ->where('name', $groupName)
                                 ->first();

            if ($existingGroup) {
                throw new \Exception("Group '{$groupName}' already exists for this batch");
            }

            // Validate supervisor availability if provided
            if ($supervisorId) {
                $supervisor = Supervisor::findOrFail($supervisorId);
                if (!$supervisor->canTakeThesis()) {
                    throw new \Exception("Supervisor {$supervisor->fullname} has no available thesis slots.");
                }
            }

            // Create the new group with admin tracking
            $group = Group::create([
                'name' => $groupName,
                'batch_number' => $batch,
                'advisor_id' => null, // Will be auto-detected when first student is assigned
                'max_students' => 4, // Admin groups can have up to 4 students
                'supervisor_id' => $supervisorId,
                'is_manual_assignment' => $supervisorId ? true : false,
                'assigned_at' => $supervisorId ? now() : null,
                'created_by_type' => 'admin',
                'created_by_admin_id' => auth()->id(),
                'advisor_auto_detected' => false
            ]);

            // Assign areas of interest if provided
            if (!empty($areaOfInterestIds)) {
                $group->syncAreasOfInterest($areaOfInterestIds);
            }

            DB::commit();

            $message = "Group '{$groupName}' created successfully";
            if ($supervisorId) {
                $supervisor = Supervisor::find($supervisorId);
                $message .= " with supervisor {$supervisor->fullname}";
            }
            if (!empty($areaOfInterestIds)) {
                $areaCount = count($areaOfInterestIds);
                $message .= " and {$areaCount} area(s) of interest";
            }
            $message .= ". Advisor will be auto-detected when first student is assigned.";

            return redirect()->route('admin.groups.index', ['batch' => $batch])
                           ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin group creation failed', [
                'batch' => $request->batch,
                'group_name' => $request->group_name,
                'area_of_interest_ids' => $request->area_of_interest_ids ?? null,
                'supervisor_id' => $request->supervisor_id ?? null,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->with('error', 'Failed to create group: ' . $e->getMessage());
        }
    }

    /**
     * Delete a group and rearrange group numbers
     */
    public function deleteGroup(Group $group)
    {
        try {
            DB::beginTransaction();
            $batchNumber = $group->batch_number;
            $groupName = $group->name;
            $studentsCount = $group->students()->count();

            // Check if group has students
            if ($studentsCount > 0) {
                throw new \Exception("Cannot delete group '{$groupName}' because it has {$studentsCount} student(s) assigned. Please remove all students first.");
            }

            // Extract the group number from the group name for renumbering
            $deletedGroupNumber = null;
            if (preg_match('/Group (\d+)/', $group->name, $matches)) {
                $deletedGroupNumber = (int) $matches[1];
            }

            // Delete the group
            $group->delete();

            // Rearrange group numbers if it was a numbered group
            if ($deletedGroupNumber !== null) {
                $this->rearrangeGroupNumbers($batchNumber, $deletedGroupNumber);
            }

            DB::commit();

            Log::info('Admin deleted group', [
                'group_name' => $groupName,
                'batch_number' => $batchNumber,
                'deleted_by' => auth()->user()->name,
                'students_count' => $studentsCount
            ]);

            return redirect()->back()
                ->with('success', "Group '{$groupName}' has been deleted successfully. Group numbers have been rearranged.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin group deletion failed', [
                'group_id' => $group->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete group: ' . $e->getMessage());
        }
    }

    /**
     * Rearrange group numbers after deletion to maintain sequential order
     */
    private function rearrangeGroupNumbers(int $batchNumber, int $deletedGroupNumber): void
    {
        try {
            // Get all groups in the batch that have numbered names and are after the deleted group
            $groupsToRenumber = Group::where('batch_number', $batchNumber)
                ->where('name', 'LIKE', 'Group %') // Only numbered groups like "Group 1", "Group 2", etc.
                ->get()
                ->filter(function ($group) use ($deletedGroupNumber) {
                    if (preg_match('/Group (\d+)/', $group->name, $matches)) {
                        return (int) $matches[1] > $deletedGroupNumber;
                    }
                    return false;
                })
                ->sortBy(function ($group) {
                    preg_match('/Group (\d+)/', $group->name, $matches);
                    return (int) $matches[1];
                });

            // Renumber the groups
            foreach ($groupsToRenumber as $group) {
                if (preg_match('/Group (\d+)/', $group->name, $matches)) {
                    $currentNumber = (int) $matches[1];
                    $newNumber = $currentNumber - 1;
                    $newName = "Group {$newNumber}";
                    
                    $group->update(['name' => $newName]);
                    
                    Log::info('Group renumbered', [
                        'old_name' => $group->name,
                        'new_name' => $newName,
                        'batch_number' => $batchNumber
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to rearrange group numbers', [
                'batch_number' => $batchNumber,
                'deleted_group_number' => $deletedGroupNumber,
                'error' => $e->getMessage()
            ]);
            
            // Don't throw exception here as the main deletion was successful
        }
    }

    /**
     * Get eligible students from batches < selected batch
     */
    private function getEligibleStudents(int $selectedBatch): \Illuminate\Support\Collection
    {
        try {
            // Get all assigned student IDs
            $assignedStudentIds = GroupStudent::pluck('student_id')->toArray();
            
            // Get active batches < selected batch
            $priorBatches = Batch::active()
                ->where('batch_number', '<', $selectedBatch)
                ->pluck('batch_number')
                ->sort();
            
            $eligibleStudents = collect();
            
            foreach ($priorBatches as $batch) {
                $studentsResult = $this->studentApiService->getStudentsByBatch($batch);
                
                if ($studentsResult['success']) {
                    $batchStudents = collect($studentsResult['students'])
                        ->filter(function ($student) use ($assignedStudentIds) {
                            return !in_array($student['roll'], $assignedStudentIds);
                        })
                        ->map(function ($student) use ($batch) {
                            return [
                                'roll' => $student['roll'],
                                'name' => $student['name'],
                                'batch' => $batch,
                                'advisor_id' => $student['advisor_id'] ?? null,
                                'advisor' => $student['advisor'] ?? 'Unknown'
                            ];
                        });
                    
                    $eligibleStudents = $eligibleStudents->merge($batchStudents);
                }
            }
            
            return $eligibleStudents->sortBy(['batch', 'name']);
            
        } catch (\Exception $e) {
            Log::error('Failed to get eligible students', [
                'selected_batch' => $selectedBatch,
                'error' => $e->getMessage()
            ]);
            
            return collect();
        }
    }

    /**
     * Assign student to group
     */
    public function assignStudent(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'student_id' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $group = Group::findOrFail($request->group_id);
            
            // Check if student is already assigned to any group
            $existingAssignment = GroupStudent::where('student_id', $request->student_id)->first();
            if ($existingAssignment) {
                throw new \Exception('Student is already assigned to a group');
            }

            // Admin can assign up to 4 students per group
            $currentCount = $group->students()->count();
            if ($currentCount >= 4) {
                throw new \Exception('Group is already full (maximum 4 students for admin assignments)');
            }

            // Auto-expand capacity if needed (up to 4)
            if ($currentCount >= $group->max_students && $group->max_students < 4) {
                $group->update(['max_students' => 4]);
            }

            // Get student details from API to find their advisor
            $student = $this->findStudentInApi($request->student_id);
            if (!$student) {
                throw new \Exception('Student not found in API');
            }

            // Set advisor_id if group doesn't have one yet (auto-detection)
            if (!$group->advisor_id && isset($student['advisor_id'])) {
                $advisorUser = User::where('api_id', $student['advisor_id'])->first();
                if ($advisorUser) {
                    $group->update([
                        'advisor_id' => $advisorUser->id,
                        'advisor_auto_detected' => true
                    ]);
                    
                    Log::info('Advisor auto-detected for admin-created group', [
                        'group_id' => $group->id,
                        'group_name' => $group->name,
                        'advisor_id' => $advisorUser->id,
                        'advisor_name' => $advisorUser->name,
                        'student_roll' => $student['roll'],
                        'student_advisor_api_id' => $student['advisor_id']
                    ]);
                } else {
                    // Try to fetch and create the advisor from API
                    Log::info('Advisor not found locally, attempting to fetch from API', [
                        'group_id' => $group->id,
                        'group_name' => $group->name,
                        'student_roll' => $student['roll'],
                        'student_advisor_api_id' => $student['advisor_id'],
                        'student_advisor_name' => $student['advisor'] ?? 'Unknown'
                    ]);
                    
                    $advisorUser = $this->fetchAndCreateAdvisorFromApi($student['advisor_id'], $student['advisor'] ?? 'Unknown');
                    
                    if ($advisorUser) {
                        $group->update([
                            'advisor_id' => $advisorUser->id,
                            'advisor_auto_detected' => true
                        ]);
                        
                        Log::info('Advisor successfully created from API and auto-detected', [
                            'group_id' => $group->id,
                            'group_name' => $group->name,
                            'advisor_id' => $advisorUser->id,
                            'advisor_name' => $advisorUser->name,
                            'student_roll' => $student['roll'],
                            'student_advisor_api_id' => $student['advisor_id']
                        ]);
                    } else {
                        Log::warning('Advisor auto-detection failed - advisor not found in API', [
                            'group_id' => $group->id,
                            'group_name' => $group->name,
                            'student_roll' => $student['roll'],
                            'student_advisor_api_id' => $student['advisor_id'],
                            'student_advisor_name' => $student['advisor'] ?? 'Unknown'
                        ]);
                        
                        throw new \Exception("Advisor auto-detection failed. The student's advisor (" . 
                            ($student['advisor'] ?? 'Unknown') . 
                            ") could not be found or created from the API. Please contact administrator to manually add this advisor.");
                    }
                }
            }

            // Enforce same advisor per group
            if ($group->advisor_id && isset($student['advisor_id'])) {
                $advisorUser = User::where('api_id', $student['advisor_id'])->first();
                if ($advisorUser && $group->advisor_id !== $advisorUser->id) {
                    throw new \Exception('Cannot mix students with different advisors in the same group. This group is assigned to ' . 
                        ($group->advisor ? $group->advisor->name : 'Unknown Advisor') . 
                        ', but the student belongs to ' . ($advisorUser ? $advisorUser->name : 'Unknown Advisor') . '.');
                }
            }

            // Create assignment
            GroupStudent::create([
                'group_id' => $group->id,
                'student_id' => $student['roll'],
                'student_name' => $student['name'],
                'student_email' => null
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Student assigned to group successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin student assignment failed', [
                'group_id' => $group->id,
                'student_id' => $request->student_id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to assign student: ' . $e->getMessage());
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
            $groupStudent = GroupStudent::findOrFail($request->group_student_id);
            $groupStudent->delete();

            return redirect()->back()
                ->with('success', 'Student removed from group successfully');

        } catch (\Exception $e) {
            Log::error('Admin student removal failed', [
                'group_student_id' => $request->group_student_id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to remove student: ' . $e->getMessage());
        }
    }

    /**
     * Assign areas of interest to group
     */
    public function assignAreaOfInterest(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'area_of_interest_ids' => 'nullable|array',
            'area_of_interest_ids.*' => 'exists:area_of_interests,id'
        ]);

        try {
            $group = Group::findOrFail($request->group_id);
            
            DB::beginTransaction();

            $areaIds = $request->area_of_interest_ids ?? [];
            $group->syncAreasOfInterest($areaIds);

            DB::commit();

            $count = count($areaIds);
            $message = $count > 0 
                ? ($count == 1 ? 'Area of interest assigned successfully' : "{$count} areas of interest assigned successfully")
                : 'All areas of interest removed successfully';

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin area of interest assignment failed', [
                'group_id' => $group->id,
                'area_of_interest_ids' => $request->area_of_interest_ids ?? null,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to assign areas of interest: ' . $e->getMessage());
        }
    }

    /**
     * Assign supervisor to group
     */
    public function assignSupervisor(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'supervisor_id' => 'required|exists:supervisors,id',
        ]);

        try {
            $group = Group::findOrFail($request->group_id);
            $supervisor = Supervisor::findOrFail($request->supervisor_id);

            // Check if supervisor has available slots
            if (!$supervisor->canTakeThesis()) {
                throw new \Exception("Supervisor {$supervisor->fullname} has no available thesis slots.");
            }

            // Assign supervisor
            $group->update([
                'supervisor_id' => $supervisor->id,
                'is_manual_assignment' => true,
                'assigned_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', "Supervisor {$supervisor->fullname} has been assigned to {$group->name}.");

        } catch (\Exception $e) {
            Log::error('Admin supervisor assignment failed', [
                'group_id' => $group->id,
                'supervisor_id' => $request->supervisor_id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to assign supervisor: ' . $e->getMessage());
        }
    }

    /**
     * Remove supervisor assignment from group
     */
    public function unassignSupervisor(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        try {
            $group = Group::findOrFail($request->group_id);
            $supervisorName = $group->supervisor ? $group->supervisor->fullname : 'Unknown';

            $group->update([
                'supervisor_id' => null,
                'matched_area_of_interest_id' => null,
                'is_manual_assignment' => false,
                'assigned_at' => null,
                'assignment_priority' => null,
            ]);

            return redirect()->back()
                ->with('success', "Supervisor assignment removed from {$group->name}. {$supervisorName} is now available for other assignments.");

        } catch (\Exception $e) {
            Log::error('Admin supervisor unassignment failed', [
                'group_id' => $group->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to remove supervisor assignment: ' . $e->getMessage());
        }
    }

    /**
     * Get available supervisors for a specific area of interest
     */
    public function getAvailableSupervisors(Request $request)
    {
        try {
            $areaOfInterestId = $request->get('area_of_interest_id');
            
            $query = Supervisor::withAvailableSlots();
            
            if ($areaOfInterestId) {
                $query->whereHas('areasOfInterest', function ($q) use ($areaOfInterestId) {
                    $q->where('area_of_interests.id', $areaOfInterestId);
                });
            }
            
            $supervisors = $query->get()
                ->sortBy('rank_priority')
                ->map(function ($supervisor) {
                    return [
                        'id' => $supervisor->id,
                        'fullname' => $supervisor->fullname,
                        'designation' => $supervisor->designation,
                        'available_slots' => $supervisor->available_slots,
                        'rank_priority' => $supervisor->rank_priority,
                    ];
                })
                ->values();

            return response()->json($supervisors);
            
        } catch (\Exception $e) {
            Log::error('Error getting available supervisors', [
                'area_of_interest_id' => $request->get('area_of_interest_id'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Failed to load supervisors',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find student in API across all batches
     */
    private function findStudentInApi(string $studentId): ?array
    {
        // Get all batches and search for the student
        $batchesResult = $this->studentApiService->getBatches();
        if (!$batchesResult['success']) {
            return null;
        }

        foreach ($batchesResult['batches'] as $batch) {
            $studentsResult = $this->studentApiService->getStudentsByBatch($batch);
            if ($studentsResult['success']) {
                $student = collect($studentsResult['students'])
                    ->firstWhere('roll', $studentId);
                
                if ($student) {
                    return $student;
                }
            }
        }

        return null;
    }

    /**
     * Fetch advisor from API and create as User
     */
    private function fetchAndCreateAdvisorFromApi(int $advisorApiId, string $advisorName): ?User
    {
        try {
            // Fetch teachers from API
            $baseUrl = config('external_api.base_url');
            $teacherListEndpoint = config('external_api.endpoints.teacher_list');
            $departmentId = config('external_api.department_id');
            $timeout = config('external_api.timeout');

            $apiUrl = $baseUrl . $teacherListEndpoint;
            
            Log::info('Attempting to fetch advisor from teacher API', [
                'advisor_api_id' => $advisorApiId,
                'advisor_name' => $advisorName,
                'api_url' => $apiUrl,
                'department_id' => $departmentId
            ]);

            $response = Http::timeout($timeout)->get($apiUrl, [
                'deptId' => $departmentId
            ]);

            if (!$response->successful()) {
                Log::error('Teacher API request failed during advisor auto-detection', [
                    'status' => $response->status(),
                    'response_body' => $response->body(),
                    'advisor_api_id' => $advisorApiId,
                    'advisor_name' => $advisorName
                ]);
                return null;
            }

            $data = $response->json();

            if (!isset($data['Data']) || !is_array($data['Data'])) {
                Log::error('Invalid teacher API response format during advisor auto-detection', [
                    'advisor_api_id' => $advisorApiId,
                    'advisor_name' => $advisorName,
                    'response_structure' => array_keys($data ?? []),
                    'full_response' => $data
                ]);
                return null;
            }

            Log::info('Teacher API response received', [
                'advisor_api_id' => $advisorApiId,
                'advisor_name' => $advisorName,
                'total_teachers_in_api' => count($data['Data']),
                'sample_teacher_ids' => collect($data['Data'])->take(5)->pluck('id')->toArray(),
                'sample_teacher_names' => collect($data['Data'])->take(5)->pluck('fullname')->toArray()
            ]);

            // Find the advisor in the API response - try different possible field names
            $advisorData = collect($data['Data'])->firstWhere('id', $advisorApiId);
            
            if (!$advisorData) {
                // Try alternative search methods
                $advisorDataByName = collect($data['Data'])->first(function ($teacher) use ($advisorName) {
                    return isset($teacher['fullname']) && 
                           (stripos($teacher['fullname'], $advisorName) !== false || 
                            stripos($advisorName, $teacher['fullname']) !== false);
                });

                if ($advisorDataByName) {
                    Log::info('Advisor found by name match instead of ID', [
                        'advisor_api_id' => $advisorApiId,
                        'advisor_name' => $advisorName,
                        'found_teacher_id' => $advisorDataByName['id'] ?? 'unknown',
                        'found_teacher_name' => $advisorDataByName['fullname'] ?? 'unknown'
                    ]);
                    $advisorData = $advisorDataByName;
                } else {
                    // Log detailed information for debugging
                    $allTeacherInfo = collect($data['Data'])->map(function ($teacher) {
                        return [
                            'id' => $teacher['id'] ?? 'missing',
                            'fullname' => $teacher['fullname'] ?? 'missing',
                            'name' => $teacher['name'] ?? 'missing'
                        ];
                    })->toArray();

                    Log::warning('Advisor not found in teacher API response', [
                        'advisor_api_id' => $advisorApiId,
                        'advisor_name' => $advisorName,
                        'total_teachers_in_api' => count($data['Data']),
                        'all_teachers' => $allTeacherInfo,
                        'searched_by_id' => $advisorApiId,
                        'searched_by_name' => $advisorName
                    ]);
                    return null;
                }
            }

            Log::info('Advisor found in API, creating user', [
                'advisor_api_id' => $advisorApiId,
                'advisor_name' => $advisorName,
                'found_advisor_data' => $advisorData
            ]);

            // Create the advisor as a User using the teacher list API format
            $advisorUser = User::createOrUpdateTeacherFromListApi($advisorData);

            Log::info('Advisor successfully created from API', [
                'advisor_api_id' => $advisorApiId,
                'advisor_name' => $advisorName,
                'created_user_id' => $advisorUser->id,
                'created_user_name' => $advisorUser->name
            ]);

            return $advisorUser;

        } catch (\Exception $e) {
            Log::error('Failed to fetch and create advisor from API', [
                'advisor_api_id' => $advisorApiId,
                'advisor_name' => $advisorName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Bulk delete multiple groups
     */
    public function bulkDeleteGroups(Request $request)
    {
        $request->validate([
            'group_ids' => 'required|array',
            'group_ids.*' => 'exists:groups,id'
        ]);

        try {
            DB::beginTransaction();
            
            $groupIds = $request->group_ids;
            $groups = Group::whereIn('id', $groupIds)->get();
            
            // Collect information about groups to be deleted
            $groupsWithStudents = [];
            $groupsToDelete = [];
            $batchNumbers = [];
            
            foreach ($groups as $group) {
                $studentCount = $group->students()->count();
                if ($studentCount > 0) {
                    $groupsWithStudents[] = "'{$group->name}' ({$studentCount} students)";
                } else {
                    $groupsToDelete[] = $group;
                    $batchNumbers[$group->batch_number] = true;
                }
            }
            
            // If any group has students, abort the operation
            if (!empty($groupsWithStudents)) {
                throw new \Exception("Cannot delete the following groups because they have students assigned: " . 
                    implode(', ', $groupsWithStudents) . 
                    ". Please remove all students from these groups first.");
            }
            
            // Delete all groups that don't have students
            $deletedCount = 0;
            $deletedGroupNames = [];
            
            foreach ($groupsToDelete as $group) {
                $deletedGroupNames[] = $group->name;
                $group->delete();
                $deletedCount++;
            }
            
            // Rearrange group numbers for each affected batch
            foreach (array_keys($batchNumbers) as $batchNumber) {
                $this->rearrangeAllGroupNumbers($batchNumber);
            }
            
            DB::commit();
            
            Log::info('Admin bulk deleted groups', [
                'deleted_count' => $deletedCount,
                'deleted_groups' => $deletedGroupNames,
                'deleted_by' => auth()->user()->name,
                'affected_batches' => array_keys($batchNumbers)
            ]);
            
            $message = "Successfully deleted {$deletedCount} group(s)";
            if (!empty($deletedGroupNames)) {
                $message .= ": " . implode(', ', $deletedGroupNames);
            }
            $message .= ". Group numbers have been rearranged.";
            
            return redirect()->back()
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk group deletion failed', [
                'group_ids' => $request->group_ids,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to delete groups: ' . $e->getMessage());
        }
    }

    /**
     * Rearrange all group numbers in a batch to maintain sequential order
     */
    private function rearrangeAllGroupNumbers(int $batchNumber): void
    {
        try {
            // Get all numbered groups in the batch
            $numberedGroups = Group::where('batch_number', $batchNumber)
                ->where('name', 'LIKE', 'Group %')
                ->get()
                ->filter(function ($group) {
                    return preg_match('/Group (\d+)/', $group->name, $matches);
                })
                ->sortBy(function ($group) {
                    preg_match('/Group (\d+)/', $group->name, $matches);
                    return (int) $matches[1];
                })
                ->values();

            // Renumber them sequentially starting from 1
            foreach ($numberedGroups as $index => $group) {
                $newNumber = $index + 1;
                $newName = "Group {$newNumber}";
                
                if ($group->name !== $newName) {
                    $oldName = $group->name;
                    $group->update(['name' => $newName]);
                    
                    Log::info('Group renumbered during bulk operation', [
                        'old_name' => $oldName,
                        'new_name' => $newName,
                        'batch_number' => $batchNumber
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to rearrange group numbers after bulk deletion', [
                'batch_number' => $batchNumber,
                'error' => $e->getMessage()
            ]);
            // Don't throw exception here as the main deletion was successful
        }
    }
}