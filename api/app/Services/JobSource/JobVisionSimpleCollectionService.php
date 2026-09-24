<?php

namespace App\Services\JobSource;

use Illuminate\Support\Facades\Log;
use App\Models\JobPosition;
use App\Enums\JobPositionStatus;

class JobVisionSimpleCollectionService
{
    public function __construct(
        private JobVisionSimpleClient $client,
    ) {}

    public function collect(int $page = 1, int $pageSize = 50): array
    {
        Log::info('[JobVisionSimpleCollectionService] collect start', ['page' => $page, 'pageSize' => $pageSize]);

        $allItems = [];
        $pageNumber = $page;
        $totalItems = 0;
        $errors = [];
        $isComplete = false;

        try {
            while (true) {
                $items = $this->client->getJobPosts($pageNumber, $pageSize);

                if (empty($items)) {
                    $isComplete = true;
                    break;
                }

                $totalItems += count($items);

                foreach ($items as $item) {
                    $this->processItem($item);
                }

                if (count($items) < $pageSize) {
                    $isComplete = true;
                    break;
                }

                $pageNumber++;
            }

            Log::info('[JobVisionSimpleCollectionService] collect done', [
                'pages' => $pageNumber - $page + 1,
                'total_items' => $totalItems,
                'is_complete' => $isComplete,
            ]);

            return [
                'success' => true,
                'page_count' => $pageNumber - $page + 1,
                'item_count' => $totalItems,
                'is_complete' => $isComplete,
            ];
        } catch (\Exception $e) {
            Log::error('[JobVisionSimpleCollectionService] collect failed', [
                'page' => $pageNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Collection failed: ' . $e->getMessage(),
                'page_count' => $pageNumber - $page + 1,
                'item_count' => $totalItems,
            ];
        }
    }

    private function processItem(array $item): void
    {
        $externalId = (string) ($item['id'] ?? $item['externalId'] ?? '');
        
        if (!$externalId) {
            Log::warning('[JobVisionSimpleCollectionService] Skipping item without external_id');
            return;
        }

        $title = $item['title'] ?? $item['jobTitle'] ?? 'Unknown Position';
        $cityName = $item['cityName'] ?? $item['city_name'] ?? null;
        $status = $item['status'] ?? 'active';
        $expireDate = $item['expireDate'] ?? $item['expire_date'] ?? null;
        $applicationCount = $item['applicationCount'] ?? $item['application_count'] ?? 0;

        // Map city to department if available
        $department = $cityName ?? 'general';

        JobPosition::updateOrCreate(
            ['external_id' => $externalId],
            [
                'title' => $title,
                'department' => $department,
                'level' => 'mid', // default
                'employment_type' => 'full_time', // default
                'min_experience_years' => 0, // default
                'description' => null,
                'required_skills' => [],
                'preferred_skills' => [],
                'status' => JobPositionStatus::Open,
            ]
        );
    }
}