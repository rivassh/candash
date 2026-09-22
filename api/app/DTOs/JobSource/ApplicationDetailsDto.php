<?php

namespace App\DTOs\JobSource;

class ApplicationDetailsDto
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $applicationId,
        public readonly ?string $resumeText,
        public readonly array $details,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            externalId: (string) ($data['external_id'] ?? $data['id'] ?? ''),
            applicationId: (string) ($data['applicationId'] ?? $data['application_id'] ?? ''),
            resumeText: $data['resumeText'] ?? $data['resume_text'] ?? $data['cvText'] ?? $data['cv_text'] ?? null,
            details: $data['details'] ?? $data['data'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'application_id' => $this->applicationId,
            'resume_text' => $this->resumeText,
            'details' => $this->details,
        ];
    }
}
