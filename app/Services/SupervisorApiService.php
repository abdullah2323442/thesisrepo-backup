<?php

namespace App\Services;

use App\Models\Supervisor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupervisorApiService
{
    private string $baseUrl;
    private string $teacherListEndpoint;
    private int $departmentId;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('external_api.base_url');
        $this->teacherListEndpoint = config('external_api.endpoints.teacher_list');
        $this->departmentId = config('external_api.department_id');
        $this->timeout = config('external_api.timeout');
    }

    /**
     * Fetch and sync supervisors from the API
     */
    public function syncSupervisors(): array
    {
        try {
            $apiUrl = $this->baseUrl . $this->teacherListEndpoint;
            $response = Http::timeout($this->timeout)->get($apiUrl, [
                'deptId' => $this->departmentId
            ]);

            if (!$response->successful()) {
                throw new \Exception('API request failed with status: ' . $response->status());
            }

            $data = $response->json();

            if (!isset($data['Data']) || !is_array($data['Data'])) {
                throw new \Exception('Invalid API response format');
            }

            $synced = 0;
            $updated = 0;
            $errors = [];

            foreach ($data['Data'] as $teacherData) {
                try {
                    $supervisor = $this->createOrUpdateSupervisor($teacherData);
                    if ($supervisor->wasRecentlyCreated) {
                        $synced++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to sync teacher ID {$teacherData['id']}: " . $e->getMessage();
                    Log::error('Supervisor sync error', [
                        'teacher_id' => $teacherData['id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return [
                'success' => true,
                'synced' => $synced,
                'updated' => $updated,
                'total' => count($data['Data']),
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            Log::error('Supervisor API sync failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'synced' => 0,
                'updated' => 0,
                'total' => 0,
                'errors' => []
            ];
        }
    }

    /**
     * Create or update a supervisor from API data
     */
    private function createOrUpdateSupervisor(array $teacherData): Supervisor
    {
        return Supervisor::updateOrCreate(
            ['api_id' => $teacherData['id']],
            [
                'fullname' => $teacherData['fullname'],
                'gender' => $teacherData['gender'],
                'email' => $teacherData['email'],
                'designation' => $teacherData['designation'],
                'department' => $teacherData['department'],
                'last_synced_at' => now(),
                // Keep existing thesis_limit and is_active if updating
                'thesis_limit' => Supervisor::where('api_id', $teacherData['id'])->value('thesis_limit') ?? 3,
                'is_active' => Supervisor::where('api_id', $teacherData['id'])->value('is_active') ?? true,
            ]
        );
    }

    /**
     * Get fresh data for a single supervisor
     */
    public function refreshSupervisor(int $apiId): ?Supervisor
    {
        try {
            $apiUrl = $this->baseUrl . $this->teacherListEndpoint;
            $response = Http::timeout($this->timeout)->get($apiUrl, [
                'deptId' => $this->departmentId
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            $teacherData = collect($data['Data'])->firstWhere('id', $apiId);

            if (!$teacherData) {
                return null;
            }

            return $this->createOrUpdateSupervisor($teacherData);

        } catch (\Exception $e) {
            Log::error('Single supervisor refresh failed', [
                'api_id' => $apiId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
