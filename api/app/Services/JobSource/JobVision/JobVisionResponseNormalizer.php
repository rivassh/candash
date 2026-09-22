<?php

namespace App\Services\JobSource\JobVision;

use App\DTOs\JobSource\PositionDto;
use App\DTOs\JobSource\CandidateDto;
use App\DTOs\JobSource\ApplicationSummaryDto;
use App\DTOs\JobSource\ApplicationHeaderDto;
use App\DTOs\JobSource\ApplicationDetailsDto;

class JobVisionResponseNormalizer
{
    public static function toPositionDto(array $jobPost): PositionDto
    {
        $jobPostId = (string) ($jobPost['jobPostId'] ?? $jobPost['id'] ?? '');

        $title = $jobPost['title']
            ?? $jobPost['jobTitle']
            ?? $jobPost['positionTitle']
            ?? 'Unknown Position';

        $department = $jobPost['category']
            ?? $jobPost['categoryTitle']
            ?? $jobPost['department']
            ?? 'general';

        $level = self::mapLevel($jobPost['seniorityLevelId'] ?? $jobPost['level'] ?? 'mid');

        $employmentType = self::mapEmploymentType(
            $jobPost['employmentTypeId'] ?? $jobPost['employmentType'] ?? $jobPost['type'] ?? 'full_time'
        );

        $minExp = (int) ($jobPost['minExperienceYears'] ?? $jobPost['minExperience'] ?? 0);

        $description = $jobPost['description']
            ?? $jobPost['jobDescription']
            ?? $jobPost['descriptionText']
            ?? null;

        $requiredSkills = [];
        $skillsData = $jobPost['requiredSkills'] ?? $jobPost['skills'] ?? [];
        foreach ($skillsData as $skill) {
            if (is_string($skill)) {
                $requiredSkills[] = ['name' => $skill, 'weight' => 5, 'min_years' => 0];
            } elseif (is_array($skill)) {
                $requiredSkills[] = [
                    'name' => $skill['name'] ?? $skill['title'] ?? 'unknown',
                    'weight' => $skill['weight'] ?? 5,
                    'min_years' => $skill['min_years'] ?? $skill['minYears'] ?? 0,
                ];
            }
        }

        $preferredSkills = [];
        $preferredData = $jobPost['preferredSkills'] ?? [];
        foreach ($preferredData as $skill) {
            if (is_string($skill)) {
                $preferredSkills[] = $skill;
            } elseif (is_array($skill)) {
                $preferredSkills[] = $skill['name'] ?? $skill['title'] ?? '';
            }
        }

        return new PositionDto(
            externalId: $jobPostId,
            title: $title,
            department: $department,
            level: $level,
            employmentType: $employmentType,
            minExperienceYears: $minExp,
            description: $description,
            requiredSkills: $requiredSkills,
            preferredSkills: $preferredSkills,
        );
    }

    public static function toCandidateDto(array $header): CandidateDto
    {
        $applicationId = (string) ($header['applicationId'] ?? $header['id'] ?? '');

        $name = $header['fullName']
            ?? $header['full_name']
            ?? $header['applicantName']
            ?? $header['name']
            ?? 'Unknown';

        $email = $header['email']
            ?? $header['emailAddress']
            ?? $header['applicantEmail']
            ?? null;

        $phone = $header['mobile']
            ?? $header['phone']
            ?? $header['mobileNumber']
            ?? $header['applicantPhone']
            ?? null;

        return new CandidateDto(
            externalId: $applicationId,
            name: $name,
            email: $email,
            phone: $phone,
        );
    }

    public static function toApplicationSummaryDto(array $summary): ApplicationSummaryDto
    {
        $applicationId = (string) ($summary['applicationId'] ?? $summary['id'] ?? '');
        $jobPostId = (string) ($summary['jobPostId'] ?? $summary['job_post_id'] ?? '');

        return new ApplicationSummaryDto(
            externalId: $applicationId,
            jobPostId: $jobPostId,
            applicationId: $applicationId,
            status: $summary['status'] ?? $summary['applicationStatus'] ?? null,
            submittedAt: $summary['submittedAt'] ?? $summary['submitted_at'] ?? $summary['applicationDate'] ?? null,
            candidateName: $summary['candidateName'] ?? $summary['candidate_name'] ?? $summary['fullName'] ?? $summary['full_name'] ?? null,
        );
    }

    public static function toApplicationHeaderDto(array $header): ApplicationHeaderDto
    {
        $applicationId = (string) ($header['applicationId'] ?? $header['id'] ?? '');
        $jobPostId = (string) ($header['jobPostId'] ?? $header['job_post_id'] ?? '');

        return new ApplicationHeaderDto(
            externalId: $applicationId,
            applicationId: $applicationId,
            jobPostId: $jobPostId,
            candidateName: $header['candidateName'] ?? $header['candidate_name'] ?? $header['fullName'] ?? $header['full_name'] ?? null,
            email: $header['email'] ?? $header['emailAddress'] ?? $header['applicantEmail'] ?? null,
            phone: $header['phone'] ?? $header['mobile'] ?? $header['mobileNumber'] ?? $header['applicantPhone'] ?? null,
            status: $header['status'] ?? $header['applicationStatus'] ?? $header['application_status'] ?? null,
            submittedAt: $header['submittedAt'] ?? $header['submitted_at'] ?? $header['applicationDate'] ?? null,
        );
    }

    public static function toApplicationDetailsDto(array $details): ApplicationDetailsDto
    {
        $applicationId = (string) ($details['applicationId'] ?? $details['id'] ?? '');

        return new ApplicationDetailsDto(
            externalId: $applicationId,
            applicationId: $applicationId,
            resumeText: $details['resumeText'] ?? $details['resume_text'] ?? $details['cvText'] ?? $details['cv_text'] ?? null,
            details: $details['details'] ?? $details['data'] ?? [],
        );
    }

    public static function resumeToText(array $details): string
    {
        $parts = [];

        $personal = $details['personalInfo'] ?? $details['personal_info'] ?? $details['applicantInfo'] ?? null;
        if ($personal && is_array($personal)) {
            $parts[] = "Personal Info:\n" . self::flattenArray($personal);
        }

        $workHistory = $details['workExperiences']
            ?? $details['workHistory']
            ?? $details['experiences']
            ?? [];
        if (!empty($workHistory)) {
            $parts[] = "Work Experience:\n" . self::flattenArray($workHistory);
        }

        $education = $details['educations'] ?? $details['education'] ?? [];
        if (!empty($education)) {
            $parts[] = "Education:\n" . self::flattenArray($education);
        }

        $skills = $details['skills'] ?? $details['userSkills'] ?? [];
        if (!empty($skills)) {
            if (is_array($skills[0] ?? null)) {
                $skills = array_column($skills, 'title');
            }
            $parts[] = "Skills: " . implode(', ', array_map('strval', $skills));
        }

        $languages = $details['languages'] ?? $details['language'] ?? [];
        if (!empty($languages)) {
            $parts[] = "Languages:\n" . self::flattenArray($languages);
        }

        $summary = $details['summary']
            ?? $details['about']
            ?? $details['profileSummary']
            ?? null;
        if ($summary) {
            $parts[] = "Summary:\n" . $summary;
        }

        $rawText = $details['cvText'] ?? $details['cv_text'] ?? $details['rawText'] ?? null;
        if ($rawText) {
            return $rawText;
        }

        return implode("\n\n", array_filter($parts));
    }

    private static function mapLevel(string|int $value): string
    {
        $value = (string) $value;
        return match (strtolower($value)) {
            '1', 'junior', 'کارآموز' => 'junior',
            '2', 'mid', 'mid_level', 'میانی' => 'mid',
            '3', 'senior', 'ارشد' => 'senior',
            default => 'mid',
        };
    }

    private static function mapEmploymentType(string|int $value): string
    {
        $value = (string) $value;
        return match (strtolower($value)) {
            '1', 'full_time', 'تمام‌وقت', 'تمام وقت' => 'full_time',
            '2', 'part_time', 'پاره‌وقت', 'پاره وقت' => 'part_time',
            '3', 'contract', 'قراردادی' => 'contract',
            '4', 'internship', 'کارآموزی' => 'internship',
            default => 'full_time',
        };
    }

    private static function flattenArray(array $data, int $depth = 0): string
    {
        $lines = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $lines[] = str_repeat('  ', $depth) . ucfirst((string) $key) . ':';
                $lines[] = self::flattenArray($value, $depth + 1);
            } else {
                $lines[] = str_repeat('  ', $depth) . ucfirst((string) $key) . ': ' . $value;
            }
        }
        return implode("\n", $lines);
    }
}
