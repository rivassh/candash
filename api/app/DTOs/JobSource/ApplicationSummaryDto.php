<?php

namespace App\DTOs\JobSource;

class ApplicationSummaryDto
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $jobPostId,
        public readonly ?string $applicationId,
        public readonly ?string $status,
        public readonly ?string $submittedAt,
        public readonly ?string $candidateName,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            externalId: (string) ($data['external_id'] ?? $data['id'] ?? ''),
            jobPostId: (string) ($data['jobPostId'] ?? $data['job_post_id'] ?? ''),
            applicationId: $data['applicationId'] ?? $data['application_id'] ?? null,
            status: $data['status'] ?? null,
            submittedAt: $data['submittedAt'] ?? $data['submitted_at'] ?? null,
            candidateName: $data['candidateName'] ?? $data['candidate_name'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'job_post_id' => $this->jobPostId,
            'application_id' => $this->applicationId,
            'status' => $this->status,
            'submitted_at' => $this->submittedAt,
            'candidate_name' => $this->candidateName,
        ];
    }
}
