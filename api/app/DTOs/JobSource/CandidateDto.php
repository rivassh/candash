<?php

namespace App\DTOs\JobSource;

class CandidateDto
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $name,
        public readonly ?string $email,
        public readonly ?string $phone,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            externalId: (string) ($data['external_id'] ?? $data['id'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
        );
    }
}