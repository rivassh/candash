<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCandidateRequest;
use App\Http\Requests\UpdateCandidateProfileRequest;
use App\Http\Resources\CandidateResource;
use App\Models\Candidate;
use App\Models\Resume;
use App\Models\Experience;
use App\Models\Education;
use App\Models\AuditLog;
use App\Contracts\Resume\ResumeExtractorInterface;
use App\Contracts\Enrichment\EnrichmentInterface;
use App\Enums\ResumeStatus;
use App\Enums\CandidateStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CandidateController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Candidate::query()
            ->with(['skills:id,name', 'latestResume'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, fn($q, $s) => $q->where('name', 'ilike', "%{$s}%"))
            ->orderByDesc('id');

        if ($request->boolean('paginate', true)) {
            return CandidateResource::collection($query->paginate(20));
        }

        return CandidateResource::collection($query->get());
    }

    public function store(StoreCandidateRequest $request): JsonResponse
    {
        $candidate = Candidate::create($request->validated());
        return response()->json(CandidateResource::make($candidate), 201);
    }

    public function show(Candidate $candidate): JsonResponse
    {
        $candidate->load([
            'skills:id,name,category',
            'experiences',
            'educations',
            'resumes:id,candidate_id,status,confidence,created_at',
        ]);
        return response()->json(CandidateResource::make($candidate));
    }

    public function updateProfile(UpdateCandidateProfileRequest $request, Candidate $candidate): JsonResponse
    {
        $old = $candidate->toArray();
        $validated = $request->validated();

        if (isset($validated['experiences'])) {
            $candidate->experiences()->delete();
            foreach ($validated['experiences'] as $exp) {
                $candidate->experiences()->create($exp);
            }
            unset($validated['experiences']);
        }

        if (isset($validated['educations'])) {
            $candidate->educations()->delete();
            foreach ($validated['educations'] as $edu) {
                $candidate->educations()->create($edu);
            }
            unset($validated['educations']);
        }

        $candidate->update($validated);
        AuditLog::log(Candidate::class, $candidate->id, 'profile_update', ['old' => $old]);

        return response()->json(CandidateResource::make($candidate->load('experiences', 'educations')));
    }

    public function uploadResume(Request $request, Candidate $candidate): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:txt,pdf,docx', 'mimetypes:text/plain,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'max:10240'],
        ]);

        $uploaded = $request->file('file');
        $mime = $uploaded->getMimeType();
        $allowed = ['text/plain' => 'txt', 'application/pdf' => 'pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'];
        if (!isset($allowed[$mime])) {
            return response()->json(['message' => 'Invalid file type.'], 422);
        }
        $path = $uploaded->store('resumes', 'public');
        $rawText = $uploaded->get();

        $resume = $candidate->resumes()->create([
            'file_path' => $path,
            'raw_text' => is_string($rawText) ? $rawText : null,
            'status' => ResumeStatus::Uploaded,
        ]);

        try {
            $extractor = app(ResumeExtractorInterface::class);
            $parsed = $extractor->extract($rawText ?? '');

            $resume->update([
                'parsed_data' => $parsed->toArray(),
                'status' => ResumeStatus::Parsed,
                'confidence' => $parsed->confidence,
            ]);

            $candidate->update([
                'name'  => $parsed->name !== 'نامشخص' ? $parsed->name : $candidate->name,
                'email' => $parsed->email ?? $candidate->email,
                'phone' => $parsed->phone ?? $candidate->phone,
                'linkedin_url' => $parsed->linkedinUrl ?? $candidate->linkedin_url,
            ]);

            foreach ($parsed->experiences as $exp) {
                $candidate->experiences()->create([
                    'company' => $exp['company'] ?? 'نامشخص',
                    'job_title' => $exp['job_title'] ?? 'نامشخص',
                    'start_date' => $exp['start_date'] ?? null,
                    'end_date' => $exp['end_date'] ?? null,
                    'is_current' => $exp['is_current'] ?? false,
                    'confidence' => $exp['confidence'] ?? 0.85,
                    'source' => 'resume',
                ]);
            }

            foreach ($parsed->educations as $edu) {
                $candidate->educations()->create([
                    'degree' => $edu['degree'] ?? 'نامشخص',
                    'field_of_study' => $edu['field_of_study'] ?? 'نامشخص',
                    'institution' => $edu['institution'] ?? 'نامشخص',
                    'graduation_year' => $edu['graduation_year'] ?? null,
                    'confidence' => $edu['confidence'] ?? 0.85,
                    'source' => 'resume',
                ]);
            }

            $candidate->update(['status' => CandidateStatus::InReview]);
        } catch (\Throwable $e) {
            $resume->update(['status' => ResumeStatus::Failed]);
        }

        return response()->json([
            'resume' => [
                'id' => $resume->id,
                'status' => $resume->status->value,
                'confidence' => $resume->confidence,
            ],
        ], 201);
    }

    public function enrichLinkedin(Request $request, Candidate $candidate): JsonResponse
    {
        $request->validate(['linkedin_url' => ['required', 'url']]);

        $enricher = app(EnrichmentInterface::class);
        $data = $enricher->enrichByLinkedin($request->linkedin_url, $candidate->name);

        $candidate->update([
            'enrichment_data' => $data,
            'status' => CandidateStatus::InReview,
        ]);

        foreach ($data['experiences'] ?? [] as $exp) {
            $candidate->experiences()->updateOrCreate(
                [
                    'company' => $exp['company'],
                    'job_title' => $exp['job_title'],
                    'is_current' => $exp['is_current'] ?? false,
                ],
                [
                    'start_date' => isset($exp['start_year_jalali']) ? "{$exp['start_year_jalali']}-01-01" : null,
                    'end_date' => isset($exp['end_year_jalali']) ? "{$exp['end_year_jalali']}-12-31" : null,
                    'confidence' => $exp['confidence'] ?? 0.85,
                    'source' => 'linkedin',
                ]
            );
        }

        AuditLog::log(Candidate::class, $candidate->id, 'enrich_linkedin', ['linkedin' => $request->linkedin_url]);

        return response()->json([
            'message' => 'غنی‌سازی با موفقیت انجام شد.',
            'data' => $data,
        ]);
    }

    public function destroy(Candidate $candidate): JsonResponse
    {
        $candidate->delete();
        return response()->json(null, 204);
    }
}