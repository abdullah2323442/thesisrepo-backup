<?php

namespace App\Services;

use App\Models\Batch;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BatchApiService
{
    private string $baseUrl;
    private string $batchListEndpoint;
    private int $programId;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('external_api.base_url');
        $this->batchListEndpoint = config('external_api.endpoints.batch_list');
        $this->programId = config('external_api.department_id'); // Using department_id as program_id
        $this->timeout = config('external_api.timeout');
    }

    /**
     * Fetch and sync batches from the API
     */
    public function syncBatches(): array
    {
        try {
            $apiUrl = $this->baseUrl . $this->batchListEndpoint;
            $response = Http::timeout($this->timeout)->get($apiUrl, [
                'programID' => $this->programId
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

            foreach ($data['Data'] as $batchData) {
                try {
                    $batch = $this->createOrUpdateBatch($batchData);
                    if ($batch->wasRecentlyCreated) {
                        $synced++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to sync batch {$batchData['batch_name']}: " . $e->getMessage();
                    Log::error('Batch sync error', [
                        'batch_name' => $batchData['batch_name'],
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
            Log::error('Batch API sync failed', ['error' => $e->getMessage()]);
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
     * Create or update a batch from API data
     */
    private function createOrUpdateBatch(array $batchData): Batch
    {
        return Batch::updateOrCreate(
            ['batch_number' => $batchData['batch_name']],
            [
                'program_id' => $this->programId,
                'batch_name' => "Batch {$batchData['batch_name']}",
                'last_synced_at' => now(),
                // Keep existing is_active status if updating, otherwise default to true
                'is_active' => Batch::where('batch_number', $batchData['batch_name'])->value('is_active') ?? true,
            ]
        );
    }

    /**
     * Get available batches from API without saving
     */
    public function getAvailableBatches(): array
    {
        try {
            $apiUrl = $this->baseUrl . $this->batchListEndpoint;
            $response = Http::timeout($this->timeout)->get($apiUrl, [
                'programID' => $this->programId
            ]);

            if (!$response->successful()) {
                throw new \Exception('API request failed with status: ' . $response->status());
            }

            $data = $response->json();

            if (!isset($data['Data']) || !is_array($data['Data'])) {
                throw new \Exception('Invalid API response format');
            }

            $batches = collect($data['Data'])
                ->pluck('batch_name')
                ->sort()
                ->values()
                ->toArray();

            return [
                'success' => true,
                'batches' => $batches,
                'total' => count($batches)
            ];

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
     * Compare local batches with API data
     */
    public function compareBatches(): array
    {
        $apiResult = $this->getAvailableBatches();
        
        if (!$apiResult['success']) {
            return [
                'success' => false,
                'error' => $apiResult['error']
            ];
        }

        $apiBatches = collect($apiResult['batches']);
        $localBatches = Batch::pluck('batch_number');

        return [
            'success' => true,
            'api_batches' => $apiBatches->toArray(),
            'local_batches' => $localBatches->toArray(),
            'new_batches' => $apiBatches->diff($localBatches)->values()->toArray(),
            'missing_batches' => $localBatches->diff($apiBatches)->values()->toArray(),
            'total_api' => $apiBatches->count(),
            'total_local' => $localBatches->count()
        ];
    }
}
