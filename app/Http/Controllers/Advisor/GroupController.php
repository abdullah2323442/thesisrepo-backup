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
            $readonlyGroups = [];
            $adminCreatedGroups = [];
            $students = [];
            $unassignedStudents = [];
            $allAvailableStudents = [];
            
            if ($selectedBatch) {
                // Verify that the selected batch is one where the advisor has students
                if (!in_array($selectedBatch, $batches)) {
                    return redirect()->route('advisor.groups.index')
                                   ->with('error', 'You do not have students in the selected batch.');
                }
                
                // Get existing groups for this batch and advisor (using local ID for database) - only advisor-created
                $groups = Group::where('batch_number', $selectedBatch)
                              ->where('advisor_id', $advisorLocalId)
                              ->where(function($query) {
                                  $query->where('created_by_type', 'advisor')
                                        ->orWhereNull('created_by_type'); // Handle legacy groups
                              })
                              ->with(['students', 'areaOfInterest', 'areasOfInterest'])
                              ->get()
                              ->sortBy(function ($group) {
                                  // Extract number from group name for sorting
                                  if (preg_match('/(\d+)/', $group->name, $matches)) {
                                      return (int) $matches[1];
                                  }
                                  return 0;
                              });

                // Get read-only groups from other advisors in the same batch
                $readonlyGroups = Group::where('batch_number', $selectedBatch)
                                     ->where('advisor_id', '!=', $advisorLocalId)
                                     ->with(['students', 'areaOfInterest', 'areasOfInterest', 'advisor'])
                                     ->get()
                                     ->sortBy(function ($group) {
                                         // Extract number from group name for sorting
                                         if (preg_match('/(\d+)/', $group->name, $matches)) {
                                             return (int) $matches[1];
                                         }
                                         return 0;
                                     });

                // Get admin-created groups for this advisor
                $adminCreatedGroups = Group::where('batch_number', $selectedBatch)
                                         ->where('advisor_id', $advisorLocalId)
                                         ->where('created_by_type', 'admin')
                                         ->with(['students', 'areaOfInterest', 'areasOfInterest', 'supervisor', 'createdByAdmin'])
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
                    
                    // Get assigned student IDs (using roll as student_id) - only from advisor-created groups
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
                'readonlyGroups',
                'adminCreatedGroups',
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
        
        // Get all assigned student IDs across all batches for this advisor (only advisor-created groups)
        $allAssignedStudentIds = GroupStudent::whereHas('group', function ($query) use ($advisorLocalId) {
            $query->where('advisor_id', $advisorLocalId)
                  ->where(function($q) {
                      $q->where('created_by_type', 'advisor')
                        ->orWhereNull('created_by_type'); // Handle legacy groups
                  });
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
                                  ->where(function($query) {
                                      $query->where('created_by_type', 'advisor')
                                            ->orWhereNull('created_by_type'); // Handle legacy groups
                                  })
                                  ->count();

            if ($existingGroups > 0) {
                throw new \Exception('Groups already exist for this batch');
            }

            // Create groups (using local ID for database) - mark as advisor-created
            for ($i = 1; $i <= $groupCount; $i++) {
                Group::create([
                    'name' => "Group {$i}",
                    'batch_number' => $batch,
                    'advisor_id' => $advisorLocalId,
                    'max_students' => 3,
                    'created_by_type' => 'advisor'
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

            // Create the new group - mark as advisor-created
            Group::create([
                'name' => $groupName,
                'batch_number' => $batch,
                'advisor_id' => $advisorLocalId,
                'max_students' => 3,
                'created_by_type' => 'advisor'
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
            
            // Check if group belongs to current advisor (using local ID) and is advisor-created
            if ($group->advisor_id !== $advisorLocalId || $group->created_by_type === 'admin') {
                throw new \Exception('Unauthorized access to group or group is admin-managed');
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
     * Assign areas of interest to group (supports multiple areas)
     */
    public function assignAreaOfInterest(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'area_of_interest_ids' => 'nullable|array',
            'area_of_interest_ids.*' => 'exists:area_of_interests,id'
        ]);

        try {
            $advisorLocalId = auth()->id();
            $group = Group::findOrFail($request->group_id);
            
            // Check if group belongs to current advisor and is advisor-created
            if ($group->advisor_id !== $advisorLocalId || $group->created_by_type === 'admin') {
                throw new \Exception('Unauthorized access to group or group is admin-managed');
            }

            DB::beginTransaction();

            // Get the area IDs from request (handle both single and multiple)
            $areaIds = [];
            if ($request->has('area_of_interest_ids')) {
                $areaIds = $request->area_of_interest_ids;
            } elseif ($request->has('area_of_interest_id')) {
                // Support legacy single area assignment
                if ($request->area_of_interest_id) {
                    $areaIds = [$request->area_of_interest_id];
                }
            }

            // Sync the areas of interest (this will add/remove as needed)
            $group->syncAreasOfInterest($areaIds);

            DB::commit();

            $count = count($areaIds);
            $message = $count > 0 
                ? ($count == 1 ? 'Area of interest assigned to group successfully' : "{$count} areas of interest assigned to group successfully")
                : 'All areas of interest removed from group successfully';

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Area of interest assignment failed', [
                'group_id' => $request->group_id,
                'area_of_interest_ids' => $request->area_of_interest_ids ?? null,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->with('error', 'Failed to assign areas of interest: ' . $e->getMessage());
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
            
            // Check if group belongs to current advisor (using local ID) and is advisor-created
            if ($groupStudent->group->advisor_id !== $advisorLocalId || $groupStudent->group->created_by_type === 'admin') {
                throw new \Exception('Unauthorized access or group is admin-managed');
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
     * Unassign area of interest from all groups in a batch
     */
    public function unassignAllAreasOfInterest(Request $request)
    {
        $request->validate([
            'batch' => 'required|integer'
        ]);

        try {
            $advisorLocalId = auth()->id();
            $batch = $request->batch;

            DB::beginTransaction();

            // Get all advisor-created groups for this batch and advisor
            $groups = Group::where('batch_number', $batch)
                          ->where('advisor_id', $advisorLocalId)
                          ->where(function($query) {
                              $query->where('created_by_type', 'advisor')
                                    ->orWhereNull('created_by_type'); // Handle legacy groups
                          })
                          ->whereNotNull('area_of_interest_id')
                          ->get();

            if ($groups->isEmpty()) {
                throw new \Exception('No groups with assigned areas of interest found for this batch');
            }

            $updatedCount = 0;
            foreach ($groups as $group) {
                $group->update(['area_of_interest_id' => null]);
                $updatedCount++;
            }

            DB::commit();

            return redirect()->back()
                           ->with('success', "Successfully unassigned areas of interest from {$updatedCount} groups in batch {$batch}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unassign all areas of interest failed', [
                'advisor_local_id' => auth()->id(),
                'batch' => $request->batch,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->with('error', 'Failed to unassign areas of interest: ' . $e->getMessage());
        }
    }

    /**
     * Remove all groups and their student assignments for a batch
     */
    public function removeAllGroups(Request $request)
    {
        $request->validate([
            'batch' => 'required|integer'
        ]);

        try {
            $advisorLocalId = auth()->id();
            $batch = $request->batch;

            DB::beginTransaction();

            // Get all advisor-created groups for this batch and advisor
            $groups = Group::where('batch_number', $batch)
                          ->where('advisor_id', $advisorLocalId)
                          ->where(function($query) {
                              $query->where('created_by_type', 'advisor')
                                    ->orWhereNull('created_by_type'); // Handle legacy groups
                          })
                          ->get();

            if ($groups->isEmpty()) {
                throw new \Exception('No groups found for this batch');
            }

            $groupCount = $groups->count();
            $studentAssignmentCount = 0;

            // Remove all student assignments first
            foreach ($groups as $group) {
                $studentAssignmentCount += $group->students()->count();
                $group->students()->delete(); // Delete all group_students records
            }

            // Remove all advisor-created groups
            Group::where('batch_number', $batch)
                 ->where('advisor_id', $advisorLocalId)
                 ->where(function($query) {
                     $query->where('created_by_type', 'advisor')
                           ->orWhereNull('created_by_type'); // Handle legacy groups
                 })
                 ->delete();

            DB::commit();

            return redirect()->back()
                           ->with('success', "Successfully removed {$groupCount} groups and {$studentAssignmentCount} student assignments from batch {$batch}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Remove all groups failed', [
                'advisor_local_id' => auth()->id(),
                'batch' => $request->batch,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                           ->with('error', 'Failed to remove all groups: ' . $e->getMessage());
        }
    }
}