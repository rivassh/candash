<?php

namespace App\Services\JobSource;

use App\Contracts\JobSource\JobSourceInterface;
use App\Models\JobPosition;
use App\Models\Candidate;
use App\Enums\JobPositionStatus;
use App\Enums\CandidateStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobSourceImporter
{
    public function __construct(protected JobSourceInterface $source) {}

    /**
     * ایمپورت همه موقعیت‌ها از منبع به جدول داخلی
     *
     * @return array ['created' => int, 'updated' => int]
     */
    public function importPositions(): array
    {
        Log::info('[JobSourceImporter] importPositions start', ['driver' => $this->source->driverName()]);

        $positions = $this->source->listPositions();
        Log::info('[JobSourceImporter] Positions fetched', ['count' => count($positions)]);

        $created = 0;
        $updated = 0;

        foreach ($positions as $dto) {
            $result = JobPosition::updateOrCreate(
                ['external_id' => $dto->externalId],
                [
                    'title' => $dto->title,
                    'department' => $dto->department,
                    'level' => $dto->level,
                    'employment_type' => $dto->employmentType,
                    'min_experience_years' => $dto->minExperienceYears,
                    'description' => $dto->description,
                    'required_skills' => $dto->requiredSkills,
                    'preferred_skills' => $dto->preferredSkills,
                    'status' => JobPositionStatus::Open,
                ]
            );

            if ($result->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        Log::info('[JobSourceImporter] importPositions done', [
            'created' => $created,
            'updated' => $updated,
            'driver' => $this->source->driverName(),
        ]);

        return ['created' => $created, 'updated' => $updated, 'driver' => $this->source->driverName()];
    }

    /**
     * ایمپورت همه کاندیداها و رزومه‌هایشان
     */
    public function importCandidates(): array
    {
        Log::info('[JobSourceImporter] importCandidates start', ['driver' => $this->source->driverName()]);

        $candidates = $this->source->listCandidates();
        Log::info('[JobSourceImporter] Candidates fetched', ['count' => count($candidates)]);

        $created = 0;

        foreach ($candidates as $dto) {
            DB::transaction(function () use ($dto, &$created) {
                $candidate = Candidate::firstOrCreate(
                    ['email' => $dto->email ?? "{$dto->externalId}@jobsource.local"],
                    [
                        'name' => $dto->name,
                        'phone' => $dto->phone,
                        'status' => CandidateStatus::New,
                    ]
                );

                if ($candidate->wasRecentlyCreated) {
                    $created++;
                }

                $resumeText = $this->source->getCandidateResume($dto->externalId);
                if ($resumeText) {
                    $candidate->resumes()->create([
                        'raw_text' => $resumeText,
                        'status' => \App\Enums\ResumeStatus::Uploaded,
                    ]);
                }
            });
        }

        Log::info('[JobSourceImporter] importCandidates done', [
            'created' => $created,
            'driver' => $this->source->driverName(),
        ]);

        return ['created' => $created, 'driver' => $this->source->driverName()];
    }
}