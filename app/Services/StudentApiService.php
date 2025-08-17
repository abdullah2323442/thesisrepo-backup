<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class StudentApiService
{
    private string $baseUrl;
    private string $batchListEndpoint;
    private string $studentListEndpoint;
    private int $programId;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('external_api.base_url');
        $this->batchListEndpoint = config('external_api.endpoints.batch_list');
        $this->studentListEndpoint = config('external_api.endpoints.student_list');
        $this->programId = config('external_api.department_id');
        $this->timeout = config('external_api.timeout');
    }

    /**
     * Get all available batches
     */
    public function getBatches(): array
    {
        try {
            // Cache for 1 hour
            return Cache::remember('batches_program_' . $this->programId, 3600, function () {
                $apiUrl = $this->baseUrl . $this->batchListEndpoint;
                $response = Http::timeout($this->timeout)->get($apiUrl, [
                    'programID' => $this->programId
                ]);

                if (!$response->successful()) {
                    throw new \Exception('Batch API request failed with status: ' . $response->status());
                }

                $data = $response->json();

                if (!isset($data['Data']) || !is_array($data['Data'])) {
                    throw new \Exception('Invalid batch API response format');
                }

                return [
                    'success' => true,
                    'batches' => collect($data['Data'])->pluck('batch_name')->sort()->values()->toArray(),
                    'total' => count($data['Data'])
                ];
            });
        } catch (\Exception $e) {
            Log::error('Batch API fetch failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'batches' => [],
                'total' => 0
            ];
        }
    }

    /**
     * Get students for a specific batch
     */
    public function getStudentsByBatch(int $batch): array
    {
        try {
            // Cache for 30 minutes
            return Cache::remember("students_batch_{$batch}_program_{$this->programId}", 1800, function () use ($batch) {
                $apiUrl = $this->baseUrl . $this->studentListEndpoint;
                $response = Http::timeout($this->timeout)->get($apiUrl, [
                    'programID' => $this->programId,
                    'batch' => $batch
                ]);

                if (!$response->successful()) {
                    throw new \Exception('Student API request failed with status: ' . $response->status());
                }

                $data = $response->json();

                if (!isset($data['Data']) || !is_array($data['Data'])) {
                    throw new \Exception('Invalid student API response format');
                }

                return [
                    'success' => true,
                    'students' => $data['Data'],
                    'total' => count($data['Data']),
                    'batch' => $batch
                ];
            });
        } catch (\Exception $e) {
            Log::error('Student API fetch failed', [
                'batch' => $batch,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'students' => [],
                'total' => 0,
                'batch' => $batch
            ];
        }
    }

    /**
     * Get students by advisor ID across all active batches
     */
    public function getStudentsByAdvisor(int $advisorId): array
    {
        try {
            // Get only active batches from database
            $activeBatches = \App\Models\Batch::active()
                ->pluck('batch_number')
                ->sort()
                ->values()
                ->toArray();

            if (empty($activeBatches)) {
                return [
                    'success' => false,
                    'error' => 'No active batches found',
                    'students' => [],
                    'batches' => [],
                    'total' => 0,
                    'no_active_batches' => true
                ];
            }

            $allStudents = [];
            $studentBatches = [];
            $errorBatches = [];

            foreach ($activeBatches as $batch) {
                $studentResult = $this->getStudentsByBatch($batch);
                
                if ($studentResult['success']) {
                    // Filter students by advisor ID
                    $advisorStudents = collect($studentResult['students'])
                        ->filter(function ($student) use ($advisorId) {
                            return isset($student['advisor_id']) && $student['advisor_id'] == $advisorId;
                        })
                        ->values()
                        ->toArray();

                    if (!empty($advisorStudents)) {
                        $allStudents = array_merge($allStudents, $advisorStudents);
                        $studentBatches[] = $batch;
                    }
                } else {
                    $errorBatches[] = $batch;
                }
            }

            return [
                'success' => true,
                'students' => $allStudents,
                'batches' => $studentBatches,
                'error_batches' => $errorBatches,
                'total' => count($allStudents),
                'advisor_id' => $advisorId,
                'active_batches_available' => count($activeBatches)
            ];

        } catch (\Exception $e) {
            Log::error('Advisor students fetch failed', [
                'advisor_id' => $advisorId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'students' => [],
                'batches' => [],
                'total' => 0,
                'advisor_id' => $advisorId
            ];
        }
    }

    /**
     * Get statistics for an advisor
     */
    public function getAdvisorStats(int $advisorId): array
    {
        $result = $this->getStudentsByAdvisor($advisorId);
        
        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error']
            ];
        }

        $students = collect($result['students']);
        
        return [
            'success' => true,
            'total_students' => $students->count(),
            'male_students' => $students->where('gender', 'Male')->count(),
            'female_students' => $students->where('gender', 'Female')->count(),
            'batches_count' => count($result['batches']),
            'batches' => $result['batches']
        ];
    }

    /**
     * Clear cache for specific batch or all
     */
    public function clearCache(?int $batch = null): void
    {
        if ($batch) {
            Cache::forget("students_batch_{$batch}_program_{$this->programId}");
        } else {
            Cache::forget('batches_program_' . $this->programId);
            // Clear all batch caches (this is a simple approach)
            for ($i = 1; $i <= 50; $i++) {
                Cache::forget("students_batch_{$i}_program_{$this->programId}");
            }
        }
    }
}
