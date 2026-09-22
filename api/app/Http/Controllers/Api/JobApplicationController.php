<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\DTOs\JobSource\ApplicationSummaryDto;
use App\DTOs\JobSource\ApplicationHeaderDto;
use App\DTOs\JobSource\ApplicationDetailsDto;
use App\Services\JobSource\JobVisionDriver;
use App\Services\JobSource\JobVision\JobVisionResponseNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobApplicationController extends Controller
{
    private JobVisionDriver $driver;

    public function __construct()
    {
        $this->driver = app(JobVisionDriver::class);
    }

    /**
     * GET /job-positions/{positionExternalId}/applications
     * List all applications for a specific job position.
     */
    public function index(Request $request, string $jobPostId): \Illuminate\Http\JsonResponse
    {
        $applications = $this->driver->listApplications((int)$jobPostId);

        return response()->json([
            'data' => $applications,
            'count' => count($applications),
        ]);
    }

    /**
     * GET /api/job-positions/{jobPostId}/applications/{applicationId}
     * Get application details including resume/CV text.
     */
    public function show(string $jobPostId, string $applicationId): \Illuminate\Http\JsonResponse
    {
        $details = $this->driver->getApplicationDetails($applicationId);

        if ($details === null) {
            return response()->json([
                'message' => 'Application not found',
            ], 404);
        }

        return response()->json([
            'data' => $details,
        ]);
    }

    /**
     * GET /api/job-positions/{jobPostId}/applications/{applicationId}/resume
     * Get resume/CV text for an application.
     */
    public function resume(string $jobPostId, string $applicationId): \Illuminate\Http\JsonResponse
    {
        $details = $this->driver->getApplicationDetails($applicationId);

        if ($details === null || !isset($details['resumeText']) || empty($details['resumeText'])) {
            return response()->json([
                'message' => 'Resume not found for this application',
            ], 404);
        }

        return response()->json([
            'data' => [
                'resumeText' => $details['resumeText'],
            ],
        ]);
    }
}