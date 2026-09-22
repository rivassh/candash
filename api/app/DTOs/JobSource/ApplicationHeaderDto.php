<?php

namespace App\DTOs\JobSource;

class ApplicationHeaderDto
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $applicationId,
        public readonly string $jobPostId,
        public readonly ?string $candidateName,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $status,
        public readonly ?string $submittedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            externalId: (string) ($data['external_id'] ?? $data['id'] ?? ''),
            applicationId: (string) ($data['applicationId'] ?? $data['application_id'] ?? ''),
            jobPostId: (string) ($data['jobPostId'] ?? $data['job_post_id'] ?? ''),
            candidateName: $data['candidateName'] ?? $data['candidate_name'] ?? $data['fullName'] ?? $data['full_name'] ?? null,
            email: $data['email'] ?? $data['emailAddress'] ?? $data['applicantEmail'] ?? null,
            phone: $data['phone'] ?? $data['mobile'] ?? $data['mobileNumber'] ?? $data['applicantPhone'] ?? null,
            status: $data['status'] ?? $data['applicationStatus'] ?? $data['application_status'] ?? null,
            submittedAt: $data['submittedAt'] ?? $data['submitted_at'] ?? $data['applicationDate'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'application_id' => $this->applicationId,
            'job_post_id' => $this->jobPostId,
            'candidate_name' => $this->candidateName,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'submitted_at' => $this->submittedAt,
        ];
    }
}
