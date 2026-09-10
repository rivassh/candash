<?php

namespace App\DTOs\Resume;

class ParsedResumeDto
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $linkedinUrl,
        public readonly array $skills = [],
        public readonly array $experiences = [],
        public readonly array $educations = [],
        public readonly ?string $summary = null,
        public readonly float $confidence = 0.85,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? 'نامشخص'),
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            linkedinUrl: $data['linkedin_url'] ?? null,
            skills: $data['skills'] ?? [],
            experiences: $data['experiences'] ?? [],
            educations: $data['educations'] ?? [],
            summary: $data['summary'] ?? null,
            confidence: (float) ($data['confidence'] ?? 0.85),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'linkedin_url' => $this->linkedinUrl,
            'skills' => $this->skills,
            'experiences' => $this->experiences,
            'educations' => $this->educations,
            'summary' => $this->summary,
            'confidence' => $this->confidence,
        ];
    }
}