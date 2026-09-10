<?php

namespace App\DTOs\JobSource;

class PositionDto
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $title,
        public readonly string $department,
        public readonly string $level,
        public readonly string $employmentType,
        public readonly int $minExperienceYears,
        public readonly ?string $description,
        public readonly array $requiredSkills = [],
        public readonly array $preferredSkills = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            externalId: (string) ($data['external_id'] ?? $data['id'] ?? ''),
            title: (string) ($data['title'] ?? ''),
            department: (string) ($data['department'] ?? 'general'),
            level: (string) ($data['level'] ?? 'mid'),
            employmentType: (string) ($data['employment_type'] ?? 'full_time'),
            minExperienceYears: (int) ($data['min_experience_years'] ?? 0),
            description: $data['description'] ?? null,
            requiredSkills: $data['required_skills'] ?? [],
            preferredSkills: $data['preferred_skills'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'title' => $this->title,
            'department' => $this->department,
            'level' => $this->level,
            'employment_type' => $this->employmentType,
            'min_experience_years' => $this->minExperienceYears,
            'description' => $this->description,
            'required_skills' => $this->requiredSkills,
            'preferred_skills' => $this->preferredSkills,
        ];
    }
}